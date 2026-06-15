<?php

declare(strict_types=1);

namespace Capell\Bookings\Support;

use Capell\Bookings\Contracts\BookingsAiAdvisor;

final class LocalBookingsAiAdvisor implements BookingsAiAdvisor
{
    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function advise(string $task, array $context): array
    {
        return [
            'task' => $task,
            'title' => __('capell-bookings::ai.local_prompt_title'),
            'body' => __('capell-bookings::ai.local_prompt_body'),
            'confidence' => 50,
            'context' => $context,
        ];
    }
}
