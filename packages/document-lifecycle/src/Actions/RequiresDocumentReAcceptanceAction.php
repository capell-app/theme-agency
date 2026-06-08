<?php

declare(strict_types=1);

namespace Capell\DocumentLifecycle\Actions;

use Capell\DocumentLifecycle\Models\Document;
use Capell\DocumentLifecycle\Models\DocumentAcceptance;
use Capell\DocumentLifecycle\Models\DocumentPublication;
use Illuminate\Database\Eloquent\Model;
use Lorisleiva\Actions\Concerns\AsAction;

final class RequiresDocumentReAcceptanceAction
{
    use AsAction;

    public function handle(Document|string $document, ?Model $subject): bool
    {
        if (! $subject instanceof Model) {
            return false;
        }

        $resolvedDocument = is_string($document)
            ? Document::query()->where('key', $document)->first()
            : $document;

        if (! $resolvedDocument instanceof Document) {
            return false;
        }

        $latestPublication = $resolvedDocument->latestPublication();

        if (! $latestPublication instanceof DocumentPublication) {
            return false;
        }

        $latestAcceptance = DocumentAcceptance::query()
            ->where('document_key', $resolvedDocument->key)
            ->where('subject_type', $subject->getMorphClass())
            ->where('subject_id', $subject->getKey())
            ->latest('accepted_at')
            ->latest('id')
            ->first();

        if (! $latestAcceptance instanceof DocumentAcceptance) {
            return true;
        }

        return $latestAcceptance->document_publication_id !== $this->modelKey($latestPublication)
            || $latestAcceptance->document_hash !== $latestPublication->content_hash;
    }

    private function modelKey(Model $model): int
    {
        $key = $model->getKey();

        return is_int($key) ? $key : 0;
    }
}
