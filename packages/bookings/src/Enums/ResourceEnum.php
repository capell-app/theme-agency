<?php

declare(strict_types=1);

namespace Capell\Bookings\Enums;

use Capell\Bookings\Filament\Resources\AppointmentRequests\AppointmentRequestResource;
use Capell\Bookings\Filament\Resources\BookingAvailabilityExceptions\BookingAvailabilityExceptionResource;
use Capell\Bookings\Filament\Resources\BookingAvailabilityWindows\BookingAvailabilityWindowResource;
use Capell\Bookings\Filament\Resources\BookingLocations\BookingLocationResource;
use Capell\Bookings\Filament\Resources\BookingServices\BookingServiceResource;
use Capell\Bookings\Filament\Resources\BookingStaffMembers\BookingStaffMemberResource;

enum ResourceEnum: string
{
    case BookingService = BookingServiceResource::class;
    case BookingStaffMember = BookingStaffMemberResource::class;
    case BookingLocation = BookingLocationResource::class;
    case BookingAvailabilityWindow = BookingAvailabilityWindowResource::class;
    case BookingAvailabilityException = BookingAvailabilityExceptionResource::class;
    case AppointmentRequest = AppointmentRequestResource::class;
}
