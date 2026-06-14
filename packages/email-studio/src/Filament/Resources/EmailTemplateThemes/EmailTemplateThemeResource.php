<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Filament\Resources\EmailTemplateThemes;

use BackedEnum;
use Capell\Core\Facades\CapellCore;
use Capell\EmailStudio\Actions\CaptureEmailTemplateThemeScreenshotAction;
use Capell\EmailStudio\Actions\CreateDefaultEmailTemplateThemeAction;
use Capell\EmailStudio\Contracts\CapturesEmailTemplateThemeScreenshots;
use Capell\EmailStudio\Filament\Resources\EmailTemplateThemes\Pages\CreateEmailTemplateTheme;
use Capell\EmailStudio\Filament\Resources\EmailTemplateThemes\Pages\EditEmailTemplateTheme;
use Capell\EmailStudio\Filament\Resources\EmailTemplateThemes\Pages\ListEmailTemplateThemes;
use Capell\EmailStudio\Models\EmailTemplateTheme;
use Capell\EmailStudio\Providers\EmailStudioServiceProvider;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Override;

final class EmailTemplateThemeResource extends Resource
{
    protected static ?string $slug = 'email-studio/template-themes';

    protected static ?string $model = EmailTemplateTheme::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSwatch;

    protected static ?int $navigationSort = 44;

    protected static ?string $recordTitleAttribute = 'name';

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('site_scope_key')
                ->label(__('capell-email-studio::templates.fields.site_scope'))
                ->default('global')
                ->required()
                ->maxLength(255),
            TextInput::make('key')
                ->label(__('capell-email-studio::templates.fields.key'))
                ->required()
                ->maxLength(255),
            TextInput::make('name')
                ->label(__('capell-email-studio::templates.fields.name'))
                ->required()
                ->maxLength(255),
            Toggle::make('is_default')
                ->label(__('capell-email-studio::templates.fields.default_theme'))
                ->disabled()
                ->dehydrated(false),
            TextInput::make('logo_url')
                ->label(__('capell-email-studio::templates.fields.logo_url'))
                ->url()
                ->maxLength(2048)
                ->columnSpanFull(),
            TextInput::make('logo_path')
                ->label(__('capell-email-studio::templates.fields.logo_path'))
                ->maxLength(2048)
                ->columnSpanFull(),
            TextInput::make('screenshot_path')
                ->label(__('capell-email-studio::templates.fields.screenshot_path'))
                ->disabled()
                ->dehydrated(false)
                ->maxLength(2048)
                ->columnSpanFull(),
            ColorPicker::make('colors.body_bg_color')
                ->label(__('capell-email-studio::templates.fields.body_bg_color')),
            ColorPicker::make('colors.content_bg_color')
                ->label(__('capell-email-studio::templates.fields.content_bg_color')),
            ColorPicker::make('colors.header_bg_color')
                ->label(__('capell-email-studio::templates.fields.header_bg_color')),
            ColorPicker::make('colors.body_color')
                ->label(__('capell-email-studio::templates.fields.body_color')),
            ColorPicker::make('colors.muted_color')
                ->label(__('capell-email-studio::templates.fields.muted_color')),
            ColorPicker::make('colors.border_color')
                ->label(__('capell-email-studio::templates.fields.border_color')),
            ColorPicker::make('colors.button_bg_color')
                ->label(__('capell-email-studio::templates.fields.button_bg_color')),
            ColorPicker::make('colors.button_color')
                ->label(__('capell-email-studio::templates.fields.button_color')),
        ]);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->label(__('capell-email-studio::templates.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('key')
                    ->label(__('capell-email-studio::templates.fields.key'))
                    ->searchable()
                    ->copyable(),
                TextColumn::make('site_scope_key')
                    ->label(__('capell-email-studio::templates.fields.site_scope'))
                    ->sortable(),
                IconColumn::make('is_default')
                    ->label(__('capell-email-studio::templates.fields.default_theme'))
                    ->boolean(),
                TextColumn::make('screenshot_path')
                    ->label(__('capell-email-studio::templates.fields.screenshot_path'))
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('capell-email-studio::templates.fields.updated_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('make_default')
                    ->label(__('capell-email-studio::templates.actions.make_default'))
                    ->icon('heroicon-o-star')
                    ->visible(fn (EmailTemplateTheme $record): bool => ! $record->is_default)
                    ->action(function (EmailTemplateTheme $record): void {
                        CreateDefaultEmailTemplateThemeAction::run(
                            siteId: $record->site_id,
                            siteScopeKey: $record->site_scope_key,
                            key: $record->key,
                            name: $record->name,
                            colors: is_array($record->colors) ? $record->colors : [],
                        );

                        Notification::make('email-template-theme-defaulted')
                            ->title(__('capell-email-studio::templates.messages.theme_defaulted'))
                            ->icon('heroicon-o-check-circle')
                            ->iconColor('success')
                            ->send();
                    }),
                Action::make('capture_screenshot')
                    ->label(__('capell-email-studio::templates.actions.capture_screenshot'))
                    ->icon('heroicon-o-camera')
                    ->visible(fn (): bool => self::hasScreenshotAdapter())
                    ->action(function (EmailTemplateTheme $record): void {
                        CaptureEmailTemplateThemeScreenshotAction::run($record);

                        Notification::make('email-template-theme-screenshot-captured')
                            ->title(__('capell-email-studio::templates.messages.theme_screenshot_captured'))
                            ->icon('heroicon-o-check-circle')
                            ->iconColor('success')
                            ->send();
                    }),
            ]);
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return (string) __('capell-email-studio::templates.navigation.themes');
    }

    #[Override]
    public static function getNavigationGroup(): string
    {
        return (string) __('capell-email-studio::generic.email_studio');
    }

    #[Override]
    public static function getPluralModelLabel(): string
    {
        return (string) __('capell-email-studio::templates.resources.themes');
    }

    #[Override]
    public static function getModelLabel(): string
    {
        return (string) __('capell-email-studio::templates.resources.theme');
    }

    #[Override]
    public static function shouldRegisterNavigation(): bool
    {
        return CapellCore::isPackageInstalled(EmailStudioServiceProvider::$packageName);
    }

    /**
     * @return array<string, mixed>
     */
    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListEmailTemplateThemes::route('/'),
            'create' => CreateEmailTemplateTheme::route('/create'),
            'edit' => EditEmailTemplateTheme::route('/{record}/edit'),
        ];
    }

    private static function hasScreenshotAdapter(): bool
    {
        $configuredAdapter = config('capell-email-studio.screenshot_adapter');

        if (is_string($configuredAdapter) && $configuredAdapter !== '') {
            return true;
        }

        return app()->bound(CapturesEmailTemplateThemeScreenshots::class);
    }
}
