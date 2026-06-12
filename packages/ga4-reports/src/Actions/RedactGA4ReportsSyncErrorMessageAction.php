<?php

declare(strict_types=1);

namespace Capell\GA4Reports\Actions;

use Capell\GA4Reports\Settings\GA4ReportsSettings;
use Illuminate\Support\Facades\File;
use JsonException;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

final class RedactGA4ReportsSyncErrorMessageAction
{
    use AsAction;

    public function handle(Throwable|string $error): string
    {
        $redacted = $error instanceof Throwable ? $error->getMessage() : $error;

        foreach ($this->knownSensitiveValues() as $value) {
            $redacted = str_replace($value, '[redacted]', $redacted);
        }

        foreach ($this->patterns() as $pattern => $replacement) {
            $redacted = preg_replace($pattern, $replacement, $redacted) ?? $redacted;
        }

        return $redacted;
    }

    /**
     * @return list<non-empty-string>
     */
    private function knownSensitiveValues(): array
    {
        /** @var GA4ReportsSettings $settings */
        $settings = app(GA4ReportsSettings::class);
        $values = [$settings->credentials_path];

        if ($settings->credentials_path !== '' && File::exists($settings->credentials_path)) {
            $values = [
                ...$values,
                ...$this->credentialValues($settings->credentials_path),
            ];
        }

        return array_values(array_unique(array_filter(
            $values,
            static fn (string $value): bool => mb_strlen($value) >= 8,
        )));
    }

    /**
     * @return list<string>
     */
    private function credentialValues(string $credentialsPath): array
    {
        try {
            $contents = File::get($credentialsPath);
            $credentials = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return [];
        }

        if (! is_array($credentials)) {
            return [];
        }

        $values = [];

        foreach (['client_email', 'private_key', 'private_key_id', 'client_id', 'token_uri'] as $key) {
            $value = $credentials[$key] ?? null;

            if (is_string($value)) {
                $values[] = $value;
            }
        }

        return $values;
    }

    /**
     * @return array<non-empty-string, non-empty-string>
     */
    private function patterns(): array
    {
        return [
            '/\b(Authorization)(\s*[:=]\s*)(Bearer|Basic)\s+[^\s,;"]+/i' => '$1$2$3 [redacted]',
            '/\b(api[_-]?key|access[_-]?token|refresh[_-]?token|client[_-]?secret|private[_-]?key|private[_-]?key[_-]?id|assertion|secret|token)(\s*["\']?\s*[:=]\s*["\']?)[^"\'\s,;}{]+/i' => '$1$2[redacted]',
        ];
    }
}
