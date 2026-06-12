<?php

declare(strict_types=1);

namespace Capell\Payments\Actions;

use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

final class RedactPaymentErrorMessageAction
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
        $values = [
            ResolvePaymentSettingAction::run('capell-payments.stripe.secret_key', 'stripe_secret_key'),
            ResolvePaymentSettingAction::run('capell-payments.stripe.webhook_secret', 'stripe_webhook_secret'),
        ];

        return array_values(array_unique(array_filter(
            $values,
            static fn (mixed $value): bool => is_string($value) && $value !== '',
        )));
    }

    /**
     * @return array<non-empty-string, non-empty-string>
     */
    private function patterns(): array
    {
        return [
            '/\b(Authorization)(\s*[:=]\s*)(Bearer|Basic)\s+[^\s,;"]+/i' => '$1$2$3 [redacted]',
            '/\b(stripe[_-]?secret[_-]?key|stripe[_-]?webhook[_-]?secret|client[_-]?secret|webhook[_-]?secret|secret|token)(\s*["\']?\s*[:=]\s*["\']?)[^"\'\s,;}{]+/i' => '$1$2[redacted]',
            '/\b(?:sk|rk|whsec)_(?:test|live)_[A-Za-z0-9_]+/i' => '[redacted]',
        ];
    }
}
