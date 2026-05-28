<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Enums;

enum PageSpeedAuditRunStatusEnum: string
{
    case Running = 'running';
    case Succeeded = 'succeeded';
    case SucceededWithErrors = 'succeeded_with_errors';
    case Failed = 'failed';
}
