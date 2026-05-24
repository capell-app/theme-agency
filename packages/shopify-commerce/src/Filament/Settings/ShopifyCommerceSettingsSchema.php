<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Filament\Settings;

use Capell\Admin\Filament\Contracts\HasSchema;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

final class ShopifyCommerceSettingsSchema implements HasSchema
{
    public static function make(Schema $configurator): array
    {
        return [
            Grid::make(2)
                ->columnSpanFull()
                ->schema([
                    TextInput::make('api_version')
                        ->label(__('capell-shopify-commerce::capell-shopify-commerce.settings.api_version'))
                        ->required(),
                    TextInput::make('search_cache_ttl_minutes')
                        ->label(__('capell-shopify-commerce::capell-shopify-commerce.settings.search_cache_ttl_minutes'))
                        ->integer()
                        ->minValue(1)
                        ->maxValue(1440)
                        ->required(),
                    TagsInput::make('default_scopes')
                        ->label(__('capell-shopify-commerce::capell-shopify-commerce.settings.default_scopes'))
                        ->required()
                        ->columnSpanFull(),
                ]),
        ];
    }
}
