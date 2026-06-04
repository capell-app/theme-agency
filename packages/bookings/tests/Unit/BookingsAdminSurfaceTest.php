<?php

declare(strict_types=1);

require_once __DIR__ . '/../Pest.php';

use Capell\Bookings\Enums\ResourceEnum;
use Capell\Bookings\Filament\Resources\AppointmentRequests\AppointmentRequestResource;
use Capell\Bookings\Filament\Resources\AppointmentRequests\Pages\EditAppointmentRequest;
use Capell\Bookings\Filament\Resources\AppointmentRequests\RelationManagers\AppointmentAuditLogsRelationManager;
use Capell\Bookings\Filament\Resources\BookingAvailabilityExceptions\BookingAvailabilityExceptionResource;
use Capell\Bookings\Filament\Resources\BookingAvailabilityWindows\BookingAvailabilityWindowResource;
use Capell\Bookings\Filament\Resources\BookingLocations\BookingLocationResource;
use Capell\Bookings\Filament\Resources\BookingServices\BookingServiceResource;
use Capell\Bookings\Filament\Resources\BookingStaffMembers\BookingStaffMemberResource;
use Capell\Bookings\Models\AppointmentRequest;
use Capell\Bookings\Models\BookingAvailabilityException;
use Capell\Bookings\Models\BookingAvailabilityWindow;
use Capell\Bookings\Models\BookingLocation;
use Capell\Bookings\Models\BookingService;
use Capell\Bookings\Models\BookingStaffMember;
use Filament\Actions\ActionGroup;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

it('declares admin resources for all bookings operator records', function (): void {
    expect(ResourceEnum::cases())->toHaveCount(6)
        ->and(BookingServiceResource::getModel())->toBe(BookingService::class)
        ->and(BookingStaffMemberResource::getModel())->toBe(BookingStaffMember::class)
        ->and(BookingLocationResource::getModel())->toBe(BookingLocation::class)
        ->and(BookingAvailabilityWindowResource::getModel())->toBe(BookingAvailabilityWindow::class)
        ->and(BookingAvailabilityExceptionResource::getModel())->toBe(BookingAvailabilityException::class)
        ->and(AppointmentRequestResource::getModel())->toBe(AppointmentRequest::class)
        ->and(BookingServiceResource::getNavigationLabel())->toBe(__('capell-bookings::admin.resources.services'))
        ->and(BookingStaffMemberResource::getNavigationLabel())->toBe(__('capell-bookings::admin.resources.staff_members'))
        ->and(BookingLocationResource::getNavigationLabel())->toBe(__('capell-bookings::admin.resources.locations'))
        ->and(BookingAvailabilityWindowResource::getNavigationLabel())->toBe(__('capell-bookings::admin.resources.availability_windows'))
        ->and(BookingAvailabilityExceptionResource::getNavigationLabel())->toBe(__('capell-bookings::admin.resources.availability_exceptions'))
        ->and(AppointmentRequestResource::getNavigationLabel())->toBe(__('capell-bookings::admin.resources.appointment_requests'));
});

it('exposes list create edit pages for mutable booking setup resources', function (): void {
    expect(array_keys(BookingServiceResource::getPages()))->toBe(['index', 'create', 'edit'])
        ->and(array_keys(BookingStaffMemberResource::getPages()))->toBe(['index', 'create', 'edit'])
        ->and(array_keys(BookingLocationResource::getPages()))->toBe(['index', 'create', 'edit'])
        ->and(array_keys(BookingAvailabilityWindowResource::getPages()))->toBe(['index', 'create', 'edit'])
        ->and(array_keys(BookingAvailabilityExceptionResource::getPages()))->toBe(['index', 'create', 'edit'])
        ->and(array_keys(AppointmentRequestResource::getPages()))->toBe(['index', 'edit']);
});

it('exposes appointment request workflow actions and audit log relation', function (): void {
    $table = AppointmentRequestResource::table(bookingsAdminTableForCoverage());
    $page = new EditAppointmentRequest;

    expect(bookingsAdminActionNames($table->getRecordActions()))->toContain('confirm', 'cancel')
        ->and(bookingsAdminActionNames(bookingsAdminEditAppointmentHeaderActions($page)))->toBe(['confirm', 'cancel'])
        ->and(AppointmentRequestResource::getRelations())->toBe([AppointmentAuditLogsRelationManager::class])
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
