<?php

declare(strict_types=1);

namespace Capell\Bookings\Http\Controllers;

use Capell\Bookings\Actions\BuildStaffCalendarFeedAction;
use Capell\Bookings\Models\BookingStaffMember;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ShowStaffCalendarFeedController
{
    public function __invoke(string $token, BuildStaffCalendarFeedAction $buildStaffCalendarFeed): Response
    {
        /** @var BookingStaffMember|null $staffMember */
        $staffMember = BookingStaffMember::query()
            ->where('calendar_feed_token', $token)
            ->first();

        throw_if($staffMember === null, NotFoundHttpException::class);

        return response($buildStaffCalendarFeed->handle($staffMember), Response::HTTP_OK, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Cache-Control' => 'private, no-store',
            'X-Robots-Tag' => 'noindex, nofollow',
        ]);
    }
}
