<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Filament\Resources\PrivacyRequests;

use BackedEnum;
use Capell\Core\Facades\CapellCore;
use Capell\PrivacyCenter\Enums\PrivacyRequestStatus;
use Capell\PrivacyCenter\Enums\PrivacyRequestType;
use Capell\PrivacyCenter\Filament\Resources\PrivacyRequests\Pages\EditPrivacyRequest;
use Capell\PrivacyCenter\Filament\Resources\PrivacyRequests\Pages\ListPrivacyRequests;
use Capell\PrivacyCenter\Models\PrivacyRequest;
use Capell\PrivacyCenter\Providers\PrivacyCenterServiceProvider;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
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
            Select::make('status')->label(__('capell-privacy-center::privacy.admin.fields.status'))->options(self::statusOptions())->required(),
            TextInput::make('email_hash')->label(__('capell-privacy-center::privacy.admin.fields.email_hash'))->disabled(),
            DateTimePicker::make('submitted_at')->label(__('capell-privacy-center::privacy.admin.fields.submitted_at'))->disabled(),
            DateTimePicker::make('due_at')->label(__('capell-privacy-center::privacy.admin.fields.due_at')),
            DateTimePicker::make('verified_at')->label(__('capell-privacy-center::privacy.admin.fields.verified_at')),
            DateTimePicker::make('fulfilled_at')->label(__('capell-privacy-center::privacy.admin.fields.fulfilled_at')),
            DateTimePicker::make('rejected_at')->label(__('capell-privacy-center::privacy.admin.fields.rejected_at')),
            TextInput::make('rejection_reason')->label(__('capell-privacy-center::privacy.admin.fields.rejection_reason')),
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
