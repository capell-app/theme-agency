<?php

declare(strict_types=1);

return [
    'method_fee_legal_acknowledgement_required' => 'Method-specific payment fees are guarded. Confirm legal approval before enabling a Stripe or PayPal fee.',
    'rider_skill_tier_mismatch' => 'This rider does not meet the skill tier required for this slot.',
    'horse_skill_tier_mismatch' => 'This horse is not suitable for the skill tier required for this slot.',
    'horse_workload_limit_exceeded' => 'This horse would exceed its configured daily workload limit.',
    'facility_capacity_exceeded' => 'This facility resource is not available in the requested quantity for that time.',
    'cash_payment_requires_approval' => 'Cash payment is only available after this rider has been approved by the coach.',
    'checkout_handoff_not_available' => 'Checkout can only be started for an active online payment hold.',
    'checkout_url_invalid' => 'Checkout success and cancellation URLs must be absolute HTTP URLs.',
    'payment_provider_required' => 'Choose Stripe or PayPal before placing this booking hold.',
    'slot_capacity_exceeded' => 'This slot no longer has enough remaining capacity.',
    'booking_window_closed' => 'Online booking is closed for this tour day.',
    'booking_not_confirmable' => 'Only provisional booking holds can be confirmed by payment.',
    'booking_hold_expired' => 'This booking hold has expired. Please start checkout again.',
    'booking_not_cancellable' => 'This booking can no longer be cancelled through the automated workflow.',
    'waitlist_requires_full_slot' => 'Waitlist entries are only available once the slot is full.',
    'waitlist_empty' => 'There are no waiting riders to promote for this slot.',
    'waitlist_offer_not_claimable' => 'This waitlist offer is not currently claimable.',
];
