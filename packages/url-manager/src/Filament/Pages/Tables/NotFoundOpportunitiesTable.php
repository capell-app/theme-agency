<?php

declare(strict_types=1);

namespace Capell\UrlManager\Filament\Pages\Tables;

use Capell\UrlManager\Actions\ConvertNotFoundOpportunityToRedirectAction;
use Capell\UrlManager\Actions\SetNotFoundOpportunityStatusAction;
use Capell\UrlManager\Data\ConvertNotFoundOpportunityData;
use Capell\UrlManager\Enums\NotFoundOpportunityStatus;
use Capell\UrlManager\Enums\UrlManagerPermission;
use Capell\UrlManager\Models\NotFoundOpportunity;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

final class NotFoundOpportunitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => NotFoundOpportunity::query())
            ->columns([
                TextColumn::make('source_url')
                    ->label(__('capell-url-manager::table.source_url'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('suggested_target_url')
                    ->label(__('capell-url-manager::table.suggested_target_url'))
                    ->searchable()
                    ->copyable()
                    ->placeholder(__('capell-url-manager::generic.no_suggestion')),
                TextColumn::make('status')
                    ->label(__('capell-url-manager::table.status'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('hit_count')
                    ->label(__('capell-url-manager::table.hit_count'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('last_seen_at')
                    ->label(__('capell-url-manager::table.last_seen_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('capell-url-manager::table.status'))
                    ->options(NotFoundOpportunityStatus::class),
            ])
            ->recordActions([
                self::convertAction(),
                self::statusAction('ignoreOpportunity', __('capell-url-manager::action.ignore_opportunity'), NotFoundOpportunityStatus::Ignored),
                self::statusAction('reopenOpportunity', __('capell-url-manager::action.reopen_opportunity'), NotFoundOpportunityStatus::Open),
            ])
            ->toolbarActions([
                self::bulkStatusAction('ignoreOpportunities', __('capell-url-manager::action.ignore_opportunities'), NotFoundOpportunityStatus::Ignored),
                self::bulkStatusAction('reopenOpportunities', __('capell-url-manager::action.reopen_opportunities'), NotFoundOpportunityStatus::Open),
            ])
            ->defaultSort('hit_count', 'desc');
    }

    private static function convertAction(): Action
    {
        return Action::make('convert_to_redirect')
            ->label(__('capell-url-manager::action.convert_to_redirect'))
            ->icon('heroicon-o-arrow-uturn-right')
            ->authorize(fn (): bool => self::canManageNotFoundOpportunities())
            ->visible(fn (NotFoundOpportunity $record): bool => $record->status !== NotFoundOpportunityStatus::Converted)
            ->schema([
                TextInput::make('target_url')
                    ->label(__('capell-url-manager::table.target_url'))
                    ->default(fn (NotFoundOpportunity $record): ?string => $record->suggested_target_url)
                    ->required(),
                Select::make('status_code')
                    ->label(__('capell-url-manager::table.status_code'))
                    ->options([
                        301 => '301',
                        302 => '302',
                        307 => '307',
                        308 => '308',
                    ])
                    ->default(301)
                    ->required(),
                TextInput::make('notes')
                    ->label(__('capell-url-manager::table.notes')),
            ])
            ->action(function (NotFoundOpportunity $record, array $data): void {
                ConvertNotFoundOpportunityToRedirectAction::run(new ConvertNotFoundOpportunityData(
                    opportunityId: (int) $record->getKey(),
                    targetUrl: is_string($data['target_url'] ?? null) ? $data['target_url'] : null,
                    statusCode: is_numeric($data['status_code'] ?? null) ? (int) $data['status_code'] : 301,
                    notes: is_string($data['notes'] ?? null) ? $data['notes'] : null,
                ));
            });
    }

    private static function statusAction(string $name, string $label, NotFoundOpportunityStatus $status): Action
    {
        return Action::make($name)
            ->label($label)
            ->authorize(fn (): bool => self::canManageNotFoundOpportunities())
            ->visible(fn (NotFoundOpportunity $record): bool => $record->status !== NotFoundOpportunityStatus::Converted && $record->status !== $status)
            ->action(fn (NotFoundOpportunity $record): int => SetNotFoundOpportunityStatusAction::run($record, $status));
    }

    private static function bulkStatusAction(string $name, string $label, NotFoundOpportunityStatus $status): BulkAction
    {
        return BulkAction::make($name)
            ->label($label)
            ->requiresConfirmation()
            ->authorize(fn (): bool => self::canManageNotFoundOpportunities())
            ->action(fn (Collection $records): int => SetNotFoundOpportunityStatusAction::run($records, $status));
    }

    private static function canManageNotFoundOpportunities(): bool
    {
        if (Gate::allows(UrlManagerPermission::ManageNotFoundOpportunities->value)) {
            return true;
        }

        return auth()->user()?->can(UrlManagerPermission::ManageNotFoundOpportunities->value) === true;
    }
}
