<?php

declare(strict_types=1);

namespace Capell\Bookings\Support;

use Capell\Bookings\Models\AppointmentAuditLog;
use Capell\Bookings\Models\AppointmentRequest;
use Capell\Bookings\Models\BookingAvailabilityException;
use Capell\Bookings\Models\BookingAvailabilityWindow;
use Capell\Bookings\Models\BookingChangeProposal;
use Capell\Bookings\Models\BookingChangeProposalParty;
use Capell\Bookings\Models\BookingGroupSession;
use Capell\Bookings\Models\BookingLessonBundle;
use Capell\Bookings\Models\BookingLessonSkillAssessment;
use Capell\Bookings\Models\BookingLocation;
use Capell\Bookings\Models\BookingMessageLog;
use Capell\Bookings\Models\BookingOwnerPrompt;
use Capell\Bookings\Models\BookingReviewParticipant;
use Capell\Bookings\Models\BookingReviewRequest;
use Capell\Bookings\Models\BookingService;
use Capell\Bookings\Models\BookingStaffMember;
use Capell\Bookings\Models\BookingTravelAdjustment;
use Capell\Bookings\Models\BookingTravelObservation;
use Capell\Bookings\Models\BookingWaitlistEntry;
use Capell\Bookings\Models\BookingWebhookEvent;
use Capell\Bookings\Models\BookingWorkZone;
use Capell\Bookings\Models\LessonNote;
use Capell\Bookings\Models\LessonSeries;
use Capell\Bookings\Models\MessagingConsent;
use Capell\Core\Facades\CapellCore;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;

class BookingsModelRegistrar
{
    /** @var list<class-string> */
    private const array MODELS = [
        BookingService::class,
        BookingStaffMember::class,
        BookingLocation::class,
        BookingAvailabilityWindow::class,
        BookingAvailabilityException::class,
        LessonSeries::class,
        AppointmentRequest::class,
        AppointmentAuditLog::class,
        LessonNote::class,
        MessagingConsent::class,
        BookingMessageLog::class,
        BookingTravelObservation::class,
        BookingTravelAdjustment::class,
        BookingWorkZone::class,
        BookingChangeProposal::class,
        BookingChangeProposalParty::class,
        BookingGroupSession::class,
        BookingReviewRequest::class,
        BookingReviewParticipant::class,
        BookingOwnerPrompt::class,
        BookingWebhookEvent::class,
        BookingWaitlistEntry::class,
        BookingLessonSkillAssessment::class,
        BookingLessonBundle::class,
    ];

    public static function register(): void
    {
        CapellCore::registerModels(self::MODELS);

        /** @var array<string, class-string<Model>> $morphMap */
        $morphMap = collect(self::MODELS)
            ->mapWithKeys(static fn (string $modelClass): array => [Str::snake(class_basename($modelClass)) => $modelClass])
            ->all();

        Relation::morphMap($morphMap, merge: true);
    }
}
