<?php

declare(strict_types=1);

namespace Capell\LiveChat\Models;

use Capell\Core\Models\Site;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property int $id
 * @property int $site_id
 * @property CarbonImmutable $date
 * @property bool $is_available
 * @property string|null $opens_at
 * @property string|null $closes_at
 * @property string|null $message
 * @property string $timezone
 */
class LiveChatAvailabilityException extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'closes_at',
        'date',
        'is_available',
        'message',
        'opens_at',
        'site_id',
        'timezone',
    ];

    #[Override]
    public function getTable(): string
    {
        $tableName = config('capell-live-chat.tables.availability_exceptions');

        return is_string($tableName) ? $tableName : 'live_chat_availability_exceptions';
    }

    /**
     * @return BelongsTo<Site, $this>
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'date' => 'immutable_date',
            'is_available' => 'boolean',
        ];
    }
}
