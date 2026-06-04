<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Filament\Resources\PrivacyRequests\Pages;

use Capell\PrivacyCenter\Filament\Resources\PrivacyRequests\PrivacyRequestResource;
use Filament\Resources\Pages\EditRecord;
use Override;

final class EditPrivacyRequest extends EditRecord
{
    protected static string $resource = PrivacyRequestResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return PrivacyRequestResource::privacyRequestWorkflowActions();
    }
}
