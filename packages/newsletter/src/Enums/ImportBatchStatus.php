<?php

declare(strict_types=1);

namespace Capell\Newsletter\Enums;

enum ImportBatchStatus: string
{
    case DryRun = 'dry_run';
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';
}
