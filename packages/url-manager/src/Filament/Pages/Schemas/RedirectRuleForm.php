<?php

declare(strict_types=1);

namespace Capell\UrlManager\Filament\Pages\Schemas;

use Capell\UrlManager\Enums\RedirectMatchType;
use Capell\UrlManager\Enums\RedirectRuleStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

final class RedirectRuleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(['default' => 1, 'lg' => 2])
            ->schema([
                TextInput::make('source_url')
                    ->label(__('capell-url-manager::table.source_url'))
                    ->required()
                    ->maxLength(2048)
                    ->columnSpanFull(),
                TextInput::make('target_url')
                    ->label(__('capell-url-manager::table.target_url'))
                    ->required()
                    ->maxLength(2048)
                    ->columnSpanFull(),
                Select::make('status_code')
                    ->label(__('capell-url-manager::table.status_code'))
                    ->options([
                        301 => '301',
                        302 => '302',
                        307 => '307',
                        308 => '308',
                    ])
                    ->default(301)
                    ->required(),
                Select::make('match_type')
                    ->label(__('capell-url-manager::table.match_type'))
                    ->options(RedirectMatchType::class)
                    ->default(RedirectMatchType::Exact->value)
                    ->required(),
                Select::make('status')
                    ->label(__('capell-url-manager::table.status'))
                    ->options(RedirectRuleStatus::class)
                    ->default(RedirectRuleStatus::Active->value)
                    ->required(),
                Toggle::make('preserve_query')
                    ->label(__('capell-url-manager::table.preserve_query'))
                    ->default(true),
                Textarea::make('notes')
                    ->label(__('capell-url-manager::table.notes'))
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}
