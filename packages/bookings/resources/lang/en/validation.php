<?php

declare(strict_types=1);

return [
    'appointment_end_after_start' => 'The appointment end time must be after the start time.',
    'appointment_after_max_future_days' => 'The requested appointment time is too far in the future.',
    'appointment_before_lead_time' => 'The requested appointment time is too soon.',
    'appointment_blocked_by_availability_exception' => 'The requested appointment time is unavailable because of a schedule exception.',
    'appointment_capacity_exceeded' => 'The requested appointment time is already fully booked.',
    'appointment_not_cancellable' => 'This appointment request cannot be cancelled.',
    'appointment_not_confirmable' => 'This appointment request cannot be confirmed.',
    'appointment_outside_availability' => 'The requested appointment time is outside the available schedule.',
    'availability_exception_capacity_required' => 'Available schedule exceptions must set a positive capacity.',
    'availability_exception_end_after_start' => 'The exception end time must be after the start time.',
    'location_unavailable' => 'The selected booking location is unavailable.',
    'service_unavailable' => 'The selected booking service is unavailable.',
    'staff_member_unavailable' => 'The selected staff member is unavailable.',
];
