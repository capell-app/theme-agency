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
     * Build a structured, non-sensitive summary suitable for persistence.
     *
     * Stores the exception class plus any Stripe error code/type (both
     * non-sensitive identifiers) and a truncated, redacted short message
     * rather than the raw free-form provider text, which can carry secrets.
     */
    public function summary(Throwable $throwable, int $shortMessageLength = 200): string
    {
        $segments = [$throwable::class];

        $stripeType = $this->stripeErrorType($throwable);

        if ($stripeType !== null) {
            $segments[] = 'type=' . $stripeType;
        }

        $stripeCode = $this->stripeErrorCode($throwable);

        if ($stripeCode !== null) {
            $segments[] = 'code=' . $stripeCode;
        }

        $shortMessage = $this->redactedShortMessage($throwable, $shortMessageLength);

        if ($shortMessage !== '') {
            $segments[] = $shortMessage;
        }

        return implode(' | ', $segments);
    }

    private function redactedShortMessage(Throwable $throwable, int $shortMessageLength): string
    {
        $redacted = trim($this->handle($throwable->getMessage()));

        if ($redacted === '') {
            return '';
        }

        if (mb_strlen($redacted) > $shortMessageLength) {
            $redacted = mb_substr($redacted, 0, $shortMessageLength) . '…';
        }

        return $redacted;
    }

    /**
     * Stripe SDK exceptions expose a non-sensitive error code via getStripeCode().
     */
    private function stripeErrorCode(Throwable $throwable): ?string
    {
        if (! method_exists($throwable, 'getStripeCode')) {
            return null;
        }

        $code = $throwable->getStripeCode();

        return is_string($code) && $code !== '' ? $code : null;
    }

    /**
     * Stripe SDK exceptions expose a non-sensitive error object via getError()
     * whose ->type categorises the failure (e.g. card_error, api_error).
     */
    private function stripeErrorType(Throwable $throwable): ?string
    {
        if (! method_exists($throwable, 'getError')) {
            return null;
        }

        $error = $throwable->getError();

        $type = is_object($error) && isset($error->type) ? $error->type : null;

        return is_string($type) && $type !== '' ? $type : null;
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
            // Authorization / bare Bearer + Basic credentials.
            '/\b(Authorization)(\s*[:=]\s*)(Bearer|Basic)\s+[^\s,;"]+/i' => '$1$2$3 [redacted]',
            '/\bBearer\s+[A-Za-z0-9._~+\/=-]{8,}/i' => 'Bearer [redacted]',
            // Named secret-like key/value heuristic.
            '/\b(stripe[_-]?secret[_-]?key|stripe[_-]?webhook[_-]?secret|client[_-]?secret|webhook[_-]?secret|access[_-]?token|api[_-]?key|secret|token|password)(\s*["\']?\s*[:=]\s*["\']?)[^"\'\s,;}{]+/i' => '$1$2[redacted]',
            // Stripe key prefixes (incl. publishable, restricted and OAuth/connected-account forms),
            // both raw and URL-encoded (sk%5Ftest%5F… / sk_test…).
            '/\b(?:sk|rk|pk|whsec|ak|ek|pi|seti|cus|sub|acct|sub_sched)(?:_|%5[Ff])(?:test|live)(?:_|%5[Ff])[A-Za-z0-9_%]+/i' => '[redacted]',
            '/\b(?:sk|rk|pk|whsec)(?:_|%5[Ff])[A-Za-z0-9_%]{16,}/i' => '[redacted]',
            // Backstop: long opaque high-entropy tokens (>= 24 chars, mixed letters+digits)
            // that survived the heuristics above. Conservative to avoid eating prose.
            '/\b(?=[A-Za-z0-9_-]{24,}\b)(?=[A-Za-z0-9_-]*[A-Za-z])(?=[A-Za-z0-9_-]*\d)[A-Za-z0-9_-]{24,}\b/' => '[redacted]',
        ];
    }
}
