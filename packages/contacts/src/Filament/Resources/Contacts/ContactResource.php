<?php

declare(strict_types=1);

namespace Capell\Contacts\Filament\Resources\Contacts;

use BackedEnum;
use Capell\Contacts\Actions\AnonymizeContactWithAuditAction;
use Capell\Contacts\Actions\AuditContactPrivacyExportAction;
use Capell\Contacts\Filament\Resources\Contacts\Pages\ListContacts;
use Capell\Contacts\Models\Contact;
use Capell\Contacts\Providers\ContactsServiceProvider;
use Capell\Core\Facades\CapellCore;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Override;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ContactResource extends Resource
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $recordTitleAttribute = 'display_name';

    #[Override]
    public static function table(Table $table): Table
    {
        return $table->columns([
            // display_name and email are encrypted at rest, so SQL LIKE search can never match the ciphertext.
            // They are intentionally not searchable until a hash-based or plaintext search column exists.
            TextColumn::make('display_name')->label(__('capell-contacts::generic.fields.display_name')),
            TextColumn::make('email')->label(__('capell-contacts::generic.fields.email')),
            TextColumn::make('phone')->label(__('capell-contacts::generic.fields.phone')),
            TextColumn::make('status')->label(__('capell-contacts::generic.fields.status'))->badge()->sortable(),
            TextColumn::make('last_seen_at')->label(__('capell-contacts::generic.fields.last_seen_at'))->dateTime()->sortable(),
        ])->recordActions([
            Action::make('privacy_export')
                ->label(__('capell-contacts::generic.actions.privacy_export'))
                ->icon('heroicon-o-arrow-down-tray')
                ->visible(fn (Contact $record): bool => Gate::allows('exportPrivacy', $record))
                ->action(function (Contact $record): StreamedResponse {
                    Gate::authorize('exportPrivacy', $record);

                    return self::downloadPrivacyExport($record);
                }),
            Action::make('privacy_anonymize')
                ->label(__('capell-contacts::generic.actions.privacy_anonymize'))
                ->icon('heroicon-o-user-minus')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn (Contact $record): bool => Gate::allows('anonymizePrivacy', $record))
                ->action(function (Contact $record): void {
                    Gate::authorize('anonymizePrivacy', $record);

                    AnonymizeContactWithAuditAction::run($record, 'admin');

                    Notification::make('contacts-privacy-anonymized')
                        ->title(__('capell-contacts::generic.privacy.anonymized_notification'))
                        ->success()
                        ->send();
                }),
        ]);
    }

    #[Override]
    public static function getModel(): string
    {
        return Contact::class;
    }

    #[Override]
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('site');
    }

    #[Override]
    public static function getNavigationGroup(): ?string
    {
        return null;
    }

    #[Override]
    public static function getNavigationParentItem(): string
    {
        return __('capell-admin::navigation.marketing_studio');
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('capell-contacts::generic.resources.contacts');
    }

    #[Override]
    public static function shouldRegisterNavigation(): bool
    {
        return CapellCore::isPackageInstalled(ContactsServiceProvider::$packageName);
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListContacts::route('/'),
        ];
    }

    private static function downloadPrivacyExport(Contact $contact): StreamedResponse
    {
        return response()->streamDownload(function () use ($contact): void {
            echo json_encode(AuditContactPrivacyExportAction::run($contact, 'admin'), JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
        }, 'contact-' . $contact->getKey() . '-privacy-export.json', [
            'Content-Type' => 'application/json',
        ]);
    }
}
