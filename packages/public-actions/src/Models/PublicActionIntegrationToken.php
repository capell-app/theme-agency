<?php

declare(strict_types=1);

namespace Capell\PublicActions\Models;

use Capell\Core\Models\Site;
use Capell\PublicActions\Database\Factories\PublicActionIntegrationTokenFactory;
use Capell\PublicActions\Enums\PublicActionIntegrationProvider;
use Capell\PublicActions\Enums\PublicActionIntegrationTokenAbility;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property PublicActionIntegrationProvider $provider
 * @property int|null $site_id
 * @property string $name
 * @property array<int, string>|null $abilities
 * @property CarbonInterface|null $revoked_at
 */
class PublicActionIntegrationToken extends Model
{
    /** @use HasFactory<PublicActionIntegrationTokenFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'site_id',
        'name',
        'token_hash',
        'provider',
        'abilities',
        'last_used_at',
        'revoked_at',
    ];

    protected static string $factory = PublicActionIntegrationTokenFactory::class;

    public static function hashPlainTextToken(string $plainTextToken): string
    {
        return hash('sha256', $plainTextToken);
    }

    #[Override]
    public function getTable(): string
    {
        $tableName = config('capell-public-actions.tables.integration_tokens');

        return is_string($tableName) ? $tableName : 'public_action_integration_tokens';
    }

    /**
     * @return BelongsTo<Site, $this>
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function isRevoked(): bool
    {
        return $this->revoked_at !== null;
    }

    public function hasAbility(PublicActionIntegrationTokenAbility $ability): bool
    {
        $abilities = $this->abilities ?? [];

        return in_array($ability->value, $abilities, true);
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'provider' => PublicActionIntegrationProvider::class,
            'abilities' => 'array',
            'last_used_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }
}
