<?php

declare(strict_types=1);

namespace Capell\CustomerPortal\Enums;

enum PortalDashboardItemPriority: int
{
    case Low = 10;
    case Normal = 50;
    case High = 90;

    public function getLabel(): string
    {
        return __('capell-customer-portal::generic.dashboard_item_priority.' . strtolower($this->name));
    }
}
