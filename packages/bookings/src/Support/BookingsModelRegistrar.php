<?php

declare(strict_types=1);

namespace Capell\Bookings\Support;

use Capell\Bookings\Models\AppointmentAuditLog;
use Capell\Bookings\Models\AppointmentRequest;
use Capell\Bookings\Models\BookingAvailabilityException;
use Capell\Bookings\Models\BookingAvailabilityWindow;
use Capell\Bookings\Models\BookingLocation;
use Capell\Bookings\Models\BookingService;
use Capell\Bookings\Models\BookingStaffMember;
use Capell\Bookings\Models\LessonSeries;
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
    ];

    public static function register(): void
    {
        CapellCore::registerModels(self::MODELS);

        /** @var array<string, class-string<Model>> $morphMap */
        $morphMap = collect(self::MODELS)
            ->mapWithKeys(fn (string $modelClass): array => [Str::snake(class_basename($modelClass)) => $modelClass])
            ->all();

        Relation::morphMap($morphMap, merge: true);
    }
}
