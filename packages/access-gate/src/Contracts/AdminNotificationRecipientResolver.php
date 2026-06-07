<?php

declare(strict_types=1);

namespace Capell\AccessGate\Contracts;

use Illuminate\Support\Collection;

interface AdminNotificationRecipientResolver
{
    /**
     * Resolve the recipients that should receive access gate admin notifications.
     *
     * @return Collection<int, mixed>
     */
    public function resolve(): Collection;
}
