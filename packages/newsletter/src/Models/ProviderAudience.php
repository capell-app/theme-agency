<?php

declare(strict_types=1);

namespace Capell\Newsletter\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

class ProviderAudience extends Model
{
    /** @use HasFactory<Factory<static>> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'provider_connection_id',
        'name',
        'remote_id',
        'settings',
        'is_default',
        'sync_subscribed_only',
    ];

    protected $table = 'newsletter_provider_audiences';

    /**
     * @return BelongsTo<ProviderConnection, $this>
     */
    public function providerConnection(): BelongsTo
    {
        return $this->belongsTo(ProviderConnection::class);
    }

    /**
     * @return HasMany<ProviderInterestMapping, $this>
     */
    public function interestMappings(): HasMany
    {
        return $this->hasMany(ProviderInterestMapping::class);
    }

    /**
     * @return HasMany<ProviderSubscriber, $this>
     */
    public function providerSubscribers(): HasMany
    {
        return $this->hasMany(ProviderSubscriber::class);
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'is_default' => 'boolean',
            'sync_subscribed_only' => 'boolean',
        ];
    }
}
