<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Contracts\BookingsAiAdvisor;
use Capell\Bookings\Enums\BookingOwnerPromptStatusEnum;
use Capell\Bookings\Models\BookingOwnerPrompt;
use Carbon\CarbonImmutable;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static BookingOwnerPrompt run(?int $siteId = null, ?CarbonImmutable $from = null, ?CarbonImmutable $to = null)
 */
class ProposeScheduleOptimisationAction
{
    use AsAction;

    public function handle(?int $siteId = null, ?CarbonImmutable $from = null, ?CarbonImmutable $to = null): BookingOwnerPrompt
    {
        $digest = BuildOwnerDigestAction::run($siteId, $from, $to);
        $advice = app(BookingsAiAdvisor::class)->advise('schedule_optimisation', $digest);
        $title = $advice['title'] ?? null;
        $body = $advice['body'] ?? null;

        /** @var BookingOwnerPrompt $prompt */
        $prompt = BookingOwnerPrompt::query()->create([
            'site_id' => $siteId,
            'type' => 'schedule_optimisation',
            'status' => BookingOwnerPromptStatusEnum::Proposed,
            'title' => is_string($title) ? $title : __('capell-bookings::ai.schedule_prompt_title'),
            'body' => is_string($body) ? $body : __('capell-bookings::ai.schedule_prompt_body'),
            'context' => $digest,
            'meta' => $advice,
        ]);

        return $prompt;
    }
}
