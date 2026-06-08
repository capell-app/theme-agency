<?php

declare(strict_types=1);

namespace Capell\Payments\Actions;

use JsonException;
use Lorisleiva\Actions\Concerns\AsAction;
use RuntimeException;

/**
 * @method static string run(string $operation, array<string, mixed> $payload)
 */
final class GeneratePaymentGatewayIdempotencyKeyAction
{
    use AsAction;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function handle(string $operation, array $payload): string
    {
        $encodedPayload = json_encode($this->normalize($payload), JSON_THROW_ON_ERROR);

        return sprintf(
            'capell-%s-%s',
            preg_replace('/[^a-z0-9-]+/', '-', strtolower($operation)) ?: 'payment-operation',
            substr(hash('sha256', $encodedPayload), 0, 48),
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function normalize(array $payload): array
    {
        ksort($payload);

        foreach ($payload as $key => $value) {
            if (is_array($value)) {
                /** @var array<string, mixed> $nested */
                $nested = $value;
                $payload[$key] = $this->normalize($nested);
            }
        }

        try {
            json_encode($payload, JSON_THROW_ON_ERROR);
        } catch (JsonException $jsonException) {
            throw new RuntimeException('Payment gateway idempotency payload could not be encoded.', $jsonException->getCode(), previous: $jsonException);
        }

        return $payload;
    }
}
