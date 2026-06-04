<?php

declare(strict_types=1);

namespace Capell\DocumentLifecycle\Actions;

use Capell\DocumentLifecycle\Enums\DocumentStatusEnum;
use Capell\DocumentLifecycle\Models\Document;
use Lorisleiva\Actions\Concerns\AsAction;

final class ArchiveDocumentAction
{
    use AsAction;

    public function handle(Document $document): Document
    {
        if ($document->status === DocumentStatusEnum::Archived) {
            return $document;
        }

        $document->forceFill([
            'status' => DocumentStatusEnum::Archived,
        ])->save();

        return $document->refresh();
    }
}
