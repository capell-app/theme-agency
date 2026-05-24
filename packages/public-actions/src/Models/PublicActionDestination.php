<?php

declare(strict_types=1);

namespace Capell\PublicActions\Models;

use Capell\PublicActions\Database\Factories\PublicActionDestinationFactory;
use Capell\PublicActions\Enums\PublicActionDestinationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

/**
 * @property PublicActionDestinationStatus $status
 * @property int $public_action_id
 * @property string $adapter
 * @property string $name
 * @property string|null $endpoint_url
 * @property string|null $secret
 * @property array<array-key, mixed>|null $headers
 * @property array<array-key, mixed>|null $settings
 */
class PublicActionDestination extends Model
{
    /** @use HasFactory<PublicActionDestinationFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'public_action_id',
        'adapter',
        'name',
        'status',
        'endpoint_url',
        'secret',
        'headers',
        'settings',
    ];

    protected static string $factory = PublicActionDestinationFactory::class;

    #[Override]
    public function getTable(): string
    {
        $tableName = config('capell-public-actions.tables.destinations');

        return is_string($tableName) ? $tableName : 'public_action_destinations';
    }

    /**
     * @return BelongsTo<PublicAction, $this>
     */
    public function action(): BelongsTo
    {
        return $this->belongsTo(PublicAction::class, 'public_action_id');
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
            'status' => PublicActionDestinationStatus::class,
            'endpoint_url' => 'encrypted',
            'secret' => 'encrypted',
            'headers' => 'encrypted:array',
            'settings' => 'array',
        ];
    }
}
