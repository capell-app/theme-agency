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

final class AppointmentAuditLogsRelationManager extends RelationManager
{
    protected static string|BackedEnum|null $icon = Heroicon::OutlinedClipboardDocumentList;

    protected static string $relationship = 'auditLogs';

    #[Override]
    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('capell-bookings::admin.resources.appointment_audit_logs');
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->latest('occurred_at')->latest('id'))
            ->columns([
                TextColumn::make('event')
                    ->label(__('capell-bookings::admin.fields.event'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('status_from')
                    ->label(__('capell-bookings::admin.fields.status_from'))
                    ->badge()
                    ->toggleable(),
                TextColumn::make('status_to')
                    ->label(__('capell-bookings::admin.fields.status_to'))
                    ->badge()
                    ->toggleable(),
                TextColumn::make('message')
                    ->label(__('capell-bookings::admin.fields.message'))
                    ->limit(80)
                    ->toggleable(),
                TextColumn::make('occurred_at')
                    ->label(__('capell-bookings::admin.fields.occurred_at'))
                    ->dateTime()
                    ->sortable(),
            ]);
    }

    #[Override]
    protected function canCreate(): bool
    {
        return false;
    }
}
