<?php

declare(strict_types=1);

namespace Capell\Bookings\Filament\Resources\BookingAvailabilityWindows;

use BackedEnum;
use Capell\Bookings\Enums\BookingAvailabilityStatusEnum;
use Capell\Bookings\Filament\Resources\BookingAvailabilityWindows\Pages\CreateBookingAvailabilityWindow;
use Capell\Bookings\Filament\Resources\BookingAvailabilityWindows\Pages\EditBookingAvailabilityWindow;
use Capell\Bookings\Filament\Resources\BookingAvailabilityWindows\Pages\ListBookingAvailabilityWindows;
use Capell\Bookings\Models\BookingAvailabilityWindow;
use Capell\Bookings\Models\BookingLocation;
use Capell\Bookings\Models\BookingService;
use Capell\Bookings\Models\BookingStaffMember;
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

final class BookingAvailabilityWindowResource extends Resource
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static ?string $recordTitleAttribute = 'starts_at';

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('capell-bookings::admin.resources.availability_windows'))
                ->schema([
                    Select::make('service_id')->label(__('capell-bookings::admin.fields.service'))->options(self::serviceOptions())->searchable(),
                    Select::make('staff_member_id')->label(__('capell-bookings::admin.fields.staff_member'))->options(self::staffOptions())->searchable(),
                    Select::make('location_id')->label(__('capell-bookings::admin.fields.location'))->options(self::locationOptions())->searchable(),
                    Select::make('status')->label(__('capell-bookings::admin.fields.status'))->options(self::statusOptions())->required()->default(BookingAvailabilityStatusEnum::Available->value),
                    Select::make('day_of_week')->label(__('capell-bookings::admin.fields.day_of_week'))->options(__('capell-bookings::admin.weekdays'))->required(),
                    TextInput::make('starts_at')->label(__('capell-bookings::admin.fields.starts_at'))->required()->maxLength(5),
                    TextInput::make('ends_at')->label(__('capell-bookings::admin.fields.ends_at'))->required()->maxLength(5),
                    TextInput::make('timezone')->label(__('capell-bookings::admin.fields.timezone'))->required()->default('UTC')->maxLength(64),
                    TextInput::make('capacity')->label(__('capell-bookings::admin.fields.capacity'))->numeric()->minValue(1)->default(1),
                    DatePicker::make('effective_from')->label(__('capell-bookings::admin.fields.effective_from')),
                    DatePicker::make('effective_until')->label(__('capell-bookings::admin.fields.effective_until')),
                    Textarea::make('notes')->label(__('capell-bookings::admin.fields.notes'))->rows(3)->columnSpanFull(),
                ])
                ->columns(3),
        ]);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('service.name')->label(__('capell-bookings::admin.fields.service'))->searchable()->sortable(),
            TextColumn::make('staffMember.display_name')->label(__('capell-bookings::admin.fields.staff_member'))->searchable()->toggleable(),
            TextColumn::make('location.name')->label(__('capell-bookings::admin.fields.location'))->searchable()->toggleable(),
            TextColumn::make('day_of_week')->label(__('capell-bookings::admin.fields.day_of_week'))->formatStateUsing(static fn (int|string|null $state): string => self::weekdayLabel($state))->sortable(),
            TextColumn::make('starts_at')->label(__('capell-bookings::admin.fields.starts_at'))->sortable(),
            TextColumn::make('ends_at')->label(__('capell-bookings::admin.fields.ends_at'))->sortable(),
            TextColumn::make('capacity')->label(__('capell-bookings::admin.fields.capacity'))->numeric()->sortable(),
            TextColumn::make('status')->label(__('capell-bookings::admin.fields.status'))->badge()->sortable(),
        ]);
    }

    #[Override]
    public static function getModel(): string
    {
        return BookingAvailabilityWindow::class;
    }

    #[Override]
    public static function getNavigationGroup(): ?string
    {
        return __('capell-bookings::admin.navigation_group');
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('capell-bookings::admin.resources.availability_windows');
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListBookingAvailabilityWindows::route('/'),
            'create' => CreateBookingAvailabilityWindow::route('/create'),
            'edit' => EditBookingAvailabilityWindow::route('/{record}/edit'),
        ];
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

    /**
     * @return array<string, string>
     */
    private static function statusOptions(): array
    {
        return collect(BookingAvailabilityStatusEnum::cases())
            ->mapWithKeys(static fn (BookingAvailabilityStatusEnum $status): array => [$status->value => $status->getLabel()])
            ->all();
    }

    private static function weekdayLabel(int|string|null $value): string
    {
        $weekdays = __('capell-bookings::admin.weekdays');

        return is_array($weekdays) && array_key_exists((int) $value, $weekdays)
            ? (string) $weekdays[(int) $value]
            : (string) $value;
    }
}
