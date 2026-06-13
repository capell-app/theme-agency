<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Http\Controllers;

use Capell\EquestrianClinics\Actions\BuildCoachTimetableAction;
use Capell\EquestrianClinics\Models\EquestrianTourDay;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

final class ShowCoachTimetableController
{
    public function __invoke(EquestrianTourDay $tourDay): SymfonyResponse
    {
        $response = response()->view('capell-equestrian-clinics::coach-timetable', [
            'timetable' => BuildCoachTimetableAction::run($tourDay),
        ]);

        $response->headers->set('Cache-Control', 'private, no-store');
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow');

        return $response;
    }
}
