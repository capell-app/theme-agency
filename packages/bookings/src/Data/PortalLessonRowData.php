<?php

declare(strict_types=1);

namespace Capell\Bookings\Data;

use Capell\Bookings\Enums\AppointmentRequestStatusEnum;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;

class PortalLessonRowData extends Data
{
    /**
     * @param  list<array{id:int,summary:string|null,body:string|null,photo_media_ids:list<int>}>  $sharedNotes
     */
    public function __construct(
        public int $appointmentRequestId,
        public string $serviceName,
        public CarbonImmutable $startsAt,
        public CarbonImmutable $endsAt,
        public AppointmentRequestStatusEnum $status,
        public array $sharedNotes,
    ) {}
}
