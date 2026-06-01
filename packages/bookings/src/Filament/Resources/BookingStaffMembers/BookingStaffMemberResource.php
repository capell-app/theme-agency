<?php

declare(strict_types=1);

namespace Capell\Bookings\Filament\Resources\BookingStaffMembers;

use BackedEnum;
use Capell\Bookings\Filament\Resources\BookingStaffMembers\Pages\CreateBookingStaffMember;
use Capell\Bookings\Filament\Resources\BookingStaffMembers\Pages\EditBookingStaffMember;
use Capell\Bookings\Filament\Resources\BookingStaffMembers\Pages\ListBookingStaffMembers;
use Capell\Bookings\Models\BookingStaffMember;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Override;

final class BookingStaffMemberResource extends Resource
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $recordTitleAttribute = 'display_name';

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('capell-bookings::admin.resources.staff_members'))
                ->schema([
                    TextInput::make('display_name')->label(__('capell-bookings::admin.fields.display_name'))->required()->maxLength(255),
                    TextInput::make('title')->label(__('capell-bookings::admin.fields.title'))->maxLength(255),
                    TextInput::make('email')->label(__('capell-bookings::admin.fields.email'))->email()->maxLength(255),
                    TextInput::make('phone')->label(__('capell-bookings::admin.fields.phone'))->maxLength(255),
                    TextInput::make('timezone')->label(__('capell-bookings::admin.fields.timezone'))->required()->default('UTC')->maxLength(64),
                    TextInput::make('profile_url')->label(__('capell-bookings::admin.fields.virtual_url'))->url()->maxLength(2048),
                    Toggle::make('active')->label(__('capell-bookings::admin.fields.active'))->default(true),
                ])
                ->columns(2),
        ]);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('display_name')->label(__('capell-bookings::admin.fields.display_name'))->searchable()->sortable(),
            TextColumn::make('title')->label(__('capell-bookings::admin.fields.title'))->searchable()->toggleable(),
            TextColumn::make('email')->label(__('capell-bookings::admin.fields.email'))->searchable()->toggleable(),
            TextColumn::make('timezone')->label(__('capell-bookings::admin.fields.timezone'))->sortable(),
            IconColumn::make('active')->label(__('capell-bookings::admin.fields.active'))->boolean()->sortable(),
        ]);
    }

    #[Override]
    public static function getModel(): string
    {
        return BookingStaffMember::class;
    }

    #[Override]
    public static function getNavigationGroup(): ?string
    {
        return __('capell-bookings::admin.navigation_group');
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('capell-bookings::admin.resources.staff_members');
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListBookingStaffMembers::route('/'),
            'create' => CreateBookingStaffMember::route('/create'),
            'edit' => EditBookingStaffMember::route('/{record}/edit'),
        ];
    }
}
