<?php

declare(strict_types=1);

namespace Capell\RecordSwitcher\Tests\Fixtures;

use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Model;
use Override;

class RecordSwitcherTestRecordResource extends Resource
{
    protected static ?string $model = RecordSwitcherTestRecord::class;

    protected static ?string $recordTitleAttribute = 'name';

    /**
     * @return array<int, string>
     */
    #[Override]
    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'code'];
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    #[Override]
    public static function getUrl(?string $name = null, array $parameters = [], bool $isAbsolute = true, ?string $panel = null, ?Model $tenant = null, bool $shouldGuessMissingParameters = false, ?string $configuration = null): string
    {
        $record = $parameters['record'] ?? null;
        $recordKey = $record instanceof Model ? $record->getRouteKey() : null;

        return sprintf('/record-switcher-test-records/%s/%s', is_string($recordKey) || is_int($recordKey) ? $recordKey : 'missing', $name ?? 'index');
    }
}
