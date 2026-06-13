<?php

declare(strict_types=1);

use Capell\Bookings\Http\Controllers\RespondBookingChangeProposalController;
use Capell\Bookings\Http\Controllers\ShowBookingChangeProposalController;
use Capell\Bookings\Http\Controllers\ShowBookingRequestController;
use Capell\Bookings\Http\Controllers\ShowMessagingConsentController;
use Capell\Bookings\Http\Controllers\ShowPortalLessonsController;
use Capell\Bookings\Http\Controllers\ShowReviewParticipantController;
use Capell\Bookings\Http\Controllers\ShowReviewRequestController;
use Capell\Bookings\Http\Controllers\ShowStaffCalendarFeedController;
use Capell\Bookings\Http\Controllers\StoreBookingRequestController;
use Capell\Bookings\Http\Controllers\StoreReviewParticipantController;
use Capell\Bookings\Http\Controllers\StoreReviewRequestController;
use Capell\Bookings\Http\Controllers\UpdateMessagingConsentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])
    ->prefix(trim((string) config('capell-bookings.public_path_prefix', 'bookings'), '/'))
    ->as('capell-bookings.')
    ->group(function (): void {
        Route::get('/', ShowBookingRequestController::class)->name('request');
        Route::post('/', StoreBookingRequestController::class)
            ->middleware('throttle:capell-bookings-request')
            ->name('request.store');
        Route::get('/calendar/staff/{token}.ics', ShowStaffCalendarFeedController::class)->name('calendar.staff');
        Route::get('/portal/{site}/{portalAccount}/lessons', ShowPortalLessonsController::class)->name('portal.lessons');
        Route::get('/portal/{site}/{portalAccount}/consent', ShowMessagingConsentController::class)->name('portal.consent');
        Route::post('/portal/{site}/{portalAccount}/consent', UpdateMessagingConsentController::class)->name('portal.consent.update');
        Route::get('/proposal/{proposalParty}/{token}', ShowBookingChangeProposalController::class)->name('portal.proposal');
        Route::post('/proposal/{proposalParty}/{token}', RespondBookingChangeProposalController::class)->name('portal.proposal.respond');
        Route::get('/review/{reviewRequest}', ShowReviewRequestController::class)->name('portal.review');
        Route::post('/review/{reviewRequest}', StoreReviewRequestController::class)->name('portal.review.store');
        Route::get('/review-participant/{reviewParticipant}/{token}', ShowReviewParticipantController::class)->name('portal.review-participant');
        Route::post('/review-participant/{reviewParticipant}/{token}', StoreReviewParticipantController::class)->name('portal.review-participant.store');
    });
