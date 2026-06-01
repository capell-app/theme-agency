<?php

declare(strict_types=1);

namespace Capell\CustomerPortal\Filament\Resources\PortalSupportRequests\Pages;

use Capell\CustomerPortal\Actions\UpdateSupportRequestStatusAction;
use Capell\CustomerPortal\Enums\SupportRequestStatus;
use Capell\CustomerPortal\Filament\Resources\PortalSupportRequests\PortalSupportRequestResource;
use Capell\CustomerPortal\Models\PortalSupportRequest;
use Filament\Resources\Pages\EditRecord;

final class EditPortalSupportRequest extends EditRecord
{
    protected static string $resource = PortalSupportRequestResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $record = $this->getRecord();
        $status = SupportRequestStatus::tryFrom((string) ($data['status'] ?? ''));

        if ($record instanceof PortalSupportRequest && $status instanceof SupportRequestStatus) {
            UpdateSupportRequestStatusAction::run($record, $status);
        }

        unset($data['status']);

        return $data;
    }
}
