<?php

declare(strict_types=1);

namespace Capell\DocumentLifecycle\Actions;

use Capell\DocumentLifecycle\Enums\DocumentStatusEnum;
use Capell\DocumentLifecycle\Models\Document;
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
            'status' => $document->latestPublication() === null
                ? DocumentStatusEnum::Draft
                : DocumentStatusEnum::Active,
        ])->save();

        return $document->refresh();
    }
}
