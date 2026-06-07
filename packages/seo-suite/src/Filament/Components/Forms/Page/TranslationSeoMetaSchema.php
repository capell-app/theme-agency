<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Filament\Components\Forms\Page;

use Capell\Admin\Filament\Support\HelperText;
use Capell\SeoSuite\Settings\SeoSuiteSettings;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;

class TranslationSeoMetaSchema
{
    /**
     * @return array<array-key, mixed>
     */
    public static function make(): array
    {
        return [
            HelperText::apply(
                TextInput::make('title')
                    ->label(__('capell-admin::form.meta_title.label'))
                    ->maxLength(160)
                    ->columnSpanFull()
                    ->helperCountText()
                    ->requiredBasedOnType(),
                'capell-admin::form.meta_title.helper',
            ),
            HelperText::apply(
                Textarea::make('description')
                    ->label(__('capell-admin::form.meta_description.label'))
                    ->maxLength(fn (): int => resolve(SeoSuiteSettings::class)->metaDescriptionMaximumLength())
                    ->rows(5)
                    ->columnSpanFull()
                    ->helperCountText(),
                'capell-admin::form.meta_description.helper',
            ),
            Section::make(__('capell-admin::generic.social_sharing'))
                ->icon(Heroicon::OutlinedShare)
                ->collapsed()
                ->compact()
                ->columnSpanFull()
                ->columns()
                ->schema([
                    TextInput::make('social_title')
                        ->label(__('capell-admin::form.social_title'))
                        ->helperText(__('capell-admin::generic.social_title_info'))
                        ->maxLength(70)
                        ->helperCountText(),
                    Textarea::make('social_description')
                        ->label(__('capell-admin::form.social_description'))
                        ->helperText(__('capell-admin::generic.social_description_info'))
                        ->maxLength(200)
                        ->rows(3)
                        ->helperCountText(),
                ]),
        ];
    }
}
