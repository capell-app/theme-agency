<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Models;

use Carbon\CarbonImmutable;
use Croustibat\FilamentJobsMonitor\Models\QueueMonitor as BaseQueueMonitor;
use Illuminate\Database\Eloquent\Builder;
use Override;

/**
 * @property int $id
 * @property string|int $job_id
 * @property string|null $name
 * @property string|null $queue
 * @property bool $failed
 * @property int $attempt
 * @property int|null $progress
 * @property string|null $exception_message
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $finished_at
 * @property CarbonImmutable|null $started_at
 * @property CarbonImmutable|null $updated_at
 */
final class QueueMonitor extends BaseQueueMonitor
{
    /**
     * @var list<string>
     */
    protected $fillable = [];

    /**
     * @var list<string>
     */
    protected $guarded = [];

    #[Override]
    public function getTable(): string
    {
        return 'queue_monitors';
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    protected function scopeFailed(Builder $query): Builder
    {
        return $query
            ->whereNotNull('finished_at')
            ->where('failed', true);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    protected function scopeSucceeded(Builder $query): Builder
    {
        return $query
            ->whereNotNull('finished_at')
            ->where('failed', false);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    protected function scopeRunning(Builder $query): Builder
    {
        return $query->whereNull('finished_at');
    }

    protected function getOperationStatusAttribute(): string
    {
        if ($this->finished_at === null) {
            return 'running';
        }

        return $this->failed ? 'failed' : 'succeeded';
    }

    protected function getOperationNameAttribute(): string
    {
        return $this->name ?: (string) __('capell-diagnostics::package.unknown_job');
    }

    protected function getAttemptsCountAttribute(): int
    {
        return $this->attempt;
    }

    protected function getOperationStartedAtAttribute(): mixed
    {
        return $this->started_at;
    }

    protected function getOperationFinishedAtAttribute(): mixed
    {
        return $this->finished_at;
    }

    protected function getDurationSecondsAttribute(): ?int
    {
        if ($this->started_at === null || $this->finished_at === null) {
            return null;
        }

        return max(0, (int) $this->started_at->diffInSeconds($this->finished_at));
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'failed' => 'bool',
            'started_at' => 'immutable_datetime',
            'finished_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }
}
