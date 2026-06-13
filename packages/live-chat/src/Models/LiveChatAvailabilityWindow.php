<?php

declare(strict_types=1);

namespace Capell\LiveChat\Models;

use Capell\Core\Models\Site;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

class LiveChatAvailabilityWindow extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'closes_at',
        'day_of_week',
        'is_active',
        'label',
        'opens_at',
        'site_id',
        'timezone',
    ];

    #[Override]
    public function getTable(): string
    {
        $tableName = config('capell-live-chat.tables.availability_windows');

        return is_string($tableName) ? $tableName : 'live_chat_availability_windows';
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
            'is_active' => 'boolean',
        ];
    }
}
