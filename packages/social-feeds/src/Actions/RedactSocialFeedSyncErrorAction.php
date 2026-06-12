<?php

declare(strict_types=1);

namespace Capell\SocialFeeds\Actions;

use Capell\SocialFeeds\Models\SocialFeedConnection;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

final class RedactSocialFeedSyncErrorAction
{
    use AsAction;

    public function handle(Throwable|string $error, SocialFeedConnection $connection): string
    {
        $redacted = $error instanceof Throwable ? $error->getMessage() : $error;

        foreach ($this->knownSensitiveValues($connection) as $value) {
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
    private function knownSensitiveValues(SocialFeedConnection $connection): array
    {
        return array_values(array_unique(array_filter(
            $this->scalarValues($connection->credentials),
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
            '/\b(api[_-]?key|access[_-]?token|refresh[_-]?token|client[_-]?secret|secret|token)(\s*["\']?\s*[:=]\s*["\']?)[^"\'\s,;}{]+/i' => '$1$2[redacted]',
        ];
    }
}
