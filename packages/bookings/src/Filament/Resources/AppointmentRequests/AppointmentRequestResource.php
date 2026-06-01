<?php

declare(strict_types=1);

namespace Capell\Bookings\Filament\Resources\AppointmentRequests;

use BackedEnum;
use Capell\Bookings\Enums\AppointmentRequestStatusEnum;
use Capell\Bookings\Filament\Resources\AppointmentRequests\Pages\EditAppointmentRequest;
use Capell\Bookings\Filament\Resources\AppointmentRequests\Pages\ListAppointmentRequests;
use Capell\Bookings\Models\AppointmentRequest;
use Capell\Bookings\Models\BookingLocation;
use Capell\Bookings\Models\BookingService;
use Capell\Bookings\Models\BookingStaffMember;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Override;

final class AppointmentRequestResource extends Resource
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendar;

    protected static ?string $recordTitleAttribute = 'customer_name';

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('capell-bookings::admin.resources.appointment_requests'))
                ->schema([
                    Select::make('status')->label(__('capell-bookings::admin.fields.status'))->options(self::statusOptions())->required(),
                    Select::make('service_id')->label(__('capell-bookings::admin.fields.service'))->options(self::serviceOptions())->searchable(),
                    Select::make('staff_member_id')->label(__('capell-bookings::admin.fields.staff_member'))->options(self::staffOptions())->searchable(),
                    Select::make('location_id')->label(__('capell-bookings::admin.fields.location'))->options(self::locationOptions())->searchable(),
                    DateTimePicker::make('requested_starts_at')->label(__('capell-bookings::admin.fields.requested_starts_at'))->required(),
                    DateTimePicker::make('requested_ends_at')->label(__('capell-bookings::admin.fields.requested_ends_at'))->required(),
                    TextInput::make('timezone')->label(__('capell-bookings::admin.fields.timezone'))->required()->default('UTC'),
                    TextInput::make('customer_name')->label(__('capell-bookings::admin.fields.customer_name'))->required()->maxLength(255),
                    TextInput::make('customer_email')->label(__('capell-bookings::admin.fields.customer_email'))->email()->required()->maxLength(255),
                    TextInput::make('customer_phone')->label(__('capell-bookings::admin.fields.customer_phone'))->maxLength(255),
                    TextInput::make('source')->label(__('capell-bookings::admin.fields.source'))->maxLength(255),
                    Textarea::make('notes')->label(__('capell-bookings::admin.fields.notes'))->rows(4)->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('customer_name')->label(__('capell-bookings::admin.fields.customer_name'))->searchable()->sortable(),
            TextColumn::make('customer_email')->label(__('capell-bookings::admin.fields.customer_email'))->searchable()->toggleable(),
            TextColumn::make('service.name')->label(__('capell-bookings::admin.fields.service'))->searchable()->sortable(),
            TextColumn::make('staffMember.display_name')->label(__('capell-bookings::admin.fields.staff_member'))->searchable()->toggleable(),
            TextColumn::make('requested_starts_at')->label(__('capell-bookings::admin.fields.requested_starts_at'))->dateTime()->sortable(),
            TextColumn::make('status')->label(__('capell-bookings::admin.fields.status'))->badge()->sortable(),
            TextColumn::make('source')->label(__('capell-bookings::admin.fields.source'))->toggleable(isToggledHiddenByDefault: true),
        ]);
    }

    #[Override]
    public static function getModel(): string
    {
        return AppointmentRequest::class;
    }

    #[Override]
    public static function getNavigationGroup(): ?string
    {
        return __('capell-bookings::admin.navigation_group');
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('capell-bookings::admin.resources.appointment_requests');
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListAppointmentRequests::route('/'),
            'edit' => EditAppointmentRequest::route('/{record}/edit'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function statusOptions(): array
    {
        return collect(AppointmentRequestStatusEnum::cases())
            ->mapWithKeys(static fn (AppointmentRequestStatusEnum $status): array => [$status->value => $status->getLabel()])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private static function serviceOptions(): array
    {
        return BookingService::query()->orderBy('name')->pluck('name', 'id')->all();
    }

    /**
     * @return array<int, string>
     */
    private static function staffOptions(): array
    {
        return BookingStaffMember::query()->orderBy('display_name')->pluck('display_name', 'id')->all();
    }

    /**
     * @return array<int, string>
     */
    private static function locationOptions(): array
    {
        return BookingLocation::query()->orderBy('name')->pluck('name', 'id')->all();
    }
}
