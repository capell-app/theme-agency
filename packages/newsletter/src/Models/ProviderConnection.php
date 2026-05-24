<?php

declare(strict_types=1);

namespace Capell\Newsletter\Models;

use Capell\Core\Models\Site;
use Capell\Newsletter\Enums\AuthType;
use Capell\Newsletter\Enums\ProviderType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

/**
 * @property ProviderType $provider
 * @property array<string, mixed>|null $credentials
 * @property array<string, mixed>|null $oauth_tokens
 */
class ProviderConnection extends Model
{
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'site_id',
        'name',
        'provider',
        'auth_type',
        'credentials',
        'oauth_tokens',
        'webhook_secret',
        'is_enabled',
    ];

    protected $table = 'newsletter_provider_connections';

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function audiences(): HasMany
    {
        return $this->hasMany(ProviderAudience::class);
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'provider' => ProviderType::class,
            'auth_type' => AuthType::class,
            'credentials' => 'encrypted:array',
            'oauth_tokens' => 'encrypted:array',
            'webhook_secret' => 'encrypted',
            'is_enabled' => 'boolean',
        ];
    }
}
