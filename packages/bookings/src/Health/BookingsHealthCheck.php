<?php

declare(strict_types=1);

namespace Capell\Bookings\Health;

use Capell\Bookings\Actions\BuildAvailableBookingSlotsAction;
use Capell\Bookings\Actions\BuildDayPlanAction;
use Capell\Bookings\Actions\BuildInstructorFuelReportAction;
use Capell\Bookings\Actions\BuildOwnerDigestAction;
use Capell\Bookings\Actions\BuildPortalLessonRowsAction;
use Capell\Bookings\Actions\BuildPublicBookingRequestOptionsAction;
use Capell\Bookings\Actions\BuildPublicBookingRequestPropsAction;
use Capell\Bookings\Actions\BuildServiceAreaHeatmapAction;
use Capell\Bookings\Actions\BuildStaffCalendarFeedAction;
use Capell\Bookings\Actions\CalculateCancellationFeeAction;
use Capell\Bookings\Actions\CancelAppointmentRequestAction;
use Capell\Bookings\Actions\CaptureMessagingConsentAction;
use Capell\Bookings\Actions\CaptureReviewAction;
use Capell\Bookings\Actions\ConfirmAppointmentRequestAction;
use Capell\Bookings\Actions\CreateAppointmentRequestAction;
use Capell\Bookings\Actions\ExpireWaitlistOffersAction;
use Capell\Bookings\Actions\ImportClinicAttendanceCsvAction;
use Capell\Bookings\Actions\JoinBookingWaitlistAction;
use Capell\Bookings\Actions\LinkBookingToPortalAccountAction;
use Capell\Bookings\Actions\MarkBookingWebhookEventProcessedAction;
use Capell\Bookings\Actions\MaterialiseLessonSeriesAction;
use Capell\Bookings\Actions\OfferWaitlistSlotAction;
use Capell\Bookings\Actions\PlaceProvisionalHoldAction;
use Capell\Bookings\Actions\ProposeWeatherCancellationAction;
use Capell\Bookings\Actions\QueueAppointmentReminderAction;
use Capell\Bookings\Actions\RecordBookingWebhookEventAction;
use Capell\Bookings\Actions\RecordLessonNoteAction;
use Capell\Bookings\Actions\RecordLessonSkillAssessmentAction;
use Capell\Bookings\Actions\ScheduleReviewRequestsAction;
use Capell\Bookings\Actions\ScoreBookingRiskAction;
use Capell\Bookings\Actions\ShouldSuppressReviewRequestAction;
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
use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

final class BookingsHealthCheck implements ChecksExtensionHealth
{
    /** @var list<class-string> */
    private const array ACTIONS = [
        BuildPublicBookingRequestOptionsAction::class,
        BuildPublicBookingRequestPropsAction::class,
        BuildAvailableBookingSlotsAction::class,
        BuildDayPlanAction::class,
        BuildInstructorFuelReportAction::class,
        BuildOwnerDigestAction::class,
        BuildPortalLessonRowsAction::class,
        BuildStaffCalendarFeedAction::class,
        BuildServiceAreaHeatmapAction::class,
        CalculateCancellationFeeAction::class,
        CancelAppointmentRequestAction::class,
        CaptureMessagingConsentAction::class,
        CaptureReviewAction::class,
        ConfirmAppointmentRequestAction::class,
        CreateAppointmentRequestAction::class,
        ExpireWaitlistOffersAction::class,
        ImportClinicAttendanceCsvAction::class,
        JoinBookingWaitlistAction::class,
        LinkBookingToPortalAccountAction::class,
        MarkBookingWebhookEventProcessedAction::class,
        MaterialiseLessonSeriesAction::class,
        OfferWaitlistSlotAction::class,
        PlaceProvisionalHoldAction::class,
        ProposeWeatherCancellationAction::class,
        QueueAppointmentReminderAction::class,
        RecordBookingWebhookEventAction::class,
        RecordLessonNoteAction::class,
        RecordLessonSkillAssessmentAction::class,
        ScheduleReviewRequestsAction::class,
        ScoreBookingRiskAction::class,
        ShouldSuppressReviewRequestAction::class,
    ];

    /** @var array<string, class-string> */
    private const array MODELS_BY_TABLE = [
        'booking_services' => BookingService::class,
        'booking_staff_members' => BookingStaffMember::class,
        'booking_locations' => BookingLocation::class,
        'booking_availability_windows' => BookingAvailabilityWindow::class,
        'booking_availability_exceptions' => BookingAvailabilityException::class,
        'lesson_series' => LessonSeries::class,
        'appointment_requests' => AppointmentRequest::class,
        'appointment_audit_logs' => AppointmentAuditLog::class,
        'booking_lesson_notes' => LessonNote::class,
        'booking_messaging_consents' => MessagingConsent::class,
        'booking_message_logs' => BookingMessageLog::class,
        'booking_travel_observations' => BookingTravelObservation::class,
        'booking_travel_adjustments' => BookingTravelAdjustment::class,
        'booking_work_zones' => BookingWorkZone::class,
        'booking_change_proposals' => BookingChangeProposal::class,
        'booking_change_proposal_parties' => BookingChangeProposalParty::class,
        'booking_group_sessions' => BookingGroupSession::class,
        'booking_review_requests' => BookingReviewRequest::class,
        'booking_owner_prompts' => BookingOwnerPrompt::class,
        'booking_webhook_events' => BookingWebhookEvent::class,
        'booking_waitlist_entries' => BookingWaitlistEntry::class,
        'booking_lesson_skill_assessments' => BookingLessonSkillAssessment::class,
        'booking_lesson_bundles' => BookingLessonBundle::class,
    ];

    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }

    public function passes(): bool
    {
        return $this->missingTables() === []
            && $this->missingMorphAliases() === []
            && $this->unresolvableActions() === [];
    }

    /**
     * @return list<string>
     */
    public function missingTables(): array
    {
        return array_values(collect(array_keys(self::MODELS_BY_TABLE))
            ->reject(static fn (string $table): bool => Schema::hasTable($table))
            ->values()
            ->all());
    }

    /**
     * @return list<string>
     */
    public function missingMorphAliases(): array
    {
        return array_values(collect(self::MODELS_BY_TABLE)
            ->reject(static fn (string $modelClass): bool => Relation::getMorphedModel(Str::snake(class_basename($modelClass))) === $modelClass)
            ->map(static fn (string $modelClass): string => Str::snake(class_basename($modelClass)))
            ->values()
            ->all());
    }

    /**
     * @return list<class-string>
     */
    public function unresolvableActions(): array
    {
        $actions = [];

        foreach (self::ACTIONS as $actionClass) {
            if (! app()->make($actionClass) instanceof $actionClass) {
                $actions[] = $actionClass;
            }
        }

        return $actions;
    }
}
