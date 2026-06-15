<?php

declare(strict_types=1);

namespace Capell\Bookings\Health;

use Capell\Bookings\Actions\AddReviewParticipantAction;
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
use Capell\Bookings\Actions\CaptureReviewParticipantResponseAction;
use Capell\Bookings\Actions\CompleteReviewLoopIfReadyAction;
use Capell\Bookings\Actions\ConfirmAppointmentRequestAction;
use Capell\Bookings\Actions\CreateAppointmentRequestAction;
use Capell\Bookings\Actions\CreatePortalAccessTokenAction;
use Capell\Bookings\Actions\CreateReviewLoopAction;
use Capell\Bookings\Actions\CreateReviewRequestUrlAction;
use Capell\Bookings\Actions\ExpireWaitlistOffersAction;
use Capell\Bookings\Actions\ImportClinicAttendanceCsvAction;
use Capell\Bookings\Actions\IssueBookingChangeProposalTokenAction;
use Capell\Bookings\Actions\IssueReviewParticipantUrlAction;
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
use Capell\Bookings\Actions\RemindPendingReviewParticipantsAction;
use Capell\Bookings\Actions\ResolveBookingChangeProposalTokenAction;
use Capell\Bookings\Actions\ResolvePortalAccessTokenAction;
use Capell\Bookings\Actions\ResolveReviewParticipantTokenAction;
use Capell\Bookings\Actions\ResolveReviewRequestTokenAction;
use Capell\Bookings\Actions\ScheduleReviewRequestsAction;
use Capell\Bookings\Actions\ScoreBookingRiskAction;
use Capell\Bookings\Actions\ShouldSuppressReviewRequestAction;
use Capell\Bookings\Contracts\PublicBookingRequestRenderer;
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
use Capell\Bookings\Settings\BookingsSettings;
use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

final class BookingsHealthCheck implements ChecksExtensionHealth
{
    /** @var list<class-string> */
    private const array ACTIONS = [
        BuildPublicBookingRequestOptionsAction::class,
        BuildPublicBookingRequestPropsAction::class,
        AddReviewParticipantAction::class,
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
        CaptureReviewParticipantResponseAction::class,
        CompleteReviewLoopIfReadyAction::class,
        ConfirmAppointmentRequestAction::class,
        CreateAppointmentRequestAction::class,
        CreatePortalAccessTokenAction::class,
        CreateReviewLoopAction::class,
        CreateReviewRequestUrlAction::class,
        ExpireWaitlistOffersAction::class,
        ImportClinicAttendanceCsvAction::class,
        IssueBookingChangeProposalTokenAction::class,
        IssueReviewParticipantUrlAction::class,
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
        RemindPendingReviewParticipantsAction::class,
        ResolveBookingChangeProposalTokenAction::class,
        ResolvePortalAccessTokenAction::class,
        ResolveReviewParticipantTokenAction::class,
        ResolveReviewRequestTokenAction::class,
        ScheduleReviewRequestsAction::class,
        ScoreBookingRiskAction::class,
        ShouldSuppressReviewRequestAction::class,
    ];

    /** @var list<string> */
    private const array COMMANDS = [
        'capell:bookings:send-due-reminders',
        'capell:bookings:expire-workflow-state',
        'capell:bookings:schedule-review-requests',
        'capell:bookings:prune-retention-data',
    ];

    /** @var array<string, string> */
    private const array SCHEDULE_EXPRESSIONS = [
        'capell:bookings:send-due-reminders' => '*/5 * * * *',
        'capell:bookings:expire-workflow-state' => '*/5 * * * *',
        'capell:bookings:schedule-review-requests' => '0 * * * *',
        'capell:bookings:prune-retention-data' => '0 0 * * *',
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
        'booking_review_participants' => BookingReviewParticipant::class,
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

    /**
     * @return Collection<int, DoctorCheckResultData>
     */
    public static function runDiagnostics(): Collection
    {
        $check = new self;

        return collect([
            $check->storageTablesCheck(),
            $check->morphMapCheck(),
            $check->actionsCheck(),
            $check->publicRoutesCheck(),
            $check->publicRendererCheck(),
            $check->scheduledCommandsCheck(),
            $check->settingsMigrationCheck(),
            $check->consoleCommandsCheck(),
        ]);
    }

    public static function passed(): bool
    {
        return self::runDiagnostics()
            ->every(static fn (DoctorCheckResultData $result): bool => $result->passed);
    }

    public function passes(): bool
    {
        return self::passed();
    }

    public function storageTablesCheck(): DoctorCheckResultData
    {
        $missingTables = $this->missingTables();

        return new DoctorCheckResultData(
            label: (string) __('capell-bookings::package.health.storage_tables.label'),
            passed: $missingTables === [],
            message: $missingTables === []
                ? (string) __('capell-bookings::package.health.storage_tables.passed')
                : (string) __('capell-bookings::package.health.storage_tables.failed', ['tables' => implode(', ', $missingTables)]),
            remediation: $missingTables === []
                ? null
                : (string) __('capell-bookings::package.health.storage_tables.remediation'),
        );
    }

    public function morphMapCheck(): DoctorCheckResultData
    {
        $missingAliases = $this->missingMorphAliases();

        return new DoctorCheckResultData(
            label: (string) __('capell-bookings::package.health.morph_map.label'),
            passed: $missingAliases === [],
            message: $missingAliases === []
                ? (string) __('capell-bookings::package.health.morph_map.passed')
                : (string) __('capell-bookings::package.health.morph_map.failed', ['aliases' => implode(', ', $missingAliases)]),
            remediation: $missingAliases === []
                ? null
                : (string) __('capell-bookings::package.health.morph_map.remediation'),
        );
    }

    public function actionsCheck(): DoctorCheckResultData
    {
        $unresolvableActions = $this->unresolvableActions();

        return new DoctorCheckResultData(
            label: (string) __('capell-bookings::package.health.actions.label'),
            passed: $unresolvableActions === [],
            message: $unresolvableActions === []
                ? (string) __('capell-bookings::package.health.actions.passed', ['count' => count(self::ACTIONS)])
                : (string) __('capell-bookings::package.health.actions.failed', ['actions' => implode(', ', $unresolvableActions)]),
            remediation: $unresolvableActions === []
                ? null
                : (string) __('capell-bookings::package.health.actions.remediation'),
        );
    }

    public function publicRoutesCheck(): DoctorCheckResultData
    {
        $missingRoutes = array_values(array_filter(
            [
                'capell-bookings.request',
                'capell-bookings.request.store',
                'capell-bookings.calendar.staff',
                'capell-bookings.portal.lessons',
                'capell-bookings.portal.consent',
                'capell-bookings.portal.proposal',
                'capell-bookings.portal.review',
                'capell-bookings.portal.review-participant',
                'capell-bookings.webhook.store',
            ],
            static fn (string $routeName): bool => ! Route::has($routeName),
        ));

        return new DoctorCheckResultData(
            label: (string) __('capell-bookings::package.health.public_routes.label'),
            passed: $missingRoutes === [],
            message: $missingRoutes === []
                ? (string) __('capell-bookings::package.health.public_routes.passed')
                : (string) __('capell-bookings::package.health.public_routes.failed', ['routes' => implode(', ', $missingRoutes)]),
            remediation: $missingRoutes === []
                ? null
                : (string) __('capell-bookings::package.health.public_routes.remediation'),
        );
    }

    public function publicRendererCheck(): DoctorCheckResultData
    {
        $renderer = app(PublicBookingRequestRenderer::class);
        $passed = $renderer instanceof PublicBookingRequestRenderer;

        return new DoctorCheckResultData(
            label: (string) __('capell-bookings::package.health.public_renderer.label'),
            passed: $passed,
            message: $passed
                ? (string) __('capell-bookings::package.health.public_renderer.passed')
                : (string) __('capell-bookings::package.health.public_renderer.failed'),
            remediation: $passed
                ? null
                : (string) __('capell-bookings::package.health.public_renderer.remediation'),
        );
    }

    public function scheduledCommandsCheck(): DoctorCheckResultData
    {
        $missingSchedules = $this->missingScheduledCommands();

        return new DoctorCheckResultData(
            label: (string) __('capell-bookings::package.health.scheduled_commands.label'),
            passed: $missingSchedules === [],
            message: $missingSchedules === []
                ? (string) __('capell-bookings::package.health.scheduled_commands.passed')
                : (string) __('capell-bookings::package.health.scheduled_commands.failed', ['commands' => implode(', ', $missingSchedules)]),
            remediation: $missingSchedules === []
                ? null
                : (string) __('capell-bookings::package.health.scheduled_commands.remediation'),
        );
    }

    public function settingsMigrationCheck(): DoctorCheckResultData
    {
        $settingsRegistered = in_array(BookingsSettings::class, config('settings.settings', []), true);
        $migrationExists = File::exists(dirname(__DIR__, 2) . '/database/settings/2026_06_13_000001_create_bookings_settings.php');
        $passed = $settingsRegistered && $migrationExists;

        return new DoctorCheckResultData(
            label: (string) __('capell-bookings::package.health.settings_migration.label'),
            passed: $passed,
            message: $passed
                ? (string) __('capell-bookings::package.health.settings_migration.passed')
                : (string) __('capell-bookings::package.health.settings_migration.failed'),
            remediation: $passed
                ? null
                : (string) __('capell-bookings::package.health.settings_migration.remediation'),
        );
    }

    public function consoleCommandsCheck(): DoctorCheckResultData
    {
        $missingCommands = array_values(array_filter(
            self::COMMANDS,
            static fn (string $command): bool => ! array_key_exists($command, Artisan::all()),
        ));

        return new DoctorCheckResultData(
            label: (string) __('capell-bookings::package.health.console_commands.label'),
            passed: $missingCommands === [],
            message: $missingCommands === []
                ? (string) __('capell-bookings::package.health.console_commands.passed')
                : (string) __('capell-bookings::package.health.console_commands.failed', ['commands' => implode(', ', $missingCommands)]),
            remediation: $missingCommands === []
                ? null
                : (string) __('capell-bookings::package.health.console_commands.remediation'),
        );
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

    /**
     * @return list<string>
     */
    private function missingScheduledCommands(): array
    {
        $events = app(Schedule::class)->events();

        return array_values(array_filter(
            array_keys(self::SCHEDULE_EXPRESSIONS),
            static function (string $command) use ($events): bool {
                foreach ($events as $event) {
                    if (
                        is_string($event->command)
                        && str_contains($event->command, $command)
                        && $event->getExpression() === self::SCHEDULE_EXPRESSIONS[$command]
                    ) {
                        return false;
                    }
                }

                return true;
            },
        ));
    }
}
