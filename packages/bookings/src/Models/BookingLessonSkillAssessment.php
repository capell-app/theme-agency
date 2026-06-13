<?php

declare(strict_types=1);

namespace Capell\Bookings\Models;

use Capell\Bookings\Enums\BookingLessonSkillStatusEnum;
use Capell\Core\Models\Site;
use Capell\CustomerPortal\Models\PortalAccount;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property int $id
 * @property int $appointment_request_id
 * @property int|null $site_id
 * @property int|null $portal_account_id
 * @property string $skill
 * @property BookingLessonSkillStatusEnum $status
 * @property int|null $confidence
 * @property string|null $notes
 * @property array<string, mixed>|null $meta
 */
class BookingLessonSkillAssessment extends Model
{
    protected $table = 'booking_lesson_skill_assessments';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'appointment_request_id',
        'confidence',
        'meta',
        'notes',
        'portal_account_id',
        'site_id',
        'skill',
        'status',
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
            'confidence' => 'integer',
            'meta' => 'json',
            'status' => BookingLessonSkillStatusEnum::class,
        ];
    }
}
