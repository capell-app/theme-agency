<?php

declare(strict_types=1);

return [
    'confirmation' => [
        'subject' => 'Your appointment is confirmed',
        'greeting' => 'Appointment confirmed',
        'body' => 'Your appointment request has been confirmed.',
    ],
    'cancellation' => [
        'subject' => 'Your appointment has been cancelled',
        'greeting' => 'Appointment cancelled',
        'body' => 'Your appointment has been cancelled.',
    ],
    'reminder' => [
        'subject' => 'Appointment reminder',
        'greeting' => 'Appointment reminder',
        'body' => 'This is a reminder for your upcoming appointment.',
    ],
    'service' => 'Service: :service',
    'starts_at' => 'Starts: :starts_at',
    'timezone' => 'Timezone: :timezone',
];
