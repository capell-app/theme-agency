<?php

declare(strict_types=1);

namespace Capell\Bookings\Filament\Resources\BookingChangeProposals;

use BackedEnum;
use Capell\Bookings\Filament\Resources\BookingChangeProposals\Pages\ListBookingChangeProposals;
use Capell\Bookings\Models\BookingChangeProposal;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Override;

final class BookingChangeProposalResource extends Resource
{
    protected static ?string $slug = 'bookings/change-proposals';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPath;

    #[Override]
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('status')->label(__('capell-bookings::admin.fields.status'))->badge()->sortable(),
            TextColumn::make('appointmentRequest.customer_name')->label(__('capell-bookings::admin.fields.customer_name'))->searchable(),
            TextColumn::make('proposed_starts_at')->label(__('capell-bookings::admin.fields.proposed_starts_at'))->dateTime()->sortable(),
            TextColumn::make('expires_at')->label(__('capell-bookings::admin.fields.expires_at'))->dateTime()->sortable(),
            TextColumn::make('reason')->label(__('capell-bookings::admin.fields.reason'))->limit(80)->toggleable(),
        ]);
    }

    #[Override]
    public static function getModel(): string
    {
        return BookingChangeProposal::class;
    }

    #[Override]
    public static function getNavigationGroup(): string
    {
        return __('capell-bookings::admin.navigation_group');
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('capell-bookings::admin.resources.change_proposals');
    }

    #[Override]
    public static function getPages(): array
    {
        return ['index' => ListBookingChangeProposals::route('/')];
    }
}
