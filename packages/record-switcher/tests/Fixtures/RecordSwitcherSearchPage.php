<?php

declare(strict_types=1);

namespace Capell\RecordSwitcher\Tests\Fixtures;

use Capell\RecordSwitcher\Concerns\HasRecordSwitcher;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Builder;

final class RecordSwitcherSearchPage extends EditRecord
{
    use HasRecordSwitcher;

    /**
     * @param  Builder<RecordSwitcherSearchFixture>  $query
     */
    public static function applySearch(Builder $query, string $search): void
    {
        self::applyRecordSwitcherAttributeConstraints($query, $search);
    }

    /**
     * @return array<int, string>
     */
    protected static function getRecordSwitcherSearchColumns(): array
    {
        return ['name', '`key`', 'admin->notes'];
    }
}
