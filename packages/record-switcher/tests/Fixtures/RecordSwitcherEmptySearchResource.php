<?php

declare(strict_types=1);

namespace Capell\RecordSwitcher\Tests\Fixtures;

final class RecordSwitcherEmptySearchResource extends RecordSwitcherTestRecordResource
{
    /**
     * @return array<int, string>
     */
    public static function getGloballySearchableAttributes(): array
    {
        return [];
    }
}
