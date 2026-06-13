<?php

declare(strict_types=1);

namespace Capell\Bookings\Filament\Resources\AppointmentRequests\RelationManagers;

use BackedEnum;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Override;

final class LessonNotesRelationManager extends RelationManager
{
    protected static string|BackedEnum|null $icon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static string $relationship = 'lessonNotes';

    #[Override]
    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('capell-bookings::admin.resources.lesson_notes');
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->latest('occurred_at')->latest('id'))
            ->columns([
                TextColumn::make('visibility')
                    ->label(__('capell-bookings::admin.fields.visibility'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('summary')
                    ->label(__('capell-bookings::admin.fields.summary'))
                    ->searchable()
                    ->limit(80),
                TextColumn::make('photo_media_ids')
                    ->label(__('capell-bookings::admin.fields.photos'))
                    ->formatStateUsing(static fn (mixed $state): string => (string) count(is_array($state) ? $state : []))
                    ->toggleable(),
                TextColumn::make('occurred_at')
                    ->label(__('capell-bookings::admin.fields.occurred_at'))
                    ->dateTime()
                    ->sortable(),
            ]);
    }
}
