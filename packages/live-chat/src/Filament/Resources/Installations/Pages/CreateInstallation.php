<?php

declare(strict_types=1);

namespace Capell\LiveChat\Filament\Resources\Installations\Pages;

use Capell\LiveChat\Filament\Resources\Installations\InstallationResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateInstallation extends CreateRecord
{
    protected static string $resource = InstallationResource::class;
}
