<?php

declare(strict_types=1);

namespace Capell\LoginAudit\Filament\Resources\Users\RelationManagers;

use BackedEnum;
use Capell\Admin\Filament\Components\Tables\Columns\DateColumn;
use Capell\LoginAudit\Actions\BuildLoginAuditsCsvAction;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Override;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class LoginAuditsRelationManager extends RelationManager
{
    protected static string|BackedEnum|null $icon = 'heroicon-o-shield-check';

    protected static ?string $recordTitleAttribute = 'id';

    protected static string $relationship = 'authentications';

    #[Override]
    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('capell-login-audit::settings.login_audits');
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query
                ->where('authenticatable_type', $this->getOwnerRecord()->getMorphClass())
                ->where('authenticatable_id', $this->getOwnerRecord()->getKey()))
            ->description(__('capell-login-audit::settings.login_audits_description'))
            ->defaultSort('login_at', 'desc')
            ->columns([
                TextColumn::make('login_successful')
                    ->label(__('capell-login-audit::settings.access_status'))
                    ->badge()
                    ->formatStateUsing(fn (mixed $state): string => $this->loginSuccessful($state)
                        ? __('capell-login-audit::settings.access_successful')
                        : __('capell-login-audit::settings.access_failed'))
                    ->color(fn (mixed $state): string => $this->loginSuccessful($state) ? 'success' : 'danger')
                    ->sortable(),
                TextColumn::make('ip_address')
                    ->label(trans('filament-authentication-log::filament-authentication-log.column.ip_address'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user_agent')
                    ->label(trans('filament-authentication-log::filament-authentication-log.column.user_agent'))
                    ->searchable()
                    ->wrap()
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        if (! is_string($state) || mb_strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }

                        return $state;
                    }),
                TextColumn::make('device_name')
                    ->label(__('capell-login-audit::settings.device'))
                    ->placeholder(__('capell-admin::generic.missing'))
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_trusted')
                    ->label(__('capell-login-audit::settings.trusted_device'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                DateColumn::make('login_at')
                    ->label(trans('filament-authentication-log::filament-authentication-log.column.login_at'))
                    ->sortable(),
                DateColumn::make('last_activity_at')
                    ->label(__('capell-login-audit::settings.last_activity_at'))
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('cleared_by_user')
                    ->label(trans('filament-authentication-log::filament-authentication-log.column.cleared_by_user'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_trusted')
                    ->label(__('capell-login-audit::settings.trusted_device')),
            ])
            ->headerActions([
                Action::make('exportLoginAuditsCsv')
                    ->label(__('capell-login-audit::settings.export_csv'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(fn (): StreamedResponse => $this->downloadCsv()),
            ]);
    }

    #[Override]
    protected function canCreate(): bool
    {
        return false;
    }

    #[Override]
    protected function canEdit(Model $record): bool
    {
        return false;
    }

    #[Override]
    protected function canDelete(Model $record): bool
    {
        return false;
    }

    private function loginSuccessful(mixed $state): bool
    {
        return in_array($state, [true, 1, '1'], true);
    }

    private function downloadCsv(): StreamedResponse
    {
        $ownerRecord = $this->getOwnerRecord();

        return response()->streamDownload(
            function () use ($ownerRecord): void {
                echo BuildLoginAuditsCsvAction::run($ownerRecord);
            },
            'login-audits-' . $ownerRecord->getKey() . '-' . now()->format('Y-m-d-His') . '.csv',
            ['Content-Type' => 'text/csv'],
        );
    }
}
