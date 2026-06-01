<?php

declare(strict_types=1);

namespace Capell\Bookings\Filament\Resources\BookingAvailabilityExceptions;

use BackedEnum;
use Capell\Bookings\Enums\BookingAvailabilityStatusEnum;
use Capell\Bookings\Filament\Resources\BookingAvailabilityExceptions\Pages\CreateBookingAvailabilityException;
use Capell\Bookings\Filament\Resources\BookingAvailabilityExceptions\Pages\EditBookingAvailabilityException;
use Capell\Bookings\Filament\Resources\BookingAvailabilityExceptions\Pages\ListBookingAvailabilityExceptions;
use Capell\Bookings\Models\BookingAvailabilityException;
use Capell\Bookings\Models\BookingLocation;
use Capell\Bookings\Models\BookingService;
use Capell\Bookings\Models\BookingStaffMember;
use Capell\Core\Support\Database\RuntimeSchemaState;
use Filament\Forms\Components\DatePicker;
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

final class BookingAvailabilityExceptionResource extends Resource
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $recordTitleAttribute = 'date';

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('capell-bookings::admin.resources.availability_exceptions'))
                ->schema([
                    Select::make('service_id')->label(__('capell-bookings::admin.fields.service'))->options(self::serviceOptions())->searchable(),
                    Select::make('staff_member_id')->label(__('capell-bookings::admin.fields.staff_member'))->options(self::staffOptions())->searchable(),
                    Select::make('location_id')->label(__('capell-bookings::admin.fields.location'))->options(self::locationOptions())->searchable(),
                    Select::make('status')->label(__('capell-bookings::admin.fields.status'))->options(self::statusOptions())->required()->default(BookingAvailabilityStatusEnum::Blocked->value),
                    DatePicker::make('date')->label(__('capell-bookings::admin.fields.date'))->required(),
                    TextInput::make('starts_at')->label(__('capell-bookings::admin.fields.starts_at'))->maxLength(5),
                    TextInput::make('ends_at')->label(__('capell-bookings::admin.fields.ends_at'))->maxLength(5),
                    TextInput::make('timezone')->label(__('capell-bookings::admin.fields.timezone'))->required()->default('UTC')->maxLength(64),
                    TextInput::make('capacity')->label(__('capell-bookings::admin.fields.capacity'))->numeric()->minValue(1),
                    TextInput::make('reason')->label(__('capell-bookings::admin.fields.reason'))->maxLength(255),
                    Textarea::make('notes')->label(__('capell-bookings::admin.fields.notes'))->rows(3)->columnSpanFull(),
                ])
                ->columns(3),
        ]);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('date')->label(__('capell-bookings::admin.fields.date'))->date()->sortable(),
            TextColumn::make('status')->label(__('capell-bookings::admin.fields.status'))->badge()->sortable(),
            TextColumn::make('service.name')->label(__('capell-bookings::admin.fields.service'))->searchable()->sortable(),
            TextColumn::make('staffMember.display_name')->label(__('capell-bookings::admin.fields.staff_member'))->searchable()->toggleable(),
            TextColumn::make('location.name')->label(__('capell-bookings::admin.fields.location'))->searchable()->toggleable(),
            TextColumn::make('starts_at')->label(__('capell-bookings::admin.fields.starts_at'))->sortable(),
            TextColumn::make('ends_at')->label(__('capell-bookings::admin.fields.ends_at'))->sortable(),
            TextColumn::make('capacity')->label(__('capell-bookings::admin.fields.capacity'))->numeric()->sortable(),
            TextColumn::make('reason')->label(__('capell-bookings::admin.fields.reason'))->searchable()->toggleable(),
        ]);
    }

    #[Override]
    public static function getModel(): string
    {
        return BookingAvailabilityException::class;
    }

    #[Override]
    public static function getNavigationGroup(): ?string
    {
        return __('capell-bookings::admin.navigation_group');
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('capell-bookings::admin.resources.availability_exceptions');
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListBookingAvailabilityExceptions::route('/'),
            'create' => CreateBookingAvailabilityException::route('/create'),
            'edit' => EditBookingAvailabilityException::route('/{record}/edit'),
        ];
    }

    /**
     * @return array<int, string>
     */
    private static function serviceOptions(): array
    {
        if (! resolve(RuntimeSchemaState::class)->hasTable((new BookingService)->getTable())) {
            return [];
        }

        return BookingService::query()->orderBy('name')->pluck('name', 'id')->all();
    }

    /**
     * @return array<int, string>
     */
    private static function staffOptions(): array
    {
        if (! resolve(RuntimeSchemaState::class)->hasTable((new BookingStaffMember)->getTable())) {
            return [];
        }

        return BookingStaffMember::query()->orderBy('display_name')->pluck('display_name', 'id')->all();
    }

    /**
     * @return array<int, string>
     */
    private static function locationOptions(): array
    {
        if (! resolve(RuntimeSchemaState::class)->hasTable((new BookingLocation)->getTable())) {
            return [];
        }

        return BookingLocation::query()->orderBy('name')->pluck('name', 'id')->all();
    }

    /**
     * @return array<string, string>
     */
    private static function statusOptions(): array
    {
        return collect(BookingAvailabilityStatusEnum::cases())
            ->mapWithKeys(static fn (BookingAvailabilityStatusEnum $status): array => [$status->value => $status->getLabel()])
            ->all();
    }
}
