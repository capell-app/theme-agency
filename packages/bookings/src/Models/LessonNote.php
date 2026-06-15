<?php

declare(strict_types=1);

namespace Capell\Bookings\Models;

use Capell\Bookings\Enums\LessonNoteVisibilityEnum;
use Capell\Core\Models\Site;
use Capell\CustomerPortal\Models\PortalAccount;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property int $id
 * @property int $appointment_request_id
 * @property int|null $portal_account_id
 * @property int|null $site_id
 * @property LessonNoteVisibilityEnum $visibility
 * @property string|null $summary
 * @property string|null $body
 * @property list<int> $photo_media_ids
 * @property CarbonImmutable|null $metadata_stripped_at
 */
class LessonNote extends Model
{
    protected $table = 'booking_lesson_notes';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'appointment_request_id',
        'body',
        'meta',
        'metadata_stripped_at',
        'occurred_at',
        'photo_media_ids',
        'portal_account_id',
        'site_id',
        'summary',
        'visibility',
    ];

    /**
     * @return BelongsTo<AppointmentRequest, $this>
     */
    public function appointmentRequest(): BelongsTo
    {
        return $this->belongsTo(AppointmentRequest::class, 'appointment_request_id');
    }

    /**
     * @return BelongsTo<PortalAccount, $this>
     */
    public function portalAccount(): BelongsTo
    {
        return $this->belongsTo(PortalAccount::class, 'portal_account_id');
    }

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
            'meta' => 'json',
            'metadata_stripped_at' => 'immutable_datetime',
            'occurred_at' => 'immutable_datetime',
            'photo_media_ids' => 'json',
            'visibility' => LessonNoteVisibilityEnum::class,
        ];
    }
}
