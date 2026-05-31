<?php

declare(strict_types=1);

namespace Capell\Newsletter\Policies;

final class FormMappingPolicy extends AbstractNewsletterResourcePolicy
{
    protected static function subject(): string
    {
        return 'FormMapping';
    }
}
