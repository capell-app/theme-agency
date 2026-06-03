<?php

declare(strict_types=1);

namespace Capell\AgentBridge\Filament\Resources\Users\RelationManagers;

use BackedEnum;
use Capell\AgentBridge\Models\CapellAgentBridgeAuditEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Override;

final class AgentBridgeAuditEntriesRelationManager extends RelationManager
{
    protected static string|BackedEnum|null $icon = Heroicon::OutlinedClipboardDocumentList;

    protected static string $relationship = 'agentBridgeAuditEntries';

    protected static bool $shouldSkipAuthorization = true;

    #[Override]
    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('capell-agent-bridge::admin.audit_entries');
    }

    /**
     * @param  Builder<CapellAgentBridgeAuditEntry>  $query
     * @return Builder<CapellAgentBridgeAuditEntry>
     */
    public static function scopedQueryForUser(Builder $query, Model $user): Builder
    {
        return $query
            ->where(function (Builder $scopedQuery) use ($user): void {
                $scopedQuery
                    ->where(function (Builder $directUserQuery) use ($user): void {
                        $directUserQuery
                            ->where('user_type', $user->getMorphClass())
                            ->where('user_id', $user->getKey());
                    })
                    ->orWhereHas('agentBridgeToken', function (Builder $tokenQuery) use ($user): void {
                        $tokenQuery
                            ->where('user_type', $user->getMorphClass())
                            ->where('user_id', $user->getKey());
                    });
            });
    }

    /**
     * @return Builder<CapellAgentBridgeAuditEntry>
     */
    #[Override]
    public function getRelationship(): Builder
    {
        return self::scopedQueryForUser(CapellAgentBridgeAuditEntry::query(), $this->ownerRecord);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => self::scopedQueryForUser(CapellAgentBridgeAuditEntry::query(), $this->ownerRecord))
            ->columns([
                TextColumn::make('event')
                    ->label(__('capell-agent-bridge::admin.event'))
                    ->searchable(),
                TextColumn::make('capability_key')
                    ->label(__('capell-agent-bridge::admin.capability'))
                    ->searchable(),
                TextColumn::make('scope')
                    ->label(__('capell-agent-bridge::admin.scope')),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('event')
                    ->label(__('capell-agent-bridge::admin.event'))
                    ->options(fn (): array => self::scopedQueryForUser(CapellAgentBridgeAuditEntry::query(), $this->ownerRecord)
                        ->whereNotNull('event')
                        ->distinct()
                        ->orderBy('event')
                        ->pluck('event', 'event')
                        ->all()),
                SelectFilter::make('capability_key')
                    ->label(__('capell-agent-bridge::admin.capability'))
                    ->options(fn (): array => self::scopedQueryForUser(CapellAgentBridgeAuditEntry::query(), $this->ownerRecord)
                        ->whereNotNull('capability_key')
                        ->distinct()
                        ->orderBy('capability_key')
                        ->pluck('capability_key', 'capability_key')
                        ->all()),
            ])
            ->defaultSort('created_at', 'desc');
    }

    #[Override]
    protected function canCreate(): bool
    {
        return false;
    }
}
