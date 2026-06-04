<?php

declare(strict_types=1);

namespace Capell\DocumentLifecycle\Actions;

use Capell\DocumentLifecycle\Enums\DocumentStatusEnum;
use Capell\DocumentLifecycle\Models\Document;
use Capell\DocumentLifecycle\Models\DocumentPublication;
use Lorisleiva\Actions\Concerns\AsAction;

final class RestoreDocumentAction
{
    use AsAction;

    public function handle(Document $document): Document
    {
        if ($document->status !== DocumentStatusEnum::Archived) {
            return $document;
        }

        $document->forceFill([
            'status' => $document->latestPublication() instanceof DocumentPublication
                ? DocumentStatusEnum::Active
                : DocumentStatusEnum::Draft,
        ])->save();

        return $document->refresh();
    }
}
