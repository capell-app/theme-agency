<?php

declare(strict_types=1);

namespace Capell\CustomerPortal\Enums;

enum SupportRequestStatus: string
{
    case Open = 'open';
    case WaitingOnCustomer = 'waiting_on_customer';
    case WaitingOnTeam = 'waiting_on_team';
    case Resolved = 'resolved';
    case Closed = 'closed';

    public function getLabel(): string
    {
        return __('capell-customer-portal::generic.support_request_status.' . $this->value);
    }
}
