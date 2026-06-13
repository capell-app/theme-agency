<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Http\Controllers;

use Capell\EquestrianClinics\Actions\RecordHostRequestAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class StoreHostRequestController
{
    public function __invoke(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'requester_name' => ['required', 'string', 'max:120'],
            'requester_email' => ['required', 'email', 'max:180'],
            'requester_phone' => ['nullable', 'string', 'max:80'],
            'venue_name' => ['nullable', 'string', 'max:160'],
            'postal_code' => ['nullable', 'string', 'max:32'],
            'preferred_region' => ['nullable', 'string', 'max:120'],
            'lesson_type' => ['nullable', 'string', 'max:120'],
            'skill_tier' => ['nullable', 'string', 'max:80'],
            'expected_riders' => ['nullable', 'integer', 'min:1', 'max:200'],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        RecordHostRequestAction::run($payload);

        return back()->with('equestrian_host_request_status', __('capell-equestrian-clinics::package.frontend.host_request_received'));
    }
}
