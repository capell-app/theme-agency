<?php

declare(strict_types=1);

namespace Capell\Newsletter\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

class ProviderSubscriber extends Model
{
    /** @use HasFactory<Factory<static>> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'subscriber_id',
        'provider_audience_id',
        'remote_id',
        'remote_status',
        'synced_at',
    ];

    protected $table = 'newsletter_provider_subscribers';

    /**
     * @return BelongsTo<Subscriber, $this>
     */
    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(Subscriber::class);
    }

    /**
     * @return BelongsTo<ProviderAudience, $this>
     */
    public function providerAudience(): BelongsTo
    {
        return $this->belongsTo(ProviderAudience::class);
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'synced_at' => 'datetime',
        ];
    }
}
