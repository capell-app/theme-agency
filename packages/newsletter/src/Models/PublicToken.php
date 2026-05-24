<?php

declare(strict_types=1);

namespace Capell\Newsletter\Models;

use Capell\Newsletter\Enums\PublicTokenType;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property CarbonInterface|null $expires_at
 * @property CarbonInterface|null $used_at
 */
class PublicToken extends Model
{
    /** @use HasFactory<Factory<static>> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'subscriber_id',
        'type',
        'token_hash',
        'expires_at',
        'used_at',
    ];

    protected $table = 'newsletter_public_tokens';

    /**
     * @return BelongsTo<Subscriber, $this>
     */
    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(Subscriber::class);
    }

    public function isUsable(): bool
    {
        if ($this->used_at !== null) {
            return false;
        }

        return $this->expires_at === null || $this->expires_at->isFuture();
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'type' => PublicTokenType::class,
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }
}
