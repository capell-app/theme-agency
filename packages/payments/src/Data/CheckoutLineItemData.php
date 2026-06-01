<?php

declare(strict_types=1);

namespace Capell\Payments\Data;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
final class CheckoutLineItemData extends Data
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public string $name,
        public int $amount,
        public string $currency,
        public int $quantity = 1,
        public ?string $description = null,
        public ?string $providerPriceId = null,
        public array $metadata = [],
    ) {}
}
