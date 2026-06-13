<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Http\Controllers;

use Capell\EquestrianClinics\Actions\BuildClinicDiscoveryAction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class ShowClinicDiscoveryController
{
    public function __invoke(Request $request): View
    {
        return view('capell-equestrian-clinics::discovery', [
            'filters' => [
                'search' => $request->string('search')->toString(),
                'postcode' => $request->string('postcode')->toString(),
                'venue_id' => $request->string('venue_id')->toString(),
                'latitude' => $request->string('latitude')->toString(),
                'longitude' => $request->string('longitude')->toString(),
            ],
            'postUrl' => route('capell-equestrian-clinics.host-request.store'),
            ...BuildClinicDiscoveryAction::run($request->only(['search', 'postcode', 'venue_id', 'latitude', 'longitude'])),
        ]);
    }
}
