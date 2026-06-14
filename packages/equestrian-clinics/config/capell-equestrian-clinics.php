<?php

declare(strict_types=1);

use Illuminate\Support\Env;

$bookingFeePence = Env::get('CAPELL_EQUESTRIAN_BOOKING_FEE_PENCE', 0);

return [
    'public_path_prefix' => 'equestrian-clinics',
    'booking_lock_hours' => 24,
    'cancellation_refund_hours' => 48,
    'checkout_hold_minutes' => 10,
    'waitlist_claim_minutes' => 120,
    'universal_booking_fee_pence' => is_numeric($bookingFeePence) ? (int) $bookingFeePence : 0,
    'method_fee_legal_acknowledgement_required' => true,
    'modules' => [
        'yard_erp' => true,
        'host_portal' => true,
        'ai_assistant' => true,
    ],
];
