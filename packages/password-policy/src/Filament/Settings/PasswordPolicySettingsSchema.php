<?php

declare(strict_types=1);

namespace Capell\PasswordPolicy\Filament\Settings;

use Capell\Admin\Filament\Contracts\HasSchema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class PasswordPolicySettingsSchema implements HasSchema
{
    public static function make(Schema $schema): array
    {
        return [
            Grid::make(2)
                ->columnSpanFull()
                ->schema([
                    Toggle::make('password_expiry_enabled')
                        ->label(__('capell-password-policy::settings.password_expiry_enabled')),
                    TextInput::make('password_expiry_days')
                        ->label(__('capell-password-policy::settings.password_expiry_days'))
                        ->integer()
                        ->minValue(1)
                        ->required()
                        ->visible(fn (callable $get): bool => (bool) $get('password_expiry_enabled')),
                    Toggle::make('password_expiry_warning_notifications_enabled')
                        ->label(__('capell-password-policy::settings.password_expiry_warning_notifications_enabled'))
                        ->visible(fn (callable $get): bool => (bool) $get('password_expiry_enabled')),
                    TextInput::make('password_expiry_warning_days')
                        ->label(__('capell-password-policy::settings.password_expiry_warning_days'))
                        ->integer()
                        ->minValue(1)
                        ->required()
                        ->visible(fn (callable $get): bool => (bool) $get('password_expiry_enabled') && (bool) $get('password_expiry_warning_notifications_enabled')),
                ]),
            Grid::make(2)
                ->columnSpanFull()
                ->schema([
                    Toggle::make('force_change_enabled')
                        ->label(__('capell-password-policy::settings.force_change_enabled')),
                ]),
            Grid::make(2)
                ->columnSpanFull()
                ->schema([
                    TextInput::make('minimum_password_length')
                        ->label(__('capell-password-policy::settings.minimum_password_length'))
                        ->integer()
                        ->minValue(8)
                        ->required(),
                    Toggle::make('require_mixed_case')
                        ->label(__('capell-password-policy::settings.require_mixed_case')),
                    Toggle::make('require_numbers')
                        ->label(__('capell-password-policy::settings.require_numbers')),
                    Toggle::make('require_symbols')
                        ->label(__('capell-password-policy::settings.require_symbols')),
                ]),
            Grid::make(2)
                ->columnSpanFull()
                ->schema([
                    Toggle::make('compromised_password_checks_enabled')
                        ->label(__('capell-password-policy::settings.compromised_password_checks_enabled')),
                    Toggle::make('password_history_enabled')
                        ->label(__('capell-password-policy::settings.password_history_enabled')),
                    TextInput::make('password_history_count')
                        ->label(__('capell-password-policy::settings.password_history_count'))
                        ->integer()
                        ->minValue(1)
                        ->required()
                        ->visible(fn (callable $get): bool => (bool) $get('password_history_enabled')),
                ]),
        ];
    }
}
