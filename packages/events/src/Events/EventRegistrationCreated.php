<?php

declare(strict_types=1);

namespace Capell\Events\Events;

use Capell\Events\Models\EventRegistration;
use Illuminate\Foundation\Events\Dispatchable;

final class EventRegistrationCreated
{
    use Dispatchable;

    public function __construct(
        public readonly EventRegistration $registration,
    ) {}
}
