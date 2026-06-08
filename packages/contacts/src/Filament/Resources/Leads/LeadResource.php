<?php

declare(strict_types=1);

namespace Capell\Contacts\Filament\Resources\Leads;

use BackedEnum;
use Capell\Contacts\Actions\UpdateLeadStatusAction;
use Capell\Contacts\Enums\LeadStatus;
use Capell\Contacts\Filament\Resources\Leads\Pages\ListLeads;
use Capell\Contacts\Models\Lead;
use Capell\Contacts\Providers\ContactsServiceProvider;
use Capell\Core\Facades\CapellCore;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Override;

final class LeadResource extends Resource
{
    protected static ?string $slug = 'contacts/leads';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static ?string $recordTitleAttribute = 'title';

    #[Override]
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('title')->label(__('capell-contacts::generic.fields.title'))->searchable(),
            TextColumn::make('contact.display_name')->label(__('capell-contacts::generic.resources.contact'))->searchable(),
            TextColumn::make('organisation.name')->label(__('capell-contacts::generic.resources.organisation'))->searchable(),
            TextColumn::make('status')->label(__('capell-contacts::generic.fields.status'))->badge()->sortable(),
            TextColumn::make('value_amount')->label(__('capell-contacts::generic.fields.value_amount'))->money(fn (Lead $record): string => $record->currency ?? 'GBP'),
            TextColumn::make('captured_at')->label(__('capell-contacts::generic.fields.captured_at'))->dateTime()->sortable(),
        ])->recordActions([
            Action::make('change_status')
                ->label(__('capell-contacts::generic.actions.change_status'))
                ->icon('heroicon-o-arrow-path')
                ->form([
                    Select::make('status')
                        ->label(__('capell-contacts::generic.fields.status'))
                        ->options(collect(LeadStatus::cases())
                            ->mapWithKeys(fn (LeadStatus $status): array => [$status->value => $status->getLabel()])
                            ->all())
                        ->required(),
                ])
                ->fillForm(fn (Lead $record): array => [
                    'status' => $record->status->value ?? LeadStatus::New->value,
                ])
                ->action(function (Lead $record, array $data): void {
                    $status = is_string($data['status'] ?? null)
                        ? LeadStatus::tryFrom($data['status'])
                        : null;

                    if (! $status instanceof LeadStatus) {
                        return;
                    }

                    UpdateLeadStatusAction::run($record, $status);

                    Notification::make('lead-status-updated')
                        ->title(__('capell-contacts::generic.notifications.lead_status_updated'))
                        ->success()
                        ->send();
                }),
        ]);
    }

    #[Override]
    public static function getModel(): string
    {
        return Lead::class;
    }

    #[Override]
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['contact', 'organisation', 'site']);
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
        return __('capell-contacts::generic.resources.leads');
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
            'index' => ListLeads::route('/'),
        ];
    }
}
