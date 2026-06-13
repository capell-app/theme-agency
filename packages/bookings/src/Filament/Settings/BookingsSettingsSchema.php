<?php

declare(strict_types=1);

namespace Capell\Bookings\Filament\Settings;

use Capell\Admin\Filament\Contracts\HasSchema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BookingsSettingsSchema implements HasSchema
{
    /**
     * @return array<int, mixed>
     */
    public static function make(Schema $schema): array
    {
        return [
            Section::make(__('capell-bookings::settings.travel'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('travel_provider')
                                ->label(__('capell-bookings::settings.travel_provider'))
                                ->options([
                                    'osrm' => __('capell-bookings::settings.travel_provider_osrm'),
                                    'google_routes' => __('capell-bookings::settings.travel_provider_google_routes'),
                                    'mapbox' => __('capell-bookings::settings.travel_provider_mapbox'),
                                ])
                                ->required(),
                            TextInput::make('travel_cache_bucket_minutes')
                                ->label(__('capell-bookings::settings.travel_cache_bucket_minutes'))
                                ->integer()
                                ->minValue(5)
                                ->required(),
                            TextInput::make('travel_observation_retention_days')
                                ->label(__('capell-bookings::settings.travel_observation_retention_days'))
                                ->integer()
                                ->minValue(30)
                                ->required(),
                        ]),
                ]),
            Section::make(__('capell-bookings::settings.day_planning'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('lunch_duration_minutes')
                                ->label(__('capell-bookings::settings.lunch_duration_minutes'))
                                ->integer()
                                ->minValue(0)
                                ->required(),
                            TextInput::make('lunch_window_starts_at')
                                ->label(__('capell-bookings::settings.lunch_window_starts_at'))
                                ->required(),
                            TextInput::make('lunch_window_ends_at')
                                ->label(__('capell-bookings::settings.lunch_window_ends_at'))
                                ->required(),
                            TextInput::make('fuel_rate_pence_per_mile')
                                ->label(__('capell-bookings::settings.fuel_rate_pence_per_mile'))
                                ->integer()
                                ->minValue(0)
                                ->required(),
                        ]),
                ]),
            Section::make(__('capell-bookings::settings.workflow'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('hold_expiry_minutes')
                                ->label(__('capell-bookings::settings.hold_expiry_minutes'))
                                ->integer()
                                ->minValue(5)
                                ->required(),
                            TextInput::make('default_group_capacity')
                                ->label(__('capell-bookings::settings.default_group_capacity'))
                                ->integer()
                                ->minValue(1)
                                ->required(),
                            TextInput::make('message_log_retention_days')
                                ->label(__('capell-bookings::settings.message_log_retention_days'))
                                ->integer()
                                ->minValue(30)
                                ->required(),
                        ]),
                ]),
            Section::make(__('capell-bookings::settings.channels'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Toggle::make('sms_enabled')
                                ->label(__('capell-bookings::settings.sms_enabled')),
                            Toggle::make('whatsapp_enabled')
                                ->label(__('capell-bookings::settings.whatsapp_enabled')),
                            Toggle::make('ai_suggestions_enabled')
                                ->label(__('capell-bookings::settings.ai_suggestions_enabled')),
                            Toggle::make('facebook_events_enabled')
                                ->label(__('capell-bookings::settings.facebook_events_enabled')),
                        ]),
                ]),
        ];
    }
}
