<?php

declare(strict_types=1);

namespace Capell\Bookings\Models;

use Capell\Bookings\Enums\BookingOwnerPromptStatusEnum;
use Capell\Core\Models\Site;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property int $id
 * @property BookingOwnerPromptStatusEnum $status
 * @property int|null $site_id
 * @property string $type
 * @property string $title
 * @property string $body
 * @property array<string, mixed>|null $context
 * @property array<string, mixed>|null $meta
 */
class BookingOwnerPrompt extends Model
{
    protected $table = 'booking_owner_prompts';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'body',
        'context',
        'expires_at',
        'meta',
        'site_id',
        'status',
        'title',
        'type',
    ];

    /**
     * @return BelongsTo<Site, $this>
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'site_id');
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'context' => 'json',
            'expires_at' => 'immutable_datetime',
            'meta' => 'json',
            'status' => BookingOwnerPromptStatusEnum::class,
        ];
    }
}
