<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Filament\Resources\PrivacyRequests;

use BackedEnum;
use Capell\Core\Facades\CapellCore;
use Capell\PrivacyCenter\Actions\MarkPrivacyRequestFulfilledAction;
use Capell\PrivacyCenter\Actions\MarkPrivacyRequestVerifiedAction;
use Capell\PrivacyCenter\Actions\RejectPrivacyRequestAction;
use Capell\PrivacyCenter\Enums\PrivacyRequestStatus;
use Capell\PrivacyCenter\Enums\PrivacyRequestType;
use Capell\PrivacyCenter\Filament\Resources\PrivacyRequests\Pages\EditPrivacyRequest;
use Capell\PrivacyCenter\Filament\Resources\PrivacyRequests\Pages\ListPrivacyRequests;
use Capell\PrivacyCenter\Models\PrivacyRequest;
use Capell\PrivacyCenter\Providers\PrivacyCenterServiceProvider;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Override;

final class PrivacyRequestResource extends Resource
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxStack;

    protected static ?string $recordTitleAttribute = 'reference';

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('reference')->label(__('capell-privacy-center::privacy.admin.fields.reference'))->disabled(),
            Select::make('type')->label(__('capell-privacy-center::privacy.admin.fields.request_type'))->options(self::typeOptions())->disabled(),
            Select::make('status')->label(__('capell-privacy-center::privacy.admin.fields.status'))->options(self::statusOptions())->disabled(),
            TextInput::make('email_hash')->label(__('capell-privacy-center::privacy.admin.fields.email_hash'))->disabled(),
            DateTimePicker::make('submitted_at')->label(__('capell-privacy-center::privacy.admin.fields.submitted_at'))->disabled(),
            DateTimePicker::make('due_at')->label(__('capell-privacy-center::privacy.admin.fields.due_at')),
            DateTimePicker::make('verified_at')->label(__('capell-privacy-center::privacy.admin.fields.verified_at'))->disabled(),
            DateTimePicker::make('fulfilled_at')->label(__('capell-privacy-center::privacy.admin.fields.fulfilled_at'))->disabled(),
            DateTimePicker::make('rejected_at')->label(__('capell-privacy-center::privacy.admin.fields.rejected_at'))->disabled(),
            TextInput::make('rejection_reason')->label(__('capell-privacy-center::privacy.admin.fields.rejection_reason'))->disabled(),
            KeyValue::make('workflow_payload')->label(__('capell-privacy-center::privacy.admin.fields.workflow_payload'))->columnSpanFull(),
            KeyValue::make('metadata')->label(__('capell-privacy-center::privacy.admin.fields.metadata'))->columnSpanFull(),
        ]);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('reference')->label(__('capell-privacy-center::privacy.admin.fields.reference'))->searchable()->sortable(),
            TextColumn::make('type')->label(__('capell-privacy-center::privacy.admin.fields.request_type'))->badge()->sortable(),
            TextColumn::make('status')->label(__('capell-privacy-center::privacy.admin.fields.status'))->badge()->sortable(),
            TextColumn::make('submitted_at')->label(__('capell-privacy-center::privacy.admin.fields.submitted_at'))->dateTime()->sortable(),
            TextColumn::make('due_at')->label(__('capell-privacy-center::privacy.admin.fields.due_at'))->dateTime()->sortable(),
            TextColumn::make('fulfilled_at')->label(__('capell-privacy-center::privacy.admin.fields.fulfilled_at'))->dateTime()->toggleable(),
        ]);
    }

    #[Override]
    public static function getModel(): string
    {
        return PrivacyRequest::class;
    }

    #[Override]
    public static function getNavigationGroup(): string
    {
        return __('capell-privacy-center::privacy.admin.navigation_group');
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('capell-privacy-center::privacy.admin.resources.privacy_requests');
    }

    #[Override]
    public static function shouldRegisterNavigation(): bool
    {
        return CapellCore::isPackageInstalled(PrivacyCenterServiceProvider::$packageName);
    }

    public static function markVerifiedAction(): Action
    {
        return Action::make('mark_verified')
            ->label(__('capell-privacy-center::privacy.admin.actions.mark_verified'))
            ->icon('heroicon-o-shield-check')
            ->color('info')
            ->visible(fn (PrivacyRequest $record): bool => in_array($record->status, [
                PrivacyRequestStatus::Submitted,
                PrivacyRequestStatus::Verifying,
            ], true))
            ->requiresConfirmation()
            ->action(function (PrivacyRequest $record): void {
                MarkPrivacyRequestVerifiedAction::run($record);

                Notification::make('privacy-request-verified')
                    ->title(__('capell-privacy-center::privacy.admin.messages.privacy_request_verified'))
                    ->success()
                    ->send();
            });
    }

    public static function markFulfilledAction(): Action
    {
        return Action::make('mark_fulfilled')
            ->label(__('capell-privacy-center::privacy.admin.actions.mark_fulfilled'))
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->visible(fn (PrivacyRequest $record): bool => $record->status === PrivacyRequestStatus::Processing)
            ->requiresConfirmation()
            ->action(function (PrivacyRequest $record): void {
                MarkPrivacyRequestFulfilledAction::run($record);

                Notification::make('privacy-request-fulfilled')
                    ->title(__('capell-privacy-center::privacy.admin.messages.privacy_request_fulfilled'))
                    ->success()
                    ->send();
            });
    }

    public static function rejectAction(): Action
    {
        return Action::make('reject')
            ->label(__('capell-privacy-center::privacy.admin.actions.reject'))
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->visible(fn (PrivacyRequest $record): bool => ! in_array($record->status, [
                PrivacyRequestStatus::Fulfilled,
                PrivacyRequestStatus::Rejected,
                PrivacyRequestStatus::Cancelled,
            ], true))
            ->schema([
                Textarea::make('reason')
                    ->label(__('capell-privacy-center::privacy.admin.fields.rejection_reason'))
                    ->required()
                    ->maxLength(255)
                    ->rows(3),
            ])
            ->requiresConfirmation()
            ->action(function (PrivacyRequest $record, array $data): void {
                RejectPrivacyRequestAction::run(
                    privacyRequest: $record,
                    reason: (string) $data['reason'],
                );

                Notification::make('privacy-request-rejected')
                    ->title(__('capell-privacy-center::privacy.admin.messages.privacy_request_rejected'))
                    ->success()
                    ->send();
            });
    }

    /**
     * @return array<Action>
     */
    public static function privacyRequestWorkflowActions(): array
    {
        return [
            self::markVerifiedAction(),
            self::markFulfilledAction(),
            self::rejectAction(),
        ];
    }

    /**
     * @return array<string, PageRegistration>
     */
    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListPrivacyRequests::route('/'),
            'edit' => EditPrivacyRequest::route('/{record}/edit'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function typeOptions(): array
    {
        return collect(PrivacyRequestType::cases())->mapWithKeys(fn (PrivacyRequestType $type): array => [$type->value => $type->getLabel()])->all();
    }

    /**
     * @return array<string, string>
     */
    private static function statusOptions(): array
    {
        return collect(PrivacyRequestStatus::cases())->mapWithKeys(fn (PrivacyRequestStatus $status): array => [$status->value => $status->getLabel()])->all();
    }
}
