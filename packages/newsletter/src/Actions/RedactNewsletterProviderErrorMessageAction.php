<?php

declare(strict_types=1);

namespace Capell\Newsletter\Actions;

use Capell\Newsletter\Models\ProviderConnection;
use Capell\Newsletter\Models\Subscriber;
use Lorisleiva\Actions\Concerns\AsAction;

final class RedactNewsletterProviderErrorMessageAction
{
    use AsAction;

    public function handle(string $message, ?ProviderConnection $connection = null, ?Subscriber $subscriber = null): string
    {
        $redacted = $message;

        foreach ($this->knownSensitiveValues($connection, $subscriber) as $value) {
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
    private function knownSensitiveValues(?ProviderConnection $connection, ?Subscriber $subscriber): array
    {
        $values = [];

        if ($connection instanceof ProviderConnection) {
            $values = [
                ...$values,
                ...$this->scalarValues($connection->credentials),
                ...$this->scalarValues($connection->oauth_tokens),
            ];

            if (is_string($connection->webhook_secret)) {
                $values[] = $connection->webhook_secret;
            }
        }

        if ($subscriber instanceof Subscriber && is_string($subscriber->email)) {
            $values[] = $subscriber->email;
        }

        return array_values(array_unique(array_filter(
            $values,
            static fn (string $value): bool => mb_strlen($value) >= 8,
        )));
    }

    /**
     * @param  array<array-key, mixed>|null  $values
     * @return list<string>
     */
    private function scalarValues(?array $values): array
    {
        if ($values === null) {
            return [];
        }

        $scalars = [];

        array_walk_recursive($values, static function (mixed $value) use (&$scalars): void {
            if (is_scalar($value)) {
                $scalars[] = (string) $value;
            }
        });

        return $scalars;
    }

    /**
     * @return array<non-empty-string, non-empty-string>
     */
    private function patterns(): array
    {
        return [
            '/\b(Authorization)(\s*[:=]\s*)(Bearer|Basic)\s+[^\s,;"]+/i' => '$1$2$3 [redacted]',
            '/\b(api[_-]?key|access[_-]?token|refresh[_-]?token|client[_-]?secret|webhook[_-]?secret|secret|token)(\s*["\']?\s*[:=]\s*["\']?)[^"\'\s,;}{]+/i' => '$1$2[redacted]',
        ];
    }
}
