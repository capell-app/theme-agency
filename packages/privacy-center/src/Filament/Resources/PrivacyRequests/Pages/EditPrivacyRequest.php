<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Filament\Resources\PrivacyRequests\Pages;

use Capell\PrivacyCenter\Filament\Resources\PrivacyRequests\PrivacyRequestResource;
use Filament\Resources\Pages\EditRecord;

final class EditPrivacyRequest extends EditRecord
{
    protected static string $resource = PrivacyRequestResource::class;
}
