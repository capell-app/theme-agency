<?php

declare(strict_types=1);

namespace Capell\Bookings\Filament\Resources\BookingWaitlistEntries;

use BackedEnum;
use Capell\Bookings\Enums\BookingWaitlistStatusEnum;
use Capell\Bookings\Filament\Resources\BookingWaitlistEntries\Pages\CreateBookingWaitlistEntry;
use Capell\Bookings\Filament\Resources\BookingWaitlistEntries\Pages\EditBookingWaitlistEntry;
use Capell\Bookings\Filament\Resources\BookingWaitlistEntries\Pages\ListBookingWaitlistEntries;
use Capell\Bookings\Models\BookingWaitlistEntry;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Override;

final class BookingWaitlistEntryResource extends Resource
{
    protected static ?string $slug = 'bookings/waitlist';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQueueList;

    protected static ?string $recordTitleAttribute = 'customer_name';

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('capell-bookings::admin.resources.waitlist'))
                ->schema([
                    Select::make('status')->label(__('capell-bookings::admin.fields.status'))->options(self::statusOptions())->required(),
                    TextInput::make('customer_name')->label(__('capell-bookings::admin.fields.customer_name'))->required()->maxLength(255),
                    TextInput::make('customer_email')->label(__('capell-bookings::admin.fields.customer_email'))->email()->required()->maxLength(255),
                    TextInput::make('customer_phone')->label(__('capell-bookings::admin.fields.customer_phone'))->tel()->maxLength(255),
                    TextInput::make('service_id')->label(__('capell-bookings::admin.fields.service'))->integer(),
                    TextInput::make('staff_member_id')->label(__('capell-bookings::admin.fields.staff_member'))->integer(),
                    TextInput::make('location_id')->label(__('capell-bookings::admin.fields.location'))->integer(),
                    DateTimePicker::make('preferred_starts_at')->label(__('capell-bookings::admin.fields.preferred_starts_at')),
                    DateTimePicker::make('preferred_ends_at')->label(__('capell-bookings::admin.fields.preferred_ends_at')),
                    DateTimePicker::make('offered_at')->label(__('capell-bookings::admin.fields.offered_at')),
                    DateTimePicker::make('offer_expires_at')->label(__('capell-bookings::admin.fields.offer_expires_at')),
                ])->columns(2),
        ]);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('customer_name')->label(__('capell-bookings::admin.fields.customer_name'))->searchable()->sortable(),
            TextColumn::make('customer_email')->label(__('capell-bookings::admin.fields.customer_email'))->searchable()->sortable(),
            TextColumn::make('status')->label(__('capell-bookings::admin.fields.status'))->badge()->sortable(),
            TextColumn::make('preferred_starts_at')->label(__('capell-bookings::admin.fields.preferred_starts_at'))->dateTime()->sortable(),
            TextColumn::make('offer_expires_at')->label(__('capell-bookings::admin.fields.offer_expires_at'))->dateTime()->sortable()->toggleable(),
            TextColumn::make('created_at')->label(__('capell-bookings::admin.fields.created_at'))->dateTime()->sortable()->toggleable(),
        ]);
    }

    #[Override]
    public static function getModel(): string
    {
        return BookingWaitlistEntry::class;
    }

    #[Override]
    public static function getNavigationGroup(): string
    {
        return __('capell-bookings::admin.navigation_group');
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('capell-bookings::admin.resources.waitlist');
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListBookingWaitlistEntries::route('/'),
            'create' => CreateBookingWaitlistEntry::route('/create'),
            'edit' => EditBookingWaitlistEntry::route('/{record}/edit'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function statusOptions(): array
    {
        return collect(BookingWaitlistStatusEnum::cases())
            ->mapWithKeys(static fn (BookingWaitlistStatusEnum $status): array => [$status->value => $status->getLabel()])
            ->all();
    }
}
