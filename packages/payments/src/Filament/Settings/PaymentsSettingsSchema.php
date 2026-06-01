<?php

declare(strict_types=1);

namespace Capell\Payments\Filament\Settings;

use Capell\Admin\Filament\Contracts\HasSchema;
use Capell\Admin\Filament\Support\HelperText;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

final class PaymentsSettingsSchema implements HasSchema
{
    public static function make(Schema $schema): array
    {
        return [
            Grid::make(2)
                ->columnSpanFull()
                ->schema([
                    HelperText::apply(
                        TextInput::make('stripe_secret_key')
                            ->label(__('capell-payments::settings.stripe_secret_key'))
                            ->password()
                            ->revealable(),
                        'capell-payments::settings.stripe_secret_key_helper',
                    ),
                    TextInput::make('stripe_publishable_key')
                        ->label(__('capell-payments::settings.stripe_publishable_key')),
                    HelperText::apply(
                        TextInput::make('stripe_webhook_secret')
                            ->label(__('capell-payments::settings.stripe_webhook_secret'))
                            ->password()
                            ->revealable(),
                        'capell-payments::settings.stripe_webhook_secret_helper',
                    ),
                    TextInput::make('webhook_freshness_hours')
                        ->label(__('capell-payments::settings.webhook_freshness_hours'))
                        ->integer()
                        ->minValue(1)
                        ->suffix(__('capell-admin::form.hours')),
                    TextInput::make('stripe_api_base_url')
                        ->label(__('capell-payments::settings.stripe_api_base_url'))
                        ->url(),
                    TextInput::make('stripe_api_version')
                        ->label(__('capell-payments::settings.stripe_api_version')),
                    TextInput::make('stripe_timeout')
                        ->label(__('capell-payments::settings.stripe_timeout'))
                        ->integer()
                        ->minValue(1),
                    TextInput::make('stripe_connect_timeout')
                        ->label(__('capell-payments::settings.stripe_connect_timeout'))
                        ->integer()
                        ->minValue(1),
                ]),
        ];
    }
}
