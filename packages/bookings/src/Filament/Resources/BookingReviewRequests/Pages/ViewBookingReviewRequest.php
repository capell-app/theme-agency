<?php

declare(strict_types=1);

namespace Capell\Bookings\Filament\Resources\BookingReviewRequests\Pages;

use Capell\Bookings\Filament\Resources\BookingReviewRequests\BookingReviewRequestResource;
use Filament\Resources\Pages\ViewRecord;

final class ViewBookingReviewRequest extends ViewRecord
{
    protected static string $resource = BookingReviewRequestResource::class;
}
