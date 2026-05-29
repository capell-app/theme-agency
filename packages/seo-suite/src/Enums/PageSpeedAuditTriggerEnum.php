<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Enums;

enum PageSpeedAuditTriggerEnum: string
{
    case Manual = 'manual';
    case Scheduled = 'scheduled';
    case Command = 'command';
}
