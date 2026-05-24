<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Settings;

use Capell\Core\Contracts\SettingsContract;
use Capell\ShopifyCommerce\Filament\Settings\ShopifyCommerceSettingsSchema;
use Spatie\LaravelSettings\Settings;

final class ShopifyCommerceSettings extends Settings implements SettingsContract
{
    public string $api_version = '2026-04';

    /** @var array<int, string> */
    public array $default_scopes = ['read_products'];

    public int $search_cache_ttl_minutes = 5;

    public static function group(): string
    {
        return 'shopify_commerce';
    }

    public static function schema(): string
    {
        return ShopifyCommerceSettingsSchema::class;
    }
}
