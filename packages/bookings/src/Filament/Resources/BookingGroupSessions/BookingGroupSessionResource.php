<?php

declare(strict_types=1);

namespace Capell\Bookings\Filament\Resources\BookingGroupSessions;

use BackedEnum;
use Capell\Bookings\Enums\BookingGroupSessionStatusEnum;
use Capell\Bookings\Filament\Resources\BookingGroupSessions\Pages\CreateBookingGroupSession;
use Capell\Bookings\Filament\Resources\BookingGroupSessions\Pages\EditBookingGroupSession;
use Capell\Bookings\Filament\Resources\BookingGroupSessions\Pages\ListBookingGroupSessions;
use Capell\Bookings\Models\BookingGroupSession;
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

final class BookingGroupSessionResource extends Resource
{
    protected static ?string $slug = 'bookings/group-sessions';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $recordTitleAttribute = 'title';

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('capell-bookings::admin.resources.group_sessions'))
                ->schema([
                    TextInput::make('title')->label(__('capell-bookings::admin.fields.title'))->required()->maxLength(255),
                    Select::make('status')->label(__('capell-bookings::admin.fields.status'))->options(self::statusOptions())->required(),
                    TextInput::make('service_id')->label(__('capell-bookings::admin.fields.service'))->integer()->required(),
                    TextInput::make('staff_member_id')->label(__('capell-bookings::admin.fields.staff_member'))->integer(),
                    TextInput::make('location_id')->label(__('capell-bookings::admin.fields.location'))->integer(),
                    TextInput::make('capacity')->label(__('capell-bookings::admin.fields.capacity'))->integer()->minValue(1)->required(),
                    TextInput::make('fee_pence')->label(__('capell-bookings::admin.fields.fee_pence'))->integer()->minValue(0),
                    DateTimePicker::make('starts_at')->label(__('capell-bookings::admin.fields.starts_at'))->required(),
                    DateTimePicker::make('ends_at')->label(__('capell-bookings::admin.fields.ends_at'))->required(),
                    Textarea::make('description')->label(__('capell-bookings::admin.fields.description'))->rows(3)->columnSpanFull(),
                ])->columns(2),
        ]);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('title')->label(__('capell-bookings::admin.fields.title'))->searchable()->sortable(),
            TextColumn::make('status')->label(__('capell-bookings::admin.fields.status'))->badge()->sortable(),
            TextColumn::make('starts_at')->label(__('capell-bookings::admin.fields.starts_at'))->dateTime()->sortable(),
            TextColumn::make('capacity')->label(__('capell-bookings::admin.fields.capacity'))->numeric()->sortable(),
            TextColumn::make('external_event_id')->label(__('capell-bookings::admin.fields.external_event_id'))->toggleable(isToggledHiddenByDefault: true),
        ]);
    }

    #[Override]
    public static function getModel(): string
    {
        return BookingGroupSession::class;
    }

    #[Override]
    public static function getNavigationGroup(): string
    {
        return __('capell-bookings::admin.navigation_group');
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('capell-bookings::admin.resources.group_sessions');
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListBookingGroupSessions::route('/'),
            'create' => CreateBookingGroupSession::route('/create'),
            'edit' => EditBookingGroupSession::route('/{record}/edit'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function statusOptions(): array
    {
        return collect(BookingGroupSessionStatusEnum::cases())
            ->mapWithKeys(static fn (BookingGroupSessionStatusEnum $status): array => [$status->value => $status->getLabel()])
            ->all();
    }
}
