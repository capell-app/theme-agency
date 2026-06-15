<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Filament\Resources\EmailTemplateVariants;

use BackedEnum;
use Capell\Admin\Enums\TinyEditorProfile;
use Capell\Admin\Filament\Components\Forms\Editor\TinyEditor;
use Capell\Core\Facades\CapellCore;
use Capell\EmailStudio\Actions\ActivateEmailTemplateVariantAction;
use Capell\EmailStudio\Enums\EmailVariantStatus;
use Capell\EmailStudio\Filament\Resources\EmailTemplateVariants\Pages\EditEmailTemplateVariant;
use Capell\EmailStudio\Filament\Resources\EmailTemplateVariants\Pages\ListEmailTemplateVariants;
use Capell\EmailStudio\Models\EmailTemplateVariant;
use Capell\EmailStudio\Providers\EmailStudioServiceProvider;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Override;

final class EmailTemplateVariantResource extends Resource
{
    protected static ?string $slug = 'email-studio/template-variants';

    protected static ?string $model = EmailTemplateVariant::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentDuplicate;

    protected static ?int $navigationSort = 43;

    protected static ?string $recordTitleAttribute = 'subject';

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('email_template_id')
                ->label(__('capell-email-studio::templates.fields.template'))
                ->relationship('template', 'name')
                ->disabled()
                ->required(),
            Select::make('status')
                ->label(__('capell-email-studio::templates.fields.status'))
                ->options(EmailVariantStatus::class)
                ->disabled()
                ->dehydrated(false)
                ->required(),
            TextInput::make('locale')
                ->label(__('capell-email-studio::templates.fields.locale'))
                ->maxLength(12),
            TextInput::make('version')
                ->label(__('capell-email-studio::templates.fields.version'))
                ->numeric()
                ->disabled(),
            Select::make('email_profile_id')
                ->label(__('capell-email-studio::templates.fields.profile'))
                ->relationship('profile', 'name')
                ->searchable()
                ->preload(),
            Select::make('email_template_theme_id')
                ->label(__('capell-email-studio::templates.fields.theme'))
                ->relationship('theme', 'name')
                ->searchable()
                ->preload(),
            TextInput::make('subject')
                ->label(__('capell-email-studio::templates.fields.subject'))
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),
            TextInput::make('preview_text')
                ->label(__('capell-email-studio::templates.fields.preview_text'))
                ->maxLength(255)
                ->columnSpanFull(),
            Repeater::make('cc')
                ->label(__('capell-email-studio::templates.fields.cc'))
                ->schema(self::addressSchema())
                ->columnSpanFull(),
            Repeater::make('bcc')
                ->label(__('capell-email-studio::templates.fields.bcc'))
                ->schema(self::addressSchema())
                ->columnSpanFull(),
            TinyEditor::make('html_body')
                ->label(__('capell-email-studio::templates.fields.html_body'))
                ->profile(TinyEditorProfile::Full->value)
                ->required()
                ->columnSpanFull(),
            Textarea::make('text_body')
                ->label(__('capell-email-studio::templates.fields.text_body'))
                ->rows(10)
                ->columnSpanFull(),
        ]);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['template', 'profile', 'theme'])->latest('updated_at'))
            ->columns([
                TextColumn::make('template.name')
                    ->label(__('capell-email-studio::templates.fields.template'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('subject')
                    ->label(__('capell-email-studio::templates.fields.subject'))
                    ->searchable()
                    ->limit(60),
                TextColumn::make('locale')
                    ->label(__('capell-email-studio::templates.fields.locale'))
                    ->badge(),
                TextColumn::make('site_scope_key')
                    ->label(__('capell-email-studio::templates.fields.site_scope'))
                    ->toggleable(),
                TextColumn::make('status')
                    ->label(__('capell-email-studio::templates.fields.status'))
                    ->badge(),
                TextColumn::make('version')
                    ->label(__('capell-email-studio::templates.fields.version'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('theme.name')
                    ->label(__('capell-email-studio::templates.fields.theme'))
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->label(__('capell-email-studio::templates.fields.updated_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('capell-email-studio::templates.fields.status'))
                    ->options(EmailVariantStatus::class),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('activate')
                    ->label(__('capell-email-studio::templates.actions.activate'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (EmailTemplateVariant $record): bool => $record->status !== EmailVariantStatus::Active)
                    ->action(function (EmailTemplateVariant $record): void {
                        $actorId = auth()->id();

                        ActivateEmailTemplateVariantAction::run(
                            $record,
                            is_numeric($actorId) ? (int) $actorId : null,
                        );

                        Notification::make('email-template-variant-activated')
                            ->title(__('capell-email-studio::templates.messages.variant_activated'))
                            ->icon('heroicon-o-check-circle')
                            ->iconColor('success')
                            ->send();
                    }),
            ]);
    }

    #[Override]
    public static function canCreate(): bool
    {
        return false;
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return (string) __('capell-email-studio::templates.navigation.variants');
    }

    #[Override]
    public static function getNavigationGroup(): string
    {
        return (string) __('capell-email-studio::generic.email_studio');
    }

    #[Override]
    public static function getPluralModelLabel(): string
    {
        return (string) __('capell-email-studio::templates.resources.variants');
    }

    #[Override]
    public static function getModelLabel(): string
    {
        return (string) __('capell-email-studio::templates.resources.variant');
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
            'index' => ListEmailTemplateVariants::route('/'),
            'edit' => EditEmailTemplateVariant::route('/{record}/edit'),
        ];
    }

    /**
     * @return array<int, TextInput>
     */
    private static function addressSchema(): array
    {
        return [
            TextInput::make('email')
                ->label(__('capell-email-studio::templates.fields.email'))
                ->email()
                ->required()
                ->maxLength(255),
            TextInput::make('name')
                ->label(__('capell-email-studio::templates.fields.name'))
                ->maxLength(255),
        ];
    }
}
