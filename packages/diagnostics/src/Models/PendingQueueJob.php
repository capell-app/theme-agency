<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * @property int $id
 * @property string|null $queue
 * @property array<string, mixed>|null $payload
 * @property int $attempts
 */
final class PendingQueueJob extends Model
{
    public $timestamps = false;

    /**
     * @var array<string>
     */
    protected $guarded = [];

    #[Override]
    public function getTable(): string
    {
        return (string) config('queue.connections.database.table', 'jobs');
    }

    #[Override]
    public function getConnectionName(): ?string
    {
        $connection = config('queue.connections.database.connection');

        return is_string($connection) && $connection !== '' ? $connection : config('database.default');
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeForConfiguredQueues(Builder $query, array $queues): Builder
    {
        if ($queues === []) {
            return $query;
        }

        return $query->whereIn('queue', $queues);
    }

    public function getOperationStatusAttribute(): string
    {
        if ($this->reserved_at !== null) {
            return 'processing';
        }

        if ($this->available_at !== null && $this->available_at->isFuture()) {
            return 'delayed';
        }

        return 'pending';
    }

    public function getOperationNameAttribute(): string
    {
        $payload = $this->payload;

        if (! is_array($payload)) {
            return (string) __('capell-diagnostics::package.unknown_job');
        }

        $name = $payload['displayName'] ?? $payload['job'] ?? null;

        return is_string($name) && $name !== ''
            ? $name
            : (string) __('capell-diagnostics::package.unknown_job');
    }

    public function getAttemptsCountAttribute(): int
    {
        return (int) $this->attempts;
    }

    public function getOperationStartedAtAttribute(): mixed
    {
        return $this->created_at;
    }

    public function getOperationFinishedAtAttribute(): null
    {
        return null;
    }

    public function getDurationSecondsAttribute(): null
    {
        return null;
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'available_at' => 'datetime',
            'created_at' => 'datetime',
            'reserved_at' => 'datetime',
        ];
    }
}
