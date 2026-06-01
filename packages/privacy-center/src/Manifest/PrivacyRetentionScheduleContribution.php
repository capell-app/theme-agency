<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Manifest;

use Capell\Core\Contracts\Extensions\RunsScheduledExtensionJob;

final class PrivacyRetentionScheduleContribution implements RunsScheduledExtensionJob
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
