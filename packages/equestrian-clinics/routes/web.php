<?php

declare(strict_types=1);

use Capell\EquestrianClinics\Http\Controllers\ShowClinicDiscoveryController;
use Capell\EquestrianClinics\Http\Controllers\ShowCoachTimetableController;
use Capell\EquestrianClinics\Http\Controllers\StoreHostRequestController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])
    ->prefix(trim((string) config('capell-equestrian-clinics.public_path_prefix', 'equestrian-clinics'), '/'))
    ->as('capell-equestrian-clinics.')
    ->group(function (): void {
        Route::get('/', ShowClinicDiscoveryController::class)->name('discovery');
        Route::post('/host-request', StoreHostRequestController::class)
            ->middleware('throttle:capell-equestrian-clinics-host-request')
            ->name('host-request.store');
        Route::get('/coach/tour-days/{tourDay}/timetable', ShowCoachTimetableController::class)
            ->middleware('signed')
            ->name('coach.timetable');
    });
