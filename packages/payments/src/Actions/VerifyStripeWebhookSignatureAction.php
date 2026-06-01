<?php

declare(strict_types=1);

namespace Capell\Payments\Actions;

use Capell\Payments\Exceptions\StripeWebhookSignatureException;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use JsonException;
use Lorisleiva\Actions\Concerns\AsAction;

final class VerifyStripeWebhookSignatureAction
{
    use AsAction;

    /**
     * @return array<string, mixed>
     */
    public function handle(
        string $payload,
        ?string $signatureHeader,
        string $endpointSecret,
        int $toleranceSeconds = 300,
        ?CarbonInterface $now = null,
    ): array {
        if ($signatureHeader === null || trim($signatureHeader) === '') {
            throw StripeWebhookSignatureException::missingSignatureHeader();
        }

        $signatureParts = $this->signatureParts($signatureHeader);
        $timestamp = isset($signatureParts['t'][0]) ? (int) $signatureParts['t'][0] : null;

        if ($timestamp === null || $timestamp <= 0) {
            throw StripeWebhookSignatureException::missingTimestamp();
        }

        $currentTimestamp = ($now ?? CarbonImmutable::now())->getTimestamp();

        if (abs($currentTimestamp - $timestamp) > $toleranceSeconds) {
            throw StripeWebhookSignatureException::staleTimestamp();
        }

        $expectedSignature = hash_hmac('sha256', $timestamp . '.' . $payload, $endpointSecret);

        foreach ($signatureParts['v1'] ?? [] as $providedSignature) {
            if (hash_equals($expectedSignature, $providedSignature)) {
                return $this->decodePayload($payload);
            }
        }

        throw StripeWebhookSignatureException::invalidSignature();
    }

    /**
     * @return array<string, list<string>>
     */
    private function signatureParts(string $signatureHeader): array
    {
        $parts = [];

        foreach (explode(',', $signatureHeader) as $signaturePart) {
            [$key, $value] = array_pad(explode('=', trim($signaturePart), 2), 2, null);
            if (! is_string($key)) {
                continue;
            }

            if ($key === '') {
                continue;
            }

            if (! is_string($value)) {
                continue;
            }

            if ($value === '') {
                continue;
            }

            $parts[$key] ??= [];
            $parts[$key][] = $value;
        }

        return $parts;
    }

    /**
     * @return array<string, mixed>
     */
    private function decodePayload(string $payload): array
    {
        try {
            $decodedPayload = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw StripeWebhookSignatureException::invalidPayload();
        }

        if (! is_array($decodedPayload)) {
            throw StripeWebhookSignatureException::invalidPayload();
        }

        return $decodedPayload;
    }
}
