<?php

declare(strict_types=1);

namespace Capell\CustomerPortal\Filament\Resources\PortalSupportRequests;

use BackedEnum;
use Capell\Core\Facades\CapellCore;
use Capell\CustomerPortal\Actions\UpdateSupportRequestStatusAction;
use Capell\CustomerPortal\Enums\SupportRequestPriority;
use Capell\CustomerPortal\Enums\SupportRequestStatus;
use Capell\CustomerPortal\Filament\Resources\PortalSupportRequests\Pages\EditPortalSupportRequest;
use Capell\CustomerPortal\Filament\Resources\PortalSupportRequests\Pages\ListPortalSupportRequests;
use Capell\CustomerPortal\Models\PortalSupportRequest;
use Capell\CustomerPortal\Providers\CustomerPortalServiceProvider;
use Filament\Actions\Action;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Override;

final class PortalSupportRequestResource extends Resource
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLifebuoy;

    protected static ?string $recordTitleAttribute = 'subject';

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('status')
                ->label(__('capell-customer-portal::generic.fields.status'))
                ->options(self::statusOptions())
                ->required(),
            Select::make('priority')
                ->label(__('capell-customer-portal::generic.fields.priority'))
                ->options(self::priorityOptions())
                ->required(),
            TextInput::make('requester_email')
                ->label(__('capell-customer-portal::generic.fields.requester_email'))
                ->disabled(),
            TextInput::make('subject')
                ->label(__('capell-customer-portal::generic.fields.subject'))
                ->disabled(),
            Textarea::make('message')
                ->label(__('capell-customer-portal::generic.fields.message'))
                ->rows(6)
                ->disabled()
                ->columnSpanFull(),
            TextInput::make('source')
                ->label(__('capell-customer-portal::generic.fields.source'))
                ->disabled(),
            TextInput::make('external_reference')
                ->label(__('capell-customer-portal::generic.fields.external_reference'))
                ->disabled(),
            KeyValue::make('context')
                ->label(__('capell-customer-portal::generic.fields.context'))
                ->disabled()
                ->columnSpanFull(),
        ]);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('subject')->label(__('capell-customer-portal::generic.fields.subject'))->limit(48),
                TextColumn::make('requester_email')->label(__('capell-customer-portal::generic.fields.requester_email')),
                TextColumn::make('status')->label(__('capell-customer-portal::generic.fields.status'))->badge()->sortable(),
                TextColumn::make('priority')->label(__('capell-customer-portal::generic.fields.priority'))->badge()->sortable(),
                TextColumn::make('source')->label(__('capell-customer-portal::generic.fields.source'))->toggleable(),
                TextColumn::make('submitted_at')->label(__('capell-customer-portal::generic.fields.submitted_at'))->dateTime()->sortable(),
                TextColumn::make('resolved_at')->label(__('capell-customer-portal::generic.fields.resolved_at'))->dateTime()->sortable()->toggleable(),
            ])
            ->recordActions([
                Action::make('mark_waiting_on_customer')
                    ->label(__('capell-customer-portal::generic.actions.mark_waiting_on_customer'))
                    ->action(fn (PortalSupportRequest $record): PortalSupportRequest => UpdateSupportRequestStatusAction::run($record, SupportRequestStatus::WaitingOnCustomer)),
                Action::make('mark_resolved')
                    ->label(__('capell-customer-portal::generic.actions.mark_resolved'))
                    ->action(fn (PortalSupportRequest $record): PortalSupportRequest => UpdateSupportRequestStatusAction::run($record, SupportRequestStatus::Resolved)),
            ]);
    }

    #[Override]
    public static function getModel(): string
    {
        return PortalSupportRequest::class;
    }

    #[Override]
    public static function getNavigationGroup(): ?string
    {
        return __('capell-customer-portal::generic.navigation.group');
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('capell-customer-portal::generic.resources.support_requests');
    }

    #[Override]
    public static function shouldRegisterNavigation(): bool
    {
        return CapellCore::isPackageInstalled(CustomerPortalServiceProvider::$packageName);
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListPortalSupportRequests::route('/'),
            'edit' => EditPortalSupportRequest::route('/{record}/edit'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function statusOptions(): array
    {
        return collect(SupportRequestStatus::cases())
            ->mapWithKeys(fn (SupportRequestStatus $status): array => [$status->value => $status->getLabel()])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private static function priorityOptions(): array
    {
        return collect(SupportRequestPriority::cases())
            ->mapWithKeys(fn (SupportRequestPriority $priority): array => [$priority->value => $priority->getLabel()])
            ->all();
    }
}
