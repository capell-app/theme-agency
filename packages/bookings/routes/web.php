<?php

declare(strict_types=1);

use Capell\Bookings\Http\Controllers\ShowBookingRequestController;
use Capell\Bookings\Http\Controllers\ShowStaffCalendarFeedController;
use Capell\Bookings\Http\Controllers\StoreBookingRequestController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])
    ->prefix(trim((string) config('capell-bookings.public_path_prefix', 'bookings'), '/'))
    ->as('capell-bookings.')
    ->group(function (): void {
        Route::get('/', ShowBookingRequestController::class)->name('request');
        Route::post('/', StoreBookingRequestController::class)->name('request.store');
        Route::get('/calendar/staff/{token}.ics', ShowStaffCalendarFeedController::class)->name('calendar.staff');
    });
