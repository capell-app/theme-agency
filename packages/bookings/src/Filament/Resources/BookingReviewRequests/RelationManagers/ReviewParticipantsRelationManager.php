<?php

declare(strict_types=1);

namespace Capell\Bookings\Filament\Resources\BookingReviewRequests\RelationManagers;

use BackedEnum;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Override;

final class ReviewParticipantsRelationManager extends RelationManager
{
    protected static string|BackedEnum|null $icon = Heroicon::OutlinedUsers;

    protected static string $relationship = 'participants';

    #[Override]
    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('capell-bookings::admin.resources.review_participants');
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->latest('required')->latest('sent_at')->latest('id'))
            ->columns([
                TextColumn::make('role')->label(__('capell-bookings::admin.fields.role'))->badge()->sortable(),
                TextColumn::make('name')->label(__('capell-bookings::admin.fields.name'))->searchable()->sortable(),
                TextColumn::make('email')->label(__('capell-bookings::admin.fields.email'))->searchable()->toggleable(),
                TextColumn::make('status')->label(__('capell-bookings::admin.fields.status'))->badge()->sortable(),
                IconColumn::make('required')->label(__('capell-bookings::admin.fields.required'))->boolean(),
                TextColumn::make('rating')->label(__('capell-bookings::admin.fields.rating'))->numeric()->sortable(),
                TextColumn::make('sent_at')->label(__('capell-bookings::admin.fields.sent_at'))->dateTime()->sortable()->toggleable(),
                TextColumn::make('completed_at')->label(__('capell-bookings::admin.fields.completed_at'))->dateTime()->sortable()->toggleable(),
            ]);
    }

    #[Override]
    protected function canCreate(): bool
    {
        return false;
    }
}
