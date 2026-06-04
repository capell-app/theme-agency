<?php

declare(strict_types=1);

namespace Capell\DocumentLifecycle\Filament\Resources\Documents\Pages;

use Capell\DocumentLifecycle\Filament\Resources\Documents\DocumentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListDocuments extends ListRecords
{
    protected static string $resource = DocumentResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label(__('capell-document-lifecycle::navigation.actions.register_document')),
        ];
    }
}
