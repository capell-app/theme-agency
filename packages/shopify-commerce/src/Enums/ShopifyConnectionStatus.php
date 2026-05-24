<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Enums;

use Filament\Support\Contracts\HasLabel;

enum ShopifyConnectionStatus: string implements HasLabel
{
    case Connecting = 'connecting';
    case Active = 'active';
    case Revoked = 'revoked';
    case Error = 'error';

    public function getLabel(): string
    {
        return match ($this) {
            self::Connecting => __('capell-shopify-commerce::capell-shopify-commerce.status.connecting'),
            self::Active => __('capell-shopify-commerce::capell-shopify-commerce.status.active'),
            self::Revoked => __('capell-shopify-commerce::capell-shopify-commerce.status.revoked'),
            self::Error => __('capell-shopify-commerce::capell-shopify-commerce.status.error'),
        };
    }
}
