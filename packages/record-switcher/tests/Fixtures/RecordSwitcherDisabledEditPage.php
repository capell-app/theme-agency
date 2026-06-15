<?php

declare(strict_types=1);

namespace Capell\RecordSwitcher\Tests\Fixtures;

use Filament\Resources\Pages\EditRecord;

final class RecordSwitcherDisabledEditPage extends EditRecord
{
    protected static string $resource = RecordSwitcherDisabledResource::class;
}
