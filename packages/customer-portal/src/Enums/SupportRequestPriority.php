<?php

declare(strict_types=1);

namespace Capell\CustomerPortal\Enums;

enum SupportRequestPriority: string
{
    case Low = 'low';
    case Normal = 'normal';
    case High = 'high';
    case Urgent = 'urgent';

    public function getLabel(): string
    {
        return __('capell-customer-portal::generic.support_request_priority.' . $this->value);
    }
}
