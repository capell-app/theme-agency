<?php

declare(strict_types=1);

namespace Capell\RecordSwitcher\Tests\Fixtures;

final class RecordSwitcherDisabledResource extends RecordSwitcherTestRecordResource
{
    public static function recordSwitcherEnabled(): bool
    {
        return false;
    }
}
