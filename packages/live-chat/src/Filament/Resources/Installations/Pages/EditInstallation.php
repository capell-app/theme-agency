<?php

declare(strict_types=1);

namespace Capell\LiveChat\Filament\Resources\Installations\Pages;

use Capell\LiveChat\Filament\Resources\Installations\InstallationResource;
use Filament\Resources\Pages\EditRecord;

final class EditInstallation extends EditRecord
{
    protected static string $resource = InstallationResource::class;
}
