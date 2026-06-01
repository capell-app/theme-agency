<?php

declare(strict_types=1);

namespace Capell\DocumentLifecycle\Actions;

use Capell\DocumentLifecycle\Models\Document;
use Capell\DocumentLifecycle\Models\DocumentPublication;
use Illuminate\Database\Eloquent\Builder;
use Lorisleiva\Actions\Concerns\AsAction;

final class ResolveLatestDocumentPublicationAction
{
    use AsAction;

    public function handle(string $documentKey): ?DocumentPublication
    {
        return DocumentPublication::query()
            ->whereHas(
                'document',
                fn (Builder $query): Builder => $query->where('key', $documentKey),
            )
            ->with('document')
            ->latest('published_at')
            ->latest('id')
            ->first();
    }

    public function forDocument(Document $document): ?DocumentPublication
    {
        return $document->publications()
            ->latest('published_at')
            ->latest('id')
            ->first();
    }
}
