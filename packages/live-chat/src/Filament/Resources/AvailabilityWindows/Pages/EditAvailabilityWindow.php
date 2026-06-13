<?php

declare(strict_types=1);

namespace Capell\LiveChat\Filament\Resources\AvailabilityWindows\Pages;

use Capell\LiveChat\Filament\Resources\AvailabilityWindows\AvailabilityWindowResource;
use Filament\Resources\Pages\EditRecord;

final class EditAvailabilityWindow extends EditRecord
{
    protected static string $resource = AvailabilityWindowResource::class;
}
