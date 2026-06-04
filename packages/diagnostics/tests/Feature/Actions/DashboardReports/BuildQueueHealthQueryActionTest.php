<?php

declare(strict_types=1);

namespace Capell\Admin\Tests\Feature\Actions\DashboardReports;

use Capell\Diagnostics\Actions\DashboardReports\BuildQueueHealthQueryAction;
use Capell\Diagnostics\Filament\Pages\Tables\QueueHealthTable;
use Capell\Diagnostics\Models\FailedJob;
use Capell\Diagnostics\Models\PendingQueueJob;
use Capell\Diagnostics\Models\QueueMonitor;
use Filament\Actions\Action;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Mockery;
use ReflectionMethod;

describe('BuildQueueHealthQueryAction', function (): void {
    it('returns query builder for failed jobs', function (): void {
        // Act
        $query = BuildQueueHealthQueryAction::run();

        // Assert
        expect($query)->not->toBeNull();
    });

    it('orders failed jobs by most recent first', function (): void {
        // Act
        $query = BuildQueueHealthQueryAction::run();
        $sql = $query->toSql();

        // Assert
        expect($sql)->toContain('order by');
    });
});

it('builds queue health table columns filters and row operations', function (): void {
    $table = QueueHealthTable::configure(queueHealthTableForCoverage());

    expect(array_keys($table->getColumns()))->toContain(
        'operation_status',
        'operation_name',
        'queue',
        'attempts_count',
        'progress',
        'duration_seconds',
        'operation_started_at',
        'operation_finished_at',
        'exception_message',
    )->and(array_keys($table->getFilters()))->toContain('status', 'queue', 'date_range', 'job_class')
        ->and(collect($table->getRecordActions())
            ->filter(fn (mixed $action): bool => $action instanceof Action)
            ->map(fn (Action $action): ?string => $action->getName())
            ->all())->toContain('details', 'retry', 'delete_pending');
});

it('applies queue health filters to the correct storage tables', function (): void {
    $statusFilter = new ReflectionMethod(QueueHealthTable::class, 'applyStatusFilter');
    $dateFilter = new ReflectionMethod(QueueHealthTable::class, 'applyDateFilter');
    $jobClassFilter = new ReflectionMethod(QueueHealthTable::class, 'applyJobClassFilter');

    $failedSql = $statusFilter->invoke(null, FailedJob::query(), 'failed')->toSql();
    $runningSql = $statusFilter->invoke(null, QueueMonitor::query(), 'running')->toSql();
    $delayedSql = $statusFilter->invoke(null, PendingQueueJob::query(), 'delayed')->toSql();
    $pendingDateSql = $dateFilter->invoke(null, PendingQueueJob::query(), ['from' => '2026-06-01', 'until' => '2026-06-02'])->toSql();
    $monitorClassSql = $jobClassFilter->invoke(null, QueueMonitor::query(), 'Import%Job')->toSql();
    $pendingClassSql = $jobClassFilter->invoke(null, PendingQueueJob::query(), 'Import%Job')->toSql();

    expect($failedSql)->not->toContain('1 = 0')
        ->and($runningSql)->toContain('"finished_at" is null')
        ->and($delayedSql)->toContain('"reserved_at" is null')
        ->and($delayedSql)->toContain('"available_at" >')
        ->and($pendingDateSql)->toContain('"created_at" >=')
        ->and($pendingDateSql)->toContain('"created_at" <=')
        ->and($monitorClassSql)->toContain('"name" like ?')
        ->and($pendingClassSql)->toContain('"payload" like ?');
});

function queueHealthTableForCoverage(): Table
{
    $livewire = Mockery::mock(HasTable::class);
    $livewire->shouldIgnoreMissing();
    $livewire->shouldReceive('makeFilamentTranslatableContentDriver')->andReturn(null)->byDefault();
    $livewire->shouldReceive('getTableFilterState')->andReturn([])->byDefault();
    $livewire->shouldReceive('isTableLoaded')->andReturnTrue()->byDefault();
    $livewire->shouldReceive('getTableArguments')->andReturn([])->byDefault();

    return Table::make($livewire);
}
