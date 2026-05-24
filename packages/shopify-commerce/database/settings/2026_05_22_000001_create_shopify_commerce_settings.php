<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        if (! $this->migrator->exists('shopify_commerce.api_version')) {
            $this->migrator->add('shopify_commerce.api_version', '2026-04');
        }

        if (! $this->migrator->exists('shopify_commerce.default_scopes')) {
            $this->migrator->add('shopify_commerce.default_scopes', ['read_products']);
        }

        if (! $this->migrator->exists('shopify_commerce.search_cache_ttl_minutes')) {
            $this->migrator->add('shopify_commerce.search_cache_ttl_minutes', 5);
        }
    }
};
