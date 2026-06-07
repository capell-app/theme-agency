<?php

declare(strict_types=1);

namespace Capell\Contacts\Filament\Resources\Contacts\Pages;

use Capell\Contacts\Actions\RecordContactActivityAction;
use Capell\Contacts\Data\ContactActivityData;
use Capell\Contacts\Enums\ContactActivityType;
use Capell\Contacts\Filament\Resources\Contacts\ContactResource;
use Capell\Contacts\Models\Contact;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Override;

/**
 * @property-read Contact $record
 */
final class ViewContact extends ViewRecord
{
    protected static string $resource = ContactResource::class;

    #[Override]
    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('capell-contacts::generic.sections.contact_overview'))
                ->columns(2)
                ->schema([
                    TextEntry::make('display_name')
                        ->label(__('capell-contacts::generic.fields.display_name'))
                        ->placeholder(__('capell-contacts::generic.placeholders.empty')),
                    TextEntry::make('email')
                        ->label(__('capell-contacts::generic.fields.email'))
                        ->placeholder(__('capell-contacts::generic.placeholders.empty')),
                    TextEntry::make('phone')
                        ->label(__('capell-contacts::generic.fields.phone'))
                        ->placeholder(__('capell-contacts::generic.placeholders.empty')),
                    TextEntry::make('status')
                        ->label(__('capell-contacts::generic.fields.status'))
                        ->badge(),
                    TextEntry::make('first_seen_at')
                        ->label(__('capell-contacts::generic.fields.first_seen_at'))
                        ->dateTime()
                        ->placeholder(__('capell-contacts::generic.placeholders.empty')),
                    TextEntry::make('last_seen_at')
                        ->label(__('capell-contacts::generic.fields.last_seen_at'))
                        ->dateTime()
                        ->placeholder(__('capell-contacts::generic.placeholders.empty')),
                ]),
            Section::make(__('capell-contacts::generic.sections.activity_timeline'))
                ->schema([
                    ViewEntry::make('activities')
                        ->view('capell-contacts::filament.contacts.activity-timeline')
                        ->state(fn (Contact $record): array => [
                            'activities' => $record
                                ->activities()
                                ->latest('occurred_at')
                                ->limit(25)
                                ->get(),
                        ]),
                ]),
        ]);
    }

    /**
     * @return array<int, Action>
     */
    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            Action::make('add_note')
                ->label(__('capell-contacts::generic.actions.add_note'))
                ->icon('heroicon-o-pencil-square')
                ->form([
                    Textarea::make('summary')
                        ->label(__('capell-contacts::generic.fields.note'))
                        ->required()
                        ->maxLength(2000)
                        ->rows(4),
                ])
                ->action(function (array $data): void {
                    $summary = is_string($data['summary'] ?? null) ? trim($data['summary']) : '';

                    if ($summary === '') {
                        return;
                    }

                    RecordContactActivityAction::run($this->record, new ContactActivityData(
                        type: ContactActivityType::Note,
                        summary: $summary,
                        payload: ['source' => 'admin'],
                    ));

                    $this->record->refresh();

                    Notification::make('contact-note-added')
                        ->title(__('capell-contacts::generic.notifications.note_added'))
                        ->success()
                        ->send();
                }),
        ];
    }
}
