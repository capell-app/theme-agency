<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * @property int $id
 * @property string|null $uuid
 * @property string|null $queue
 * @property string|null $payload
 * @property string|null $exception
 * @property CarbonImmutable|null $failed_at
 */
final class FailedJob extends Model
{
    /** @use HasFactory<Factory<static>> */
    use HasFactory;

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $guarded = [];

    #[Override]
    public function getTable(): string
    {
        return config('queue.failed.table', 'failed_jobs');
    }

    #[Override]
    public function getConnectionName(): ?string
    {
        $connection = config('queue.failed.database');

        return is_string($connection) && $connection !== '' ? $connection : config('database.default');
    }

    protected function getOperationStatusAttribute(): string
    {
        return 'failed';
    }

    protected function getOperationNameAttribute(): string
    {
        $payload = json_decode((string) $this->payload, true);

        if (! is_array($payload)) {
            return (string) __('capell-diagnostics::package.unknown_job');
        }

        $name = $payload['displayName'] ?? $payload['job'] ?? null;

        return is_string($name) && $name !== ''
            ? $name
            : (string) __('capell-diagnostics::package.unknown_job');
    }

    protected function getAttemptsCountAttribute(): null
    {
        return null;
    }

    protected function getOperationStartedAtAttribute(): mixed
    {
        return $this->failed_at;
    }

    protected function getOperationFinishedAtAttribute(): mixed
    {
        return $this->failed_at;
    }

    protected function getExceptionMessageAttribute(): ?string
    {
        return $this->exception;
    }

    protected function getDurationSecondsAttribute(): null
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
            'failed_at' => 'immutable_datetime',
        ];
    }
}
