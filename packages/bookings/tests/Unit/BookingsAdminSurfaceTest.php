<?php

declare(strict_types=1);

require_once __DIR__ . '/../Pest.php';

use Capell\Bookings\Enums\ResourceEnum;
use Capell\Bookings\Filament\Resources\AppointmentRequests\AppointmentRequestResource;
use Capell\Bookings\Filament\Resources\AppointmentRequests\Pages\EditAppointmentRequest;
use Capell\Bookings\Filament\Resources\AppointmentRequests\RelationManagers\AppointmentAuditLogsRelationManager;
use Capell\Bookings\Filament\Resources\AppointmentRequests\RelationManagers\LessonNotesRelationManager;
use Capell\Bookings\Filament\Resources\BookingAvailabilityExceptions\BookingAvailabilityExceptionResource;
use Capell\Bookings\Filament\Resources\BookingAvailabilityWindows\BookingAvailabilityWindowResource;
use Capell\Bookings\Filament\Resources\BookingChangeProposals\BookingChangeProposalResource;
use Capell\Bookings\Filament\Resources\BookingDayPlanner\BookingDayPlannerResource;
use Capell\Bookings\Filament\Resources\BookingGroupSessions\BookingGroupSessionResource;
use Capell\Bookings\Filament\Resources\BookingLocations\BookingLocationResource;
use Capell\Bookings\Filament\Resources\BookingMessageLogs\BookingMessageLogResource;
use Capell\Bookings\Filament\Resources\BookingOwnerPrompts\BookingOwnerPromptResource;
use Capell\Bookings\Filament\Resources\BookingReviewRequests\BookingReviewRequestResource;
use Capell\Bookings\Filament\Resources\BookingServices\BookingServiceResource;
use Capell\Bookings\Filament\Resources\BookingStaffMembers\BookingStaffMemberResource;
use Capell\Bookings\Filament\Resources\BookingTravelObservations\BookingTravelObservationResource;
use Capell\Bookings\Filament\Resources\BookingWorkZones\BookingWorkZoneResource;
use Capell\Bookings\Filament\Resources\LessonSeries\LessonSeriesResource;
use Capell\Bookings\Models\AppointmentRequest;
use Capell\Bookings\Models\BookingAvailabilityException;
use Capell\Bookings\Models\BookingAvailabilityWindow;
use Capell\Bookings\Models\BookingChangeProposal;
use Capell\Bookings\Models\BookingGroupSession;
use Capell\Bookings\Models\BookingLocation;
use Capell\Bookings\Models\BookingMessageLog;
use Capell\Bookings\Models\BookingOwnerPrompt;
use Capell\Bookings\Models\BookingReviewRequest;
use Capell\Bookings\Models\BookingService;
use Capell\Bookings\Models\BookingStaffMember;
use Capell\Bookings\Models\BookingTravelObservation;
use Capell\Bookings\Models\BookingWorkZone;
use Capell\Bookings\Models\LessonSeries;
use Filament\Actions\ActionGroup;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

it('declares admin resources for all bookings operator records', function (): void {
    expect(ResourceEnum::cases())->toHaveCount(15)
        ->and(BookingServiceResource::getModel())->toBe(BookingService::class)
        ->and(BookingStaffMemberResource::getModel())->toBe(BookingStaffMember::class)
        ->and(BookingLocationResource::getModel())->toBe(BookingLocation::class)
        ->and(BookingAvailabilityWindowResource::getModel())->toBe(BookingAvailabilityWindow::class)
        ->and(BookingAvailabilityExceptionResource::getModel())->toBe(BookingAvailabilityException::class)
        ->and(LessonSeriesResource::getModel())->toBe(LessonSeries::class)
        ->and(AppointmentRequestResource::getModel())->toBe(AppointmentRequest::class)
        ->and(BookingDayPlannerResource::getModel())->toBe(AppointmentRequest::class)
        ->and(BookingGroupSessionResource::getModel())->toBe(BookingGroupSession::class)
        ->and(BookingMessageLogResource::getModel())->toBe(BookingMessageLog::class)
        ->and(BookingReviewRequestResource::getModel())->toBe(BookingReviewRequest::class)
        ->and(BookingTravelObservationResource::getModel())->toBe(BookingTravelObservation::class)
        ->and(BookingWorkZoneResource::getModel())->toBe(BookingWorkZone::class)
        ->and(BookingOwnerPromptResource::getModel())->toBe(BookingOwnerPrompt::class)
        ->and(BookingChangeProposalResource::getModel())->toBe(BookingChangeProposal::class)
        ->and(BookingServiceResource::getNavigationLabel())->toBe(__('capell-bookings::admin.resources.services'))
        ->and(BookingStaffMemberResource::getNavigationLabel())->toBe(__('capell-bookings::admin.resources.staff_members'))
        ->and(BookingLocationResource::getNavigationLabel())->toBe(__('capell-bookings::admin.resources.locations'))
        ->and(BookingAvailabilityWindowResource::getNavigationLabel())->toBe(__('capell-bookings::admin.resources.availability_windows'))
        ->and(BookingAvailabilityExceptionResource::getNavigationLabel())->toBe(__('capell-bookings::admin.resources.availability_exceptions'))
        ->and(LessonSeriesResource::getNavigationLabel())->toBe(__('capell-bookings::admin.resources.lesson_series'))
        ->and(AppointmentRequestResource::getNavigationLabel())->toBe(__('capell-bookings::admin.resources.appointment_requests'))
        ->and(BookingDayPlannerResource::getNavigationLabel())->toBe(__('capell-bookings::admin.resources.day_planner'))
        ->and(BookingGroupSessionResource::getNavigationLabel())->toBe(__('capell-bookings::admin.resources.group_sessions'))
        ->and(BookingMessageLogResource::getNavigationLabel())->toBe(__('capell-bookings::admin.resources.message_logs'))
        ->and(BookingReviewRequestResource::getNavigationLabel())->toBe(__('capell-bookings::admin.resources.review_requests'))
        ->and(BookingTravelObservationResource::getNavigationLabel())->toBe(__('capell-bookings::admin.resources.travel_observations'))
        ->and(BookingWorkZoneResource::getNavigationLabel())->toBe(__('capell-bookings::admin.resources.work_zones'))
        ->and(BookingOwnerPromptResource::getNavigationLabel())->toBe(__('capell-bookings::admin.resources.owner_prompts'))
        ->and(BookingChangeProposalResource::getNavigationLabel())->toBe(__('capell-bookings::admin.resources.change_proposals'));
});

it('exposes list create edit pages for mutable booking setup resources', function (): void {
    expect(array_keys(BookingServiceResource::getPages()))->toBe(['index', 'create', 'edit'])
        ->and(array_keys(BookingStaffMemberResource::getPages()))->toBe(['index', 'create', 'edit'])
        ->and(array_keys(BookingLocationResource::getPages()))->toBe(['index', 'create', 'edit'])
        ->and(array_keys(BookingAvailabilityWindowResource::getPages()))->toBe(['index', 'create', 'edit'])
        ->and(array_keys(BookingAvailabilityExceptionResource::getPages()))->toBe(['index', 'create', 'edit'])
        ->and(array_keys(LessonSeriesResource::getPages()))->toBe(['index', 'create', 'edit'])
        ->and(array_keys(AppointmentRequestResource::getPages()))->toBe(['index', 'edit'])
        ->and(array_keys(BookingGroupSessionResource::getPages()))->toBe(['index', 'create', 'edit'])
        ->and(array_keys(BookingWorkZoneResource::getPages()))->toBe(['index', 'create', 'edit'])
        ->and(array_keys(BookingOwnerPromptResource::getPages()))->toBe(['index', 'edit'])
        ->and(array_keys(BookingDayPlannerResource::getPages()))->toBe(['index'])
        ->and(array_keys(BookingMessageLogResource::getPages()))->toBe(['index'])
        ->and(array_keys(BookingReviewRequestResource::getPages()))->toBe(['index'])
        ->and(array_keys(BookingTravelObservationResource::getPages()))->toBe(['index'])
        ->and(array_keys(BookingChangeProposalResource::getPages()))->toBe(['index']);
});

it('exposes appointment request workflow actions and audit log relation', function (): void {
    $table = AppointmentRequestResource::table(bookingsAdminTableForCoverage());
    $page = new EditAppointmentRequest;

    expect(bookingsAdminActionNames($table->getRecordActions()))->toContain('confirm', 'cancel')
        ->and(bookingsAdminActionNames(bookingsAdminEditAppointmentHeaderActions($page)))->toBe(['confirm', 'cancel'])
        ->and(AppointmentRequestResource::getRelations())->toBe([LessonNotesRelationManager::class, AppointmentAuditLogsRelationManager::class])
        ->and(LessonNotesRelationManager::getRelationshipName())->toBe('lessonNotes')
        ->and(AppointmentAuditLogsRelationManager::getRelationshipName())->toBe('auditLogs');
});

function bookingsAdminTableForCoverage(): Table
{
    $livewire = Mockery::mock(HasTable::class);
    $livewire->shouldIgnoreMissing();
    $livewire->shouldReceive('makeFilamentTranslatableContentDriver')->andReturn(null)->byDefault();
    $livewire->shouldReceive('getTableFilterState')->andReturn([])->byDefault();
    $livewire->shouldReceive('isTableLoaded')->andReturnTrue()->byDefault();
    $livewire->shouldReceive('getTableArguments')->andReturn([])->byDefault();

    return Table::make($livewire);
}

/**
 * @param  array<array-key, mixed>  $actions
 * @return array<int, string>
 */
function bookingsAdminActionNames(array $actions): array
{
    return collect($actions)
        ->flatMap(fn (mixed $action): array => bookingsAdminFlattenActionNames($action))
        ->values()
        ->all();
}

/**
 * @return array<int, string>
 */
function bookingsAdminFlattenActionNames(mixed $action): array
{
    if ($action instanceof ActionGroup) {
        return bookingsAdminActionNames($action->getActions());
    }

    if (is_object($action) && method_exists($action, 'getName')) {
        return [(string) $action->getName()];
    }

    return [];
}

/**
 * @return array<array-key, mixed>
 */
function bookingsAdminEditAppointmentHeaderActions(EditAppointmentRequest $page): array
{
    $method = new ReflectionMethod(EditAppointmentRequest::class, 'getHeaderActions');

    return $method->invoke($page);
}
