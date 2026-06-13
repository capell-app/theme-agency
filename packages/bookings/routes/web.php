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
use Capell\Bookings\Http\Controllers\StoreBookingWebhookEventController;
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
        Route::get('/portal/{portalToken}/lessons', ShowPortalLessonsController::class)->name('portal.lessons');
        Route::get('/portal/{portalToken}/consent', ShowMessagingConsentController::class)->name('portal.consent');
        Route::post('/portal/{portalToken}/consent', UpdateMessagingConsentController::class)->name('portal.consent.update');
        Route::get('/proposal/{token}', ShowBookingChangeProposalController::class)->name('portal.proposal');
        Route::post('/proposal/{token}', RespondBookingChangeProposalController::class)->name('portal.proposal.respond');
        Route::get('/review/{token}', ShowReviewRequestController::class)->name('portal.review');
        Route::post('/review/{token}', StoreReviewRequestController::class)->name('portal.review.store');
        Route::get('/review-participant/{token}', ShowReviewParticipantController::class)->name('portal.review-participant');
        Route::post('/review-participant/{token}', StoreReviewParticipantController::class)->name('portal.review-participant.store');
        Route::post('/webhooks/{provider}', StoreBookingWebhookEventController::class)
            ->middleware('throttle:capell-bookings-webhook')
            ->name('webhook.store');
    });
