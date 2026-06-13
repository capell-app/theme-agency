<?php

declare(strict_types=1);

namespace Capell\Bookings\Enums;

use Capell\Bookings\Filament\Resources\AppointmentRequests\AppointmentRequestResource;
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

enum ResourceEnum: string
{
    case BookingService = BookingServiceResource::class;
    case BookingStaffMember = BookingStaffMemberResource::class;
    case BookingLocation = BookingLocationResource::class;
    case BookingAvailabilityWindow = BookingAvailabilityWindowResource::class;
    case BookingAvailabilityException = BookingAvailabilityExceptionResource::class;
    case LessonSeries = LessonSeriesResource::class;
    case AppointmentRequest = AppointmentRequestResource::class;
    case BookingDayPlanner = BookingDayPlannerResource::class;
    case BookingGroupSession = BookingGroupSessionResource::class;
    case BookingMessageLog = BookingMessageLogResource::class;
    case BookingReviewRequest = BookingReviewRequestResource::class;
    case BookingTravelObservation = BookingTravelObservationResource::class;
    case BookingWorkZone = BookingWorkZoneResource::class;
    case BookingOwnerPrompt = BookingOwnerPromptResource::class;
    case BookingChangeProposal = BookingChangeProposalResource::class;
}
