<?php

declare(strict_types=1);

namespace Capell\DocumentLifecycle\Filament\Resources\Documents\Pages;

use Capell\DocumentLifecycle\Actions\RegisterDocumentAction;
use Capell\DocumentLifecycle\Enums\DocumentStatusEnum;
use Capell\DocumentLifecycle\Filament\Resources\Documents\DocumentResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Override;

final class CreateDocument extends CreateRecord
{
    protected static string $resource = DocumentResource::class;

    /**
     * @param  array<string, mixed>  $data
     */
    #[Override]
    protected function handleRecordCreation(array $data): Model
    {
        $status = $data['status'] instanceof DocumentStatusEnum
            ? $data['status']
            : DocumentStatusEnum::from((string) $data['status']);

        return RegisterDocumentAction::run(
            key: (string) $data['key'],
            title: (string) $data['title'],
            status: $status,
            metadata: is_array($data['metadata'] ?? null) ? $data['metadata'] : [],
        );
    }

    #[Override]
    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label(__('capell-document-lifecycle::navigation.actions.register_document'));
    }
}
