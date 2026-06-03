<?php

declare(strict_types=1);

namespace Capell\AgentBridge\Filament\Resources\Users\RelationManagers;

use BackedEnum;
use Capell\AgentBridge\Actions\CreateAgentBridgeTokenAction;
use Capell\AgentBridge\Actions\RevokeAgentBridgeTokenAction;
use Capell\AgentBridge\Actions\RotateAgentBridgeTokenAction;
use Capell\AgentBridge\Models\CapellAgentBridgeToken;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Override;

final class AgentBridgeTokensRelationManager extends RelationManager
{
    protected static string|BackedEnum|null $icon = Heroicon::OutlinedKey;

    protected static string $relationship = 'agentBridgeTokens';

    protected static bool $shouldSkipAuthorization = true;

    #[Override]
    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('capell-agent-bridge::admin.tokens');
    }

    /**
     * @param  Builder<CapellAgentBridgeToken>  $query
     * @return Builder<CapellAgentBridgeToken>
     */
    public static function scopedQueryForUser(Builder $query, Model $user): Builder
    {
        return $query
            ->where('user_type', $user->getMorphClass())
            ->where('user_id', $user->getKey());
    }

    /** @return array<string, string> */
    public static function scopeOptions(): array
    {
        return [
            '*' => __('capell-agent-bridge::admin.scope_all'),
            'capell.cache.run' => __('capell-agent-bridge::admin.scope_cache_run'),
            'capell.pages.read' => __('capell-agent-bridge::admin.scope_pages_read'),
            'capell.pages.write' => __('capell-agent-bridge::admin.scope_pages_write'),
        ];
    }

    /**
     * @return Builder<CapellAgentBridgeToken>
     */
    #[Override]
    public function getRelationship(): Builder
    {
        return self::scopedQueryForUser(CapellAgentBridgeToken::query(), $this->ownerRecord);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => self::scopedQueryForUser(CapellAgentBridgeToken::query(), $this->ownerRecord))
            ->columns([
                TextColumn::make('name')
                    ->label(__('capell-agent-bridge::admin.token_name'))
                    ->searchable(),
                TextColumn::make('scopes')
                    ->label(__('capell-agent-bridge::admin.scopes'))
                    ->formatStateUsing(fn (CapellAgentBridgeToken $record): string => implode(', ', $record->scopes))
                    ->wrap(),
                TextColumn::make('status')
                    ->label(__('capell-agent-bridge::admin.token_status'))
                    ->state(fn (CapellAgentBridgeToken $record): string => $this->statusLabel($record)),
                TextColumn::make('created_from_ip')
                    ->label(__('capell-agent-bridge::admin.created_from_ip'))
                    ->toggleable(),
                TextColumn::make('last_used_at')
                    ->label(__('capell-agent-bridge::admin.last_used_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('rotated_at')
                    ->label(__('capell-agent-bridge::admin.rotated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('revoked_at')
                    ->label(__('capell-agent-bridge::admin.revoked_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('expires_at')
                    ->label(__('capell-agent-bridge::admin.expires_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('rotate')
                    ->label(__('capell-agent-bridge::admin.rotate_token'))
                    ->requiresConfirmation()
                    ->visible(fn (CapellAgentBridgeToken $record): bool => $record->isUsable())
                    ->action(function (CapellAgentBridgeToken $record): void {
                        $result = RotateAgentBridgeTokenAction::run($record);

                        Notification::make('capell_agent_bridge_token_rotated')
                            ->success()
                            ->title(__('capell-agent-bridge::admin.token_rotated'))
                            ->body(__('capell-agent-bridge::admin.token_plaintext_once', [
                                'token' => $result['plainTextToken'],
                            ]))
                            ->send();
                    }),
                Action::make('revoke')
                    ->label(__('capell-agent-bridge::admin.revoke_token'))
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (CapellAgentBridgeToken $record): bool => $record->isUsable())
                    ->action(fn (CapellAgentBridgeToken $record): CapellAgentBridgeToken => RevokeAgentBridgeTokenAction::run($record)),
            ])
            ->headerActions([
                Action::make('createToken')
                    ->label(__('capell-agent-bridge::admin.create_token'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('capell-agent-bridge::admin.token_name'))
                            ->required()
                            ->maxLength(255),
                        CheckboxList::make('scopes')
                            ->label(__('capell-agent-bridge::admin.scopes'))
                            ->options(self::scopeOptions())
                            ->columns(2)
                            ->required(),
                        DateTimePicker::make('expires_at')
                            ->label(__('capell-agent-bridge::admin.expires_at')),
                    ])
                    ->action(function (array $data): void {
                        $user = $this->ownerRecord;

                        if (! $user instanceof Authenticatable) {
                            return;
                        }

                        $expiresAt = $data['expires_at'] ?? null;

                        if (is_string($expiresAt) && $expiresAt !== '') {
                            $expiresAt = CarbonImmutable::parse($expiresAt);
                        }

                        if (! $expiresAt instanceof DateTimeInterface) {
                            $expiresAt = null;
                        }

                        $result = CreateAgentBridgeTokenAction::run(
                            $user,
                            (string) $data['name'],
                            array_values($data['scopes'] ?? []),
                            $expiresAt,
                            request()->ip(),
                        );

                        Notification::make('capell_agent_bridge_token_created')
                            ->success()
                            ->title(__('capell-agent-bridge::admin.token_created'))
                            ->body(__('capell-agent-bridge::admin.token_plaintext_once', [
                                'token' => $result['plainTextToken'],
                            ]))
                            ->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    #[Override]
    protected function canCreate(): bool
    {
        return false;
    }

    private function statusLabel(CapellAgentBridgeToken $token): string
    {
        if ($token->isRevoked() || ! $token->is_enabled) {
            return (string) __('capell-agent-bridge::admin.token_revoked');
        }

        if ($token->isExpired()) {
            return (string) __('capell-agent-bridge::admin.token_expired');
        }

        return (string) __('capell-agent-bridge::admin.token_active');
    }
}
