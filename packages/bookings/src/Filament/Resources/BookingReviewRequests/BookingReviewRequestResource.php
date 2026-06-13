<?php

declare(strict_types=1);

namespace Capell\Bookings\Filament\Resources\BookingReviewRequests;

use BackedEnum;
use Capell\Bookings\Filament\Resources\BookingReviewRequests\Pages\ListBookingReviewRequests;
use Capell\Bookings\Filament\Resources\BookingReviewRequests\Pages\ViewBookingReviewRequest;
use Capell\Bookings\Filament\Resources\BookingReviewRequests\RelationManagers\ReviewParticipantsRelationManager;
use Capell\Bookings\Models\BookingReviewRequest;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Override;

final class BookingReviewRequestResource extends Resource
{
    protected static ?string $slug = 'bookings/review-requests';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    #[Override]
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('status')->label(__('capell-bookings::admin.fields.status'))->badge()->sortable(),
            TextColumn::make('participants_count')->counts('participants')->label(__('capell-bookings::admin.fields.participants'))->sortable(),
            TextColumn::make('rating')->label(__('capell-bookings::admin.fields.rating'))->numeric()->sortable(),
            TextColumn::make('scheduled_for')->label(__('capell-bookings::admin.fields.scheduled_for'))->dateTime()->sortable(),
            TextColumn::make('sent_at')->label(__('capell-bookings::admin.fields.sent_at'))->dateTime()->sortable()->toggleable(),
            TextColumn::make('completed_at')->label(__('capell-bookings::admin.fields.completed_at'))->dateTime()->sortable()->toggleable(),
        ]);
    }

    #[Override]
    public static function getModel(): string
    {
        return BookingReviewRequest::class;
    }

    #[Override]
    public static function getNavigationGroup(): string
    {
        return __('capell-bookings::admin.navigation_group');
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('capell-bookings::admin.resources.review_requests');
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListBookingReviewRequests::route('/'),
            'view' => ViewBookingReviewRequest::route('/{record}'),
        ];
    }

    #[Override]
    public static function getRelations(): array
    {
        return [ReviewParticipantsRelationManager::class];
    }
}
