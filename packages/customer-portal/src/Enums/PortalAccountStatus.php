<?php

declare(strict_types=1);

namespace Capell\CustomerPortal\Enums;

enum PortalAccountStatus: string
{
    case Active = 'active';
    case Suspended = 'suspended';
    case Archived = 'archived';

    public function getLabel(): string
    {
        return __('capell-customer-portal::generic.account_status.' . $this->value);
    }
}
