<?php

declare(strict_types=1);

namespace Capell\GA4Reports\Filament\Settings;

use Capell\Admin\Filament\Contracts\HasSchema;
use Capell\Admin\Filament\Support\HelperText;
use Capell\GA4Reports\Actions\CheckGA4ReportsCredentialsPathAction;
use Closure;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

final class GA4ReportsSettingsSchema implements HasSchema
{
    public static function make(Schema $configurator): array
    {
        return [
            Grid::make(2)
                ->columnSpanFull()
                ->schema([
                    HelperText::apply(
                        Toggle::make('enabled')
                            ->label(__('capell-ga4-reports::settings.enabled')),
                        'capell-ga4-reports::settings.enabled_helper',
                    ),
                    TextInput::make('property_id')
                        ->label(__('capell-ga4-reports::settings.property_id'))
                        ->helperText(__('capell-ga4-reports::settings.property_id_helper')),
                    TextInput::make('credentials_path')
                        ->label(__('capell-ga4-reports::settings.credentials_path'))
                        ->helperText(__('capell-ga4-reports::settings.credentials_path_helper'))
                        ->maxLength(1024)
                        ->rules([self::credentialsPathRule()]),
                    TextInput::make('sync_days')
                        ->label(__('capell-ga4-reports::settings.sync_days'))
                        ->integer()
                        ->minValue(1)
                        ->suffix(__('capell-admin::form.days')),
                    TextInput::make('sync_cron')
                        ->label(__('capell-ga4-reports::settings.sync_cron'))
                        ->helperText(__('capell-ga4-reports::settings.sync_cron_helper'))
                        ->required(),
                    TextInput::make('route_slug')
                        ->label(__('capell-ga4-reports::settings.route_slug'))
                        ->required(),
                ]),
        ];
    }

    private static function credentialsPathRule(): Closure
    {
        return static function (string $attribute, mixed $value, Closure $fail): void {
            if ($value === null) {
                return;
            }

            if (! is_string($value) && ! is_numeric($value)) {
                $fail((string) __('capell-ga4-reports::settings.credentials_path_not_readable'));

                return;
            }

            $path = trim((string) $value);

            if ($path === '') {
                return;
            }

            $status = CheckGA4ReportsCredentialsPathAction::run($path);

            if (! $status->valid) {
                $fail((string) __($status->messageKey));
            }
        };
    }
}
