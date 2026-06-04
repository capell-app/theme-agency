<?php

declare(strict_types=1);

namespace Capell\Bookings\Health;

use Capell\Bookings\Actions\BuildAvailableBookingSlotsAction;
use Capell\Bookings\Actions\BuildPublicBookingRequestOptionsAction;
use Capell\Bookings\Actions\BuildPublicBookingRequestPropsAction;
use Capell\Bookings\Actions\BuildStaffCalendarFeedAction;
use Capell\Bookings\Actions\CancelAppointmentRequestAction;
use Capell\Bookings\Actions\ConfirmAppointmentRequestAction;
use Capell\Bookings\Actions\CreateAppointmentRequestAction;
use Capell\Bookings\Actions\QueueAppointmentReminderAction;
use Capell\Bookings\Models\AppointmentAuditLog;
use Capell\Bookings\Models\AppointmentRequest;
use Capell\Bookings\Models\BookingAvailabilityException;
use Capell\Bookings\Models\BookingAvailabilityWindow;
use Capell\Bookings\Models\BookingLocation;
use Capell\Bookings\Models\BookingService;
use Capell\Bookings\Models\BookingStaffMember;
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
        BuildStaffCalendarFeedAction::class,
        CancelAppointmentRequestAction::class,
        ConfirmAppointmentRequestAction::class,
        CreateAppointmentRequestAction::class,
        QueueAppointmentReminderAction::class,
    ];

    /** @var array<string, class-string> */
    private const array MODELS_BY_TABLE = [
        'booking_services' => BookingService::class,
        'booking_staff_members' => BookingStaffMember::class,
        'booking_locations' => BookingLocation::class,
        'booking_availability_windows' => BookingAvailabilityWindow::class,
        'booking_availability_exceptions' => BookingAvailabilityException::class,
        'appointment_requests' => AppointmentRequest::class,
        'appointment_audit_logs' => AppointmentAuditLog::class,
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
