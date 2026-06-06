<?php

declare(strict_types=1);

namespace Capell\PasswordPolicy\Actions;

use Capell\Core\Facades\CapellCore;
use Capell\PasswordPolicy\Enums\PasswordPolicyLifecycleEvent;
use Lorisleiva\Actions\Concerns\AsObject;

final class NotifyPasswordPolicyLifecycleEventAction
{
    use AsObject;

    public function handle(PasswordPolicyLifecycleEvent $event, object $context): void
    {
        CapellCore::subscriberManager()->notifySubscribers($event, $context);
    }
}
