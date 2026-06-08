<?php

declare(strict_types=1);

namespace Capell\Contacts\Filament\Resources\Contacts;

use BackedEnum;
use Capell\Contacts\Actions\AnonymizeContactWithAuditAction;
use Capell\Contacts\Actions\AuditContactPrivacyExportAction;
use Capell\Contacts\Actions\MergeContactsAction;
use Capell\Contacts\Filament\Resources\Contacts\Pages\ListContacts;
use Capell\Contacts\Filament\Resources\Contacts\Pages\ViewContact;
use Capell\Contacts\Models\Contact;
use Capell\Contacts\Models\ContactTag;
use Capell\Contacts\Providers\ContactsServiceProvider;
use Capell\Core\Facades\CapellCore;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
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
        ])->filters([
            SelectFilter::make('tag')
                ->label(__('capell-contacts::generic.filters.tag'))
                ->options(fn (): array => ContactTag::query()
                    ->orderBy('name')
                    ->pluck('name', 'slug')
                    ->all())
                ->query(function (Builder $query, array $data): Builder {
                    $tag = is_string($data['value'] ?? null) ? ContactTag::slugFor($data['value']) : '';

                    return $tag !== ''
                        ? $query->whereHas('tags', static fn (Builder $tagQuery): Builder => $tagQuery->where('slug', $tag))
                        : $query;
                }),
        ])->recordActions([
            Action::make('view')
                ->label(__('capell-contacts::generic.actions.view'))
                ->icon('heroicon-o-eye')
                ->url(fn (Contact $record): string => self::getUrl('view', ['record' => $record])),
            Action::make('merge')
                ->label(__('capell-contacts::generic.actions.merge'))
                ->icon('heroicon-o-arrows-right-left')
                ->color('warning')
                ->requiresConfirmation()
                ->form([
                    Select::make('target_contact_id')
                        ->label(__('capell-contacts::generic.fields.target_contact'))
                        ->options(fn (Contact $record): array => Contact::query()
                            ->where('site_id', $record->site_id)
                            ->whereKeyNot($record->getKey())
                            ->orderBy('id')
                            ->limit(50)
                            ->get()
                            ->mapWithKeys(fn (Contact $contact): array => [
                                self::modelKey($contact) => self::contactOptionLabel($contact),
                            ])
                            ->all())
                        ->searchable()
                        ->required(),
                    Textarea::make('reason')
                        ->label(__('capell-contacts::generic.fields.reason'))
                        ->maxLength(500)
                        ->rows(2),
                ])
                ->action(function (Contact $record, array $data): void {
                    $targetContactId = $data['target_contact_id'] ?? null;
                    $target = is_numeric($targetContactId)
                        ? Contact::query()->where('site_id', $record->site_id)->find((int) $targetContactId)
                        : null;

                    if (! $target instanceof Contact) {
                        Notification::make('contacts-merge-failed')
                            ->title(__('capell-contacts::generic.merge.target_not_found'))
                            ->danger()
                            ->send();

                        return;
                    }

                    $reason = is_string($data['reason'] ?? null) && trim($data['reason']) !== ''
                        ? trim($data['reason'])
                        : 'manual';

                    MergeContactsAction::run($record, $target, $reason);

                    Notification::make('contacts-merge-completed')
                        ->title(__('capell-contacts::generic.merge.completed_notification'))
                        ->success()
                        ->send();
                }),
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
        return parent::getEloquentQuery()->with(['site', 'tags']);
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
            'view' => ViewContact::route('/{record}'),
        ];
    }

    private static function downloadPrivacyExport(Contact $contact): StreamedResponse
    {
        return response()->streamDownload(function () use ($contact): void {
            echo json_encode(AuditContactPrivacyExportAction::run($contact, 'admin'), JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
        }, 'contact-' . self::modelKey($contact) . '-privacy-export.json', [
            'Content-Type' => 'application/json',
        ]);
    }

    private static function contactOptionLabel(Contact $contact): string
    {
        return collect([$contact->display_name, $contact->email, '#' . self::modelKey($contact)])
            ->filter(fn (mixed $value): bool => is_string($value) && $value !== '')
            ->join(' - ');
    }

    private static function modelKey(Contact $contact): string
    {
        $key = $contact->getKey();

        return is_int($key) || is_string($key) ? (string) $key : '';
    }
}
