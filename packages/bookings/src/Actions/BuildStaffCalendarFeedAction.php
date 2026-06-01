<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Enums\AppointmentRequestStatusEnum;
use Capell\Bookings\Models\AppointmentRequest;
use Capell\Bookings\Models\BookingStaffMember;
use Carbon\CarbonImmutable;
use Lorisleiva\Actions\Concerns\AsAction;

class BuildStaffCalendarFeedAction
{
    use AsAction;

    /**
     * @return non-empty-string
     */
    public function handle(BookingStaffMember $staffMember): string
    {
        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Appointments//Calendar Feed//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'X-WR-CALNAME:' . $this->escapeText($staffMember->display_name . ' appointments'),
        ];

        AppointmentRequest::query()
            ->with(['service', 'location'])
            ->where('staff_member_id', $staffMember->getKey())
            ->where('status', AppointmentRequestStatusEnum::Confirmed->value)
            ->oldest('requested_starts_at')
            ->each(function (AppointmentRequest $appointmentRequest) use (&$lines): void {
                array_push($lines, ...$this->appointmentLines($appointmentRequest));
            });

        $lines[] = 'END:VCALENDAR';

        return implode("\r\n", $lines) . "\r\n";
    }

    /**
     * @return list<string>
     */
    private function appointmentLines(AppointmentRequest $appointmentRequest): array
    {
        $summary = $appointmentRequest->service?->name ?? __('capell-bookings::generic.appointment');
        $description = trim($appointmentRequest->customer_name . "\n" . $appointmentRequest->notes);
        $location = $appointmentRequest->location?->name;

        $lines = [
            'BEGIN:VEVENT',
            'UID:' . $this->escapeText($appointmentRequest->calendar_uid),
            'DTSTAMP:' . $this->formatTimestamp(CarbonImmutable::now('UTC')),
            'DTSTART:' . $this->formatTimestamp($appointmentRequest->requested_starts_at),
            'DTEND:' . $this->formatTimestamp($appointmentRequest->requested_ends_at),
            'SUMMARY:' . $this->escapeText($summary),
            'DESCRIPTION:' . $this->escapeText($description),
            'STATUS:CONFIRMED',
        ];

        if (is_string($location) && $location !== '') {
            $lines[] = 'LOCATION:' . $this->escapeText($location);
        }

        $lines[] = 'END:VEVENT';

        return $lines;
    }

    private function formatTimestamp(CarbonImmutable $date): string
    {
        return $date->setTimezone('UTC')->format('Ymd\THis\Z');
    }

    private function escapeText(string $value): string
    {
        return str_replace(
            ['\\', "\r\n", "\r", "\n", ';', ','],
            ['\\\\', '\\n', '\\n', '\\n', '\\;', '\\,'],
            $value,
        );
    }
}
