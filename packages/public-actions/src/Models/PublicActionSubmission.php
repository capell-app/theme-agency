<?php

declare(strict_types=1);

namespace Capell\PublicActions\Models;

use Capell\Core\Models\Site;
use Capell\PublicActions\Database\Factories\PublicActionSubmissionFactory;
use Capell\PublicActions\Enums\PublicActionSubmissionStatus;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

/**
 * @property PublicActionSubmissionStatus $status
 * @property int $public_action_id
 * @property int|null $site_id
 * @property string|null $source_type
 * @property string|null $source_id
 * @property array<array-key, mixed>|null $payload
 * @property array<array-key, mixed>|null $metadata
 * @property CarbonInterface|null $submitted_at
 * @property CarbonInterface|null $created_at
 */
class PublicActionSubmission extends Model
{
    /** @use HasFactory<PublicActionSubmissionFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'public_action_id',
        'site_id',
        'source_type',
        'source_id',
        'payload',
        'metadata',
        'status',
        'submitted_at',
    ];

    protected static string $factory = PublicActionSubmissionFactory::class;

    #[Override]
    public function getTable(): string
    {
        $tableName = config('capell-public-actions.tables.submissions');

        return is_string($tableName) ? $tableName : 'public_action_submissions';
    }

    /**
     * @return BelongsTo<PublicAction, $this>
     */
    public function action(): BelongsTo
    {
        return $this->belongsTo(PublicAction::class, 'public_action_id');
    }

    /**
     * @return BelongsTo<Site, $this>
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * @return HasMany<PublicActionDispatchAttempt, $this>
     */
    public function dispatchAttempts(): HasMany
    {
        return $this->hasMany(PublicActionDispatchAttempt::class);
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'payload' => 'encrypted:array',
            'metadata' => 'array',
            'status' => PublicActionSubmissionStatus::class,
            'submitted_at' => 'datetime',
        ];
    }
}
