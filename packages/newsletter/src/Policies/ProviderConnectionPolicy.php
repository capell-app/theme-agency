<?php

declare(strict_types=1);

namespace Capell\Newsletter\Policies;

final class ProviderConnectionPolicy extends AbstractNewsletterResourcePolicy
{
    protected static function subject(): string
    {
        return 'ProviderConnection';
    }
}
