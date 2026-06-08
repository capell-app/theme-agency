<?php

declare(strict_types=1);

namespace Capell\UrlManager\Filament\Pages\Schemas;

use Capell\UrlManager\Actions\PrepareRedirectRuleDataAction;
use Capell\UrlManager\Data\RedirectRuleData;
use Capell\UrlManager\Enums\RedirectMatchType;
use Capell\UrlManager\Enums\RedirectRuleStatus;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use InvalidArgumentException;

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
                    ->rules([
                        fn (Get $get): Closure => function (string $attribute, mixed $value, Closure $fail) use ($get): void {
                            if ($get('match_type') !== RedirectMatchType::Regex->value) {
                                return;
                            }

                            try {
                                PrepareRedirectRuleDataAction::make()->normalizeRegexSource((string) $value);
                            } catch (InvalidArgumentException $invalidArgumentException) {
                                $fail($invalidArgumentException->getMessage());
                            }
                        },
                    ])
                    ->columnSpanFull(),
                TextInput::make('target_url')
                    ->label(__('capell-url-manager::table.target_url'))
                    ->required(fn (Get $get): bool => self::nullableInt($get('status_code')) !== 410)
                    ->maxLength(2048)
                    ->rules([
                        fn (Get $get): Closure => function (string $attribute, mixed $value, Closure $fail) use ($get): void {
                            try {
                                PrepareRedirectRuleDataAction::run(self::redirectRuleDataFromFormState($get));
                            } catch (InvalidArgumentException $invalidArgumentException) {
                                $fail($invalidArgumentException->getMessage());
                            }
                        },
                    ])
                    ->columnSpanFull(),
                Select::make('status_code')
                    ->label(__('capell-url-manager::table.status_code'))
                    ->options([
                        301 => '301',
                        302 => '302',
                        307 => '307',
                        308 => '308',
                        410 => '410',
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
                TextInput::make('priority')
                    ->label(__('capell-url-manager::table.priority'))
                    ->integer()
                    ->default(0)
                    ->minValue(-1000)
                    ->maxValue(1000)
                    ->helperText(__('capell-url-manager::generic.priority_help')),
                Toggle::make('preserve_query')
                    ->label(__('capell-url-manager::table.preserve_query'))
                    ->default(true),
                Textarea::make('notes')
                    ->label(__('capell-url-manager::table.notes'))
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    private static function redirectRuleDataFromFormState(Get $get): RedirectRuleData
    {
        return new RedirectRuleData(
            sourceUrl: (string) ($get('source_url') ?? ''),
            targetUrl: (string) ($get('target_url') ?? ''),
            siteId: self::nullableInt($get('site_id')),
            languageId: self::nullableInt($get('language_id')),
            statusCode: self::nullableInt($get('status_code')) ?? 301,
            matchType: RedirectMatchType::from((string) ($get('match_type') ?? RedirectMatchType::Exact->value)),
            status: RedirectRuleStatus::from((string) ($get('status') ?? RedirectRuleStatus::Active->value)),
            priority: self::nullableInt($get('priority')) ?? 0,
            preserveQuery: (bool) ($get('preserve_query') ?? true),
            notes: is_string($get('notes')) && trim($get('notes')) !== '' ? $get('notes') : null,
        );
    }

    private static function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_int($value)) {
            return $value;
        }

        return is_numeric($value) ? (int) $value : null;
    }
}
