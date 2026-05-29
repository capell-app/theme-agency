<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Filament\Pages\Tables;

use Capell\Admin\Filament\Contracts\TableConfigurator;
use Capell\Diagnostics\Actions\DashboardReports\BuildQueueHealthQueryAction;
use Capell\Diagnostics\Actions\DashboardReports\DeletePendingQueueJobAction;
use Capell\Diagnostics\Actions\DashboardReports\DiscoverQueueMonitorQueuesAction;
use Capell\Diagnostics\Actions\DashboardReports\PruneQueueMonitorsAction;
use Capell\Diagnostics\Actions\DashboardReports\RetryFailedJobAction;
use Capell\Diagnostics\Actions\DashboardReports\RetrySelectedFailedJobsAction;
use Capell\Diagnostics\Actions\DashboardReports\SummarizeFailedJobExceptionAction;
use Capell\Diagnostics\Filament\Pages\QueueHealthPage;
use Capell\Diagnostics\Models\FailedJob;
use Capell\Diagnostics\Models\PendingQueueJob;
use Capell\Diagnostics\Models\QueueMonitor;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use RuntimeException;

class QueueHealthTable implements TableConfigurator
{
    public static function configure(Table $table, ?QueueHealthPage $page = null): Table
    {
        return $table
            ->query(fn (): Builder => BuildQueueHealthQueryAction::run($page?->activeTab ?? 'history'))
            ->columns([
                TextColumn::make('operation_status')
                    ->label(__('capell-diagnostics::package.status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __("capell-diagnostics::package.queue_operations_status_{$state}"))
                    ->color(fn (string $state): string => match ($state) {
                        'succeeded' => 'success',
                        'failed' => 'danger',
                        'running', 'processing' => 'warning',
                        'delayed' => 'gray',
                        default => 'primary',
                    }),
                TextColumn::make('operation_name')
                    ->label(__('capell-diagnostics::package.job'))
                    ->wrap(),
                TextColumn::make('queue')
                    ->label(__('capell-diagnostics::package.queue'))
                    ->sortable(),
                TextColumn::make('attempts_count')
                    ->label(__('capell-diagnostics::package.attempts'))
                    ->alignEnd(),
                TextColumn::make('progress')
                    ->label(__('capell-diagnostics::package.progress'))
                    ->formatStateUsing(fn (?int $state): string => $state === null ? '-' : $state . '%')
                    ->alignEnd(),
                TextColumn::make('duration_seconds')
                    ->label(__('capell-diagnostics::package.duration'))
                    ->formatStateUsing(fn (?int $state): string => $state === null ? '-' : __('capell-diagnostics::package.duration_seconds', ['seconds' => $state]))
                    ->alignEnd(),
                TextColumn::make('operation_started_at')
                    ->label(__('capell-diagnostics::package.started_at'))
                    ->dateTime('Y-m-d H:i:s'),
                TextColumn::make('operation_finished_at')
                    ->label(__('capell-diagnostics::package.finished_at'))
                    ->dateTime('Y-m-d H:i:s'),
                TextColumn::make('exception_message')
                    ->label(__('capell-diagnostics::package.exception'))
                    ->formatStateUsing(fn (?string $state): string => SummarizeFailedJobExceptionAction::run($state))
                    ->limit(100)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters(self::filters())
            ->headerActions(self::headerActions())
            ->recordActions(self::recordActions())
            ->toolbarActions(self::toolbarActions($page))
            ->striped()
            ->paginated();
    }

    /**
     * @return array<int, mixed>
     */
    private static function filters(): array
    {
        return [
            SelectFilter::make('status')
                ->label(__('capell-diagnostics::package.status'))
                ->options([
                    'succeeded' => __('capell-diagnostics::package.queue_operations_status_succeeded'),
                    'failed' => __('capell-diagnostics::package.queue_operations_status_failed'),
                    'running' => __('capell-diagnostics::package.queue_operations_status_running'),
                    'pending' => __('capell-diagnostics::package.queue_operations_status_pending'),
                    'processing' => __('capell-diagnostics::package.queue_operations_status_processing'),
                    'delayed' => __('capell-diagnostics::package.queue_operations_status_delayed'),
                ])
                ->query(fn (Builder $query, array $data): Builder => self::applyStatusFilter($query, $data['value'] ?? null)),
            SelectFilter::make('queue')
                ->label(__('capell-diagnostics::package.queue'))
                ->options(fn (): array => collect(DiscoverQueueMonitorQueuesAction::run())
                    ->mapWithKeys(fn (string $queue): array => [$queue => $queue])
                    ->all()),
            Filter::make('date_range')
                ->label(__('capell-diagnostics::package.date_range'))
                ->schema([
                    DatePicker::make('from')
                        ->label(__('capell-diagnostics::package.date_from')),
                    DatePicker::make('until')
                        ->label(__('capell-diagnostics::package.date_until')),
                ])
                ->query(fn (Builder $query, array $data): Builder => self::applyDateFilter($query, $data)),
            Filter::make('job_class')
                ->label(__('capell-diagnostics::package.job_class'))
                ->schema([
                    TextInput::make('name')
                        ->label(__('capell-diagnostics::package.job_class')),
                ])
                ->query(fn (Builder $query, array $data): Builder => self::applyJobClassFilter($query, $data['name'] ?? null)),
        ];
    }

    /**
     * @return array<int, Action>
     */
    private static function headerActions(): array
    {
        if (! (bool) config('capell-diagnostics.queue_monitor.prune_enabled', true)) {
            return [];
        }

        return [
            Action::make('prune_queue_monitors')
                ->label(__('capell-diagnostics::package.prune_queue_monitors'))
                ->icon('heroicon-o-trash')
                ->color('gray')
                ->requiresConfirmation()
                ->action(function (): void {
                    $count = PruneQueueMonitorsAction::run();

                    Notification::make('capell-diagnostics-queue-pruned')
                        ->title(__('capell-diagnostics::package.queue_monitors_pruned', ['count' => $count]))
                        ->success()
                        ->send();
                }),
        ];
    }

    /**
     * @return array<int, Action>
     */
    private static function recordActions(): array
    {
        return [
            Action::make('details')
                ->label(__('capell-diagnostics::package.details'))
                ->icon('heroicon-o-information-circle')
                ->modalSubmitAction(false)
                ->modalHeading(fn (Model $record): string => (string) $record->getAttribute('operation_name'))
                ->modalDescription(fn (Model $record): string => SummarizeFailedJobExceptionAction::run($record->getAttribute('exception_message'))),
            Action::make('retry')
                ->label(__('capell-diagnostics::package.retry'))
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->visible(fn (Model $record): bool => $record instanceof FailedJob && (bool) config('capell-diagnostics.queue_monitor.retry_enabled', true))
                ->action(function (Model $record): void {
                    try {
                        RetryFailedJobAction::run($record);
                    } catch (RuntimeException $exception) {
                        Notification::make('capell-diagnostics-queue-retry-failed')
                            ->title($exception->getMessage())
                            ->danger()
                            ->send();

                        return;
                    }

                    Notification::make('capell-diagnostics-queue-retried')
                        ->title(__('capell-diagnostics::package.failed_job_retried'))
                        ->success()
                        ->send();
                }),
            Action::make('delete_pending')
                ->label(__('capell-diagnostics::package.delete_pending_job'))
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn (Model $record): bool => $record instanceof PendingQueueJob && (bool) config('capell-diagnostics.queue_monitor.delete_pending_enabled', true))
                ->action(function (Model $record): void {
                    if (! $record instanceof PendingQueueJob) {
                        return;
                    }

                    DeletePendingQueueJobAction::run($record);

                    Notification::make('capell-diagnostics-pending-job-deleted')
                        ->title(__('capell-diagnostics::package.pending_job_deleted'))
                        ->success()
                        ->send();
                }),
        ];
    }

    /**
     * @return array<int, BulkAction>
     */
    private static function toolbarActions(?QueueHealthPage $page = null): array
    {
        if (! (bool) config('capell-diagnostics.queue_monitor.retry_enabled', true)) {
            return [];
        }

        return [
            BulkAction::make('retry_failed_jobs')
                ->label(__('capell-diagnostics::package.retry_selected_failed_jobs'))
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->visible(fn (): bool => ($page?->activeTab ?? 'history') === 'failed')
                ->action(function (EloquentCollection $records): void {
                    $failedJobs = $records->filter(fn (Model $record): bool => $record instanceof FailedJob);

                    try {
                        $retried = RetrySelectedFailedJobsAction::run($failedJobs);
                    } catch (RuntimeException $exception) {
                        Notification::make('capell-diagnostics-queue-bulk-retry-failed')
                            ->title($exception->getMessage())
                            ->danger()
                            ->send();

                        return;
                    }

                    Notification::make('capell-diagnostics-queue-bulk-retried')
                        ->title(__('capell-diagnostics::package.failed_jobs_retried', ['count' => count($retried)]))
                        ->success()
                        ->send();
                })
                ->deselectRecordsAfterCompletion(),
        ];
    }

    private static function applyStatusFilter(Builder $query, mixed $status): Builder
    {
        if (! is_string($status) || $status === '') {
            return $query;
        }

        $model = $query->getModel();

        if ($model instanceof QueueMonitor) {
            return match ($status) {
                'succeeded' => $query->whereNotNull('finished_at')->where('failed', false),
                'failed' => $query->whereNotNull('finished_at')->where('failed', true),
                'running' => $query->whereNull('finished_at'),
                default => $query->whereRaw('1 = 0'),
            };
        }

        if ($model instanceof FailedJob) {
            return $status === 'failed' ? $query : $query->whereRaw('1 = 0');
        }

        if ($model instanceof PendingQueueJob) {
            return match ($status) {
                'processing' => $query->whereNotNull('reserved_at'),
                'delayed' => $query->whereNull('reserved_at')->where('available_at', '>', now()->timestamp),
                'pending' => $query->whereNull('reserved_at')->where('available_at', '<=', now()->timestamp),
                default => $query->whereRaw('1 = 0'),
            };
        }

        return $query;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function applyDateFilter(Builder $query, array $data): Builder
    {
        if ($query->getModel() instanceof PendingQueueJob) {
            return self::applyPendingJobDateFilter($query, $data);
        }

        $column = match (true) {
            $query->getModel() instanceof FailedJob => 'failed_at',
            default => 'started_at',
        };

        return $query
            ->when(
                $data['from'] ?? null,
                fn (Builder $query, string $date): Builder => $query->whereDate($column, '>=', $date),
            )
            ->when(
                $data['until'] ?? null,
                fn (Builder $query, string $date): Builder => $query->whereDate($column, '<=', $date),
            );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function applyPendingJobDateFilter(Builder $query, array $data): Builder
    {
        $from = $data['from'] ?? null;
        $until = $data['until'] ?? null;

        if (is_string($from) && $from !== '') {
            $query->where('created_at', '>=', Carbon::parse($from)->startOfDay()->timestamp);
        }

        if (is_string($until) && $until !== '') {
            $query->where('created_at', '<=', Carbon::parse($until)->endOfDay()->timestamp);
        }

        return $query;
    }

    private static function applyJobClassFilter(Builder $query, mixed $name): Builder
    {
        if (! is_string($name) || $name === '') {
            return $query;
        }

        $model = $query->getModel();
        $needle = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $name) . '%';

        if ($model instanceof QueueMonitor) {
            return $query->where('name', 'like', $needle);
        }

        if ($model instanceof FailedJob || $model instanceof PendingQueueJob) {
            return $query->where('payload', 'like', $needle);
        }

        return $query;
    }
}
