<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Filament\Resources\EmailTemplates;

use BackedEnum;
use Capell\Core\Facades\CapellCore;
use Capell\EmailStudio\Actions\CreateEmailTemplateOverrideAction;
use Capell\EmailStudio\Actions\SendEmailTemplateTestAction;
use Capell\EmailStudio\Enums\EmailVariantStatus;
use Capell\EmailStudio\Filament\Resources\EmailTemplates\Pages\ListEmailTemplates;
use Capell\EmailStudio\Models\EmailTemplate;
use Capell\EmailStudio\Models\EmailTemplateRegistration;
use Capell\EmailStudio\Providers\EmailStudioServiceProvider;
use Capell\EmailStudio\Support\EmailTemplateRegistry;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Override;

final class EmailTemplateResource extends Resource
{
    protected static ?string $slug = 'email-studio/templates';

    protected static ?string $model = EmailTemplateRegistration::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::RectangleStack;

    protected static ?int $navigationSort = 42;

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema;
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
                TextColumn::make('template_key')
                    ->label(__('capell-email-studio::templates.fields.key'))
                    ->searchable()
                    ->copyable(),
                TextColumn::make('package_name')
                    ->label(__('capell-email-studio::templates.fields.package'))
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('default_locale')
                    ->label(__('capell-email-studio::templates.fields.default_locale'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('coverage')
                    ->label(__('capell-email-studio::templates.fields.coverage'))
                    ->state(fn (EmailTemplateRegistration $record): string => self::coverage($record))
                    ->toggleable(),
                TextColumn::make('variables')
                    ->label(__('capell-email-studio::templates.fields.variables'))
                    ->state(fn (EmailTemplateRegistration $record): string => implode(', ', $record->variables ?? []))
                    ->limit(60)
                    ->toggleable(),
                TextColumn::make('override_status')
                    ->label(__('capell-email-studio::templates.fields.status'))
                    ->state(fn (EmailTemplateRegistration $record): string => self::overrideStatus($record))
                    ->badge(),
                TextColumn::make('updated_at')
                    ->label(__('capell-email-studio::templates.fields.updated_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('preview')
                    ->label(__('capell-email-studio::templates.actions.preview'))
                    ->icon('heroicon-o-eye')
                    ->modalSubmitAction(false)
                    ->modalWidth('7xl')
                    ->modalContent(fn (EmailTemplateRegistration $record): View => view('capell-email-studio::filament.email-templates.preview', [
                        'record' => $record,
                    ])),
                Action::make('customize')
                    ->label(__('capell-email-studio::templates.actions.customize'))
                    ->icon('heroicon-o-pencil-square')
                    ->visible(fn (EmailTemplateRegistration $record): bool => resolve(EmailTemplateRegistry::class)->findDefinition($record->template_key) !== null)
                    ->action(function (EmailTemplateRegistration $record): void {
                        CreateEmailTemplateOverrideAction::run(
                            templateKey: $record->template_key,
                            siteId: $record->site_id,
                            siteScopeKey: $record->site_scope_key,
                        );

                        Notification::make('email-template-override-created')
                            ->title(__('capell-email-studio::templates.messages.override_created'))
                            ->icon('heroicon-o-check-circle')
                            ->iconColor('success')
                            ->send();
                    }),
                Action::make('send_test')
                    ->label(__('capell-email-studio::templates.actions.send_test'))
                    ->icon('heroicon-o-paper-airplane')
                    ->schema([
                        TextInput::make('email')
                            ->label(__('capell-email-studio::templates.fields.test_email'))
                            ->email()
                            ->required()
                            ->maxLength(255),
                    ])
                    ->action(function (EmailTemplateRegistration $record, array $data): void {
                        SendEmailTemplateTestAction::run(
                            templateKey: $record->template_key,
                            recipientEmail: (string) $data['email'],
                            siteId: $record->site_id,
                            siteScopeKey: $record->site_scope_key,
                        );

                        Notification::make('email-template-test-sent')
                            ->title(__('capell-email-studio::templates.messages.test_sent'))
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
    public static function canEdit(Model $record): bool
    {
        return false;
    }

    #[Override]
    public static function canDelete(Model $record): bool
    {
        return false;
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return (string) __('capell-email-studio::templates.navigation.templates');
    }

    #[Override]
    public static function getNavigationGroup(): string
    {
        return (string) __('capell-email-studio::generic.email_studio');
    }

    #[Override]
    public static function getPluralModelLabel(): string
    {
        return (string) __('capell-email-studio::templates.resources.templates');
    }

    #[Override]
    public static function getModelLabel(): string
    {
        return (string) __('capell-email-studio::templates.resources.template');
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
            'index' => ListEmailTemplates::route('/'),
        ];
    }

    private static function overrideStatus(EmailTemplateRegistration $registration): string
    {
        $template = EmailTemplate::query()
            ->where('key', $registration->template_key)
            ->where('site_scope_key', $registration->site_scope_key)
            ->with('variants')
            ->first();

        if (! $template instanceof EmailTemplate) {
            return (string) __('capell-email-studio::templates.status.static');
        }

        if ($template->variants->contains('status', EmailVariantStatus::Active)) {
            return (string) __('capell-email-studio::templates.status.active_override');
        }

        return (string) __('capell-email-studio::templates.status.draft_override');
    }

    private static function coverage(EmailTemplateRegistration $registration): string
    {
        $template = EmailTemplate::query()
            ->where('key', $registration->template_key)
            ->where('site_scope_key', $registration->site_scope_key)
            ->with('variants')
            ->first();

        $locales = $template instanceof EmailTemplate
            ? $template->variants
                ->pluck('locale')
                ->map(static fn (mixed $locale): string => is_string($locale) && $locale !== '' ? $locale : 'default')
                ->unique()
                ->values()
                ->all()
            : [];

        if ($locales === []) {
            $locales = [$registration->default_locale ?? 'en'];
        }

        $source = $registration->is_static_renderable
            ? (string) __('capell-email-studio::templates.status.static')
            : (string) __('capell-email-studio::templates.status.registration_only');

        return sprintf('%s: %s', $source, implode(', ', $locales));
    }
}
