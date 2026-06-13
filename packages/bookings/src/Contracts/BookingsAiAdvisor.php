<?php

declare(strict_types=1);

namespace Capell\Bookings\Contracts;

interface BookingsAiAdvisor
{
    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function advise(string $task, array $context): array;
}
