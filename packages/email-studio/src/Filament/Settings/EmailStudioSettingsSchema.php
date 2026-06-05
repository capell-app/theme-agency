<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Filament\Settings;

use Capell\Admin\Filament\Contracts\HasSchema;
use Capell\Admin\Filament\Support\HelperText;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class EmailStudioSettingsSchema implements HasSchema
{
    public static function make(Schema $configurator): array
    {
        return [
            Section::make(__('capell-email-studio::settings.mail_tracker'))
                ->columnSpanFull()
                ->schema([
                    Grid::make(2)
                        ->schema([
                            HelperText::apply(
                                Toggle::make('mail_tracker_inject_pixel')
                                    ->label(__('capell-email-studio::settings.mail_tracker_inject_pixel')),
                                'capell-email-studio::settings.mail_tracker_inject_pixel_helper',
                            ),
                            HelperText::apply(
                                Toggle::make('mail_tracker_track_links')
                                    ->label(__('capell-email-studio::settings.mail_tracker_track_links')),
                                'capell-email-studio::settings.mail_tracker_track_links_helper',
                            ),
                            HelperText::apply(
                                Toggle::make('mail_tracker_log_content')
                                    ->label(__('capell-email-studio::settings.mail_tracker_log_content')),
                                'capell-email-studio::settings.mail_tracker_log_content_helper',
                            ),
                            Select::make('mail_tracker_log_content_strategy')
                                ->label(__('capell-email-studio::settings.mail_tracker_log_content_strategy'))
                                ->options([
                                    'database' => __('capell-email-studio::settings.content_strategy_database'),
                                    'filesystem' => __('capell-email-studio::settings.content_strategy_filesystem'),
                                ])
                                ->required(),
                            TextInput::make('mail_tracker_filesystem')
                                ->label(__('capell-email-studio::settings.mail_tracker_filesystem'))
                                ->maxLength(255)
                                ->required(),
                            TextInput::make('mail_tracker_filesystem_folder')
                                ->label(__('capell-email-studio::settings.mail_tracker_filesystem_folder'))
                                ->maxLength(255)
                                ->required(),
                            TextInput::make('mail_tracker_queue')
                                ->label(__('capell-email-studio::settings.mail_tracker_queue'))
                                ->maxLength(255)
                                ->required(),
                            TextInput::make('mail_tracker_content_max_size')
                                ->label(__('capell-email-studio::settings.mail_tracker_content_max_size'))
                                ->integer()
                                ->minValue(1)
                                ->suffix(__('capell-email-studio::settings.bytes')),
                            TextInput::make('mail_tracker_search_date_start_days')
                                ->label(__('capell-email-studio::settings.mail_tracker_search_date_start_days'))
                                ->integer()
                                ->minValue(1)
                                ->suffix(__('capell-admin::form.days')),
                            TextInput::make('mail_tracker_purge_retention_days')
                                ->label(__('capell-email-studio::settings.mail_tracker_purge_retention_days'))
                                ->helperText(__('capell-email-studio::settings.mail_tracker_purge_retention_days_helper'))
                                ->integer()
                                ->minValue(0)
                                ->suffix(__('capell-admin::form.days')),
                        ]),
                ]),
        ];
    }
}
