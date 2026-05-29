<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Models;

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
 */
final class FailedJob extends Model
{
    /** @use HasFactory<Factory<static>> */
    use HasFactory;

    public $timestamps = false;

    /**
     * @var array<string>
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

    public function getOperationStatusAttribute(): string
    {
        return 'failed';
    }

    public function getOperationNameAttribute(): string
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

    public function getAttemptsCountAttribute(): ?int
    {
        return null;
    }

    public function getOperationStartedAtAttribute(): mixed
    {
        return $this->failed_at;
    }

    public function getOperationFinishedAtAttribute(): mixed
    {
        return $this->failed_at;
    }

    public function getExceptionMessageAttribute(): ?string
    {
        return $this->exception;
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
            'failed_at' => 'immutable_datetime',
        ];
    }
}
