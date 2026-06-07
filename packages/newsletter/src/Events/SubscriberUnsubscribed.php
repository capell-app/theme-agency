<?php

declare(strict_types=1);

namespace Capell\Newsletter\Events;

use Capell\Newsletter\Models\Subscriber;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class SubscriberUnsubscribed
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public Subscriber $subscriber) {}
}
