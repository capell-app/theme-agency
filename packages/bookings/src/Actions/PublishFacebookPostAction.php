<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Enums\BookingOwnerPromptStatusEnum;
use Capell\Bookings\Models\BookingOwnerPrompt;
use Carbon\CarbonImmutable;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static BookingOwnerPrompt run(BookingOwnerPrompt $prompt)
 */
class PublishFacebookPostAction
{
    use AsAction;

    public function handle(BookingOwnerPrompt $prompt): BookingOwnerPrompt
    {
        $prompt->forceFill([
            'status' => BookingOwnerPromptStatusEnum::Applied,
            'meta' => [
                ...($prompt->meta ?? []),
                'published_at' => CarbonImmutable::now()->toIso8601String(),
            ],
        ])->save();

        return $prompt->refresh();
    }
}
