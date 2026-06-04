<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Actions\CommandPalette;

use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static string run(string $output)
 */
final class RedactCommandPaletteOutputAction
{
    use AsAction;

    private const string REDACTION = '[redacted]';

    /**
     * @var list<string>
     */
    private const array SECRET_KEY_PARTS = [
        'secret',
        'token',
        'password',
        'passwd',
        'pwd',
        'credential',
        'private_key',
        'api_key',
        'access_key',
        'client_secret',
        'webhook_secret',
        'signing_secret',
    ];

    public function handle(string $output): string
    {
        $redacted = $this->redactBearerTokens($output);
        $redacted = $this->redactCredentialUrls($redacted);

        return collect(preg_split('/\R/', $redacted) ?: [])
            ->map(fn (string $line): string => $this->redactSecretLine($line))
            ->implode(PHP_EOL);
    }

    private function redactBearerTokens(string $output): string
    {
        return (string) preg_replace(
            '/\bBearer\s+[A-Za-z0-9._~+\/=-]{8,}\b/',
            'Bearer ' . self::REDACTION,
            $output,
        );
    }

    private function redactCredentialUrls(string $output): string
    {
        return (string) preg_replace(
            '/\b([a-z][a-z0-9+.-]*:\/\/)([^:\s\/@]+):([^@\s\/]+)@/i',
            '$1$2:' . self::REDACTION . '@',
            $output,
        );
    }

    private function redactSecretLine(string $line): string
    {
        foreach (self::SECRET_KEY_PARTS as $secretKeyPart) {
            if (! str_contains(mb_strtolower($line), $secretKeyPart)) {
                continue;
            }

            $line = (string) preg_replace(
                '/^(\s*[A-Za-z0-9_.-]*(?:secret|token|password|passwd|pwd|credential|private_key|api_key|access_key|client_secret|webhook_secret|signing_secret)[A-Za-z0-9_.-]*\s*[:=]\s*)(.*)$/i',
                '$1' . self::REDACTION,
                $line,
            );
            $line = (string) preg_replace(
                '/([\'"]?(?:secret|token|password|passwd|pwd|credential|private_key|api_key|access_key|client_secret|webhook_secret|signing_secret)[A-Za-z0-9_.-]*[\'"]?\s*=>\s*)([^,\]}]+)/i',
                '$1' . self::REDACTION,
                $line,
            );
        }

        return $line;
    }
}
