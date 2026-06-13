<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Models;

use Capell\EquestrianClinics\Enums\EquestrianCommunicationChannelEnum;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property int $id
 * @property int|null $tour_day_id
 * @property int|null $tour_day_slot_id
 * @property EquestrianCommunicationChannelEnum $channel
 * @property string $audience
 * @property string|null $subject
 * @property string $message
 * @property int $recipient_count
 * @property array<int, array<string, mixed>>|null $recipients
 * @property CarbonImmutable|null $sent_at
 * @property array<string, mixed>|null $meta
 * @property-read EquestrianTourDay|null $tourDay
 * @property-read EquestrianTourDaySlot|null $slot
 */
final class EquestrianCommunicationLog extends Model
{
    protected $table = 'equestrian_communication_logs';

    protected $guarded = [];

    /**
     * @return BelongsTo<EquestrianTourDay, $this>
     */
    public function tourDay(): BelongsTo
    {
        return $this->belongsTo(EquestrianTourDay::class, 'tour_day_id');
    }

    /**
     * @return BelongsTo<EquestrianTourDaySlot, $this>
     */
    public function slot(): BelongsTo
    {
        return $this->belongsTo(EquestrianTourDaySlot::class, 'tour_day_slot_id');
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'channel' => EquestrianCommunicationChannelEnum::class,
            'meta' => 'json',
            'recipient_count' => 'integer',
            'recipients' => 'json',
            'sent_at' => 'immutable_datetime',
        ];
    }
}
