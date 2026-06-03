<?php

declare(strict_types=1);

namespace Capell\DocumentLifecycle\Actions;

use Capell\DocumentLifecycle\Models\DocumentAcceptance;
use Capell\DocumentLifecycle\Models\DocumentPublication;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Lorisleiva\Actions\Concerns\AsAction;

final class RecordDocumentAcceptanceAction
{
    use AsAction;

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function handle(
        string $documentKey,
        ?Model $acceptor = null,
        ?Model $subject = null,
        ?CarbonInterface $acceptedAt = null,
        ?string $context = null,
        array $metadata = [],
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): DocumentAcceptance {
        $acceptedAt ??= now();
        $ipAddress ??= request()->ip();
        $userAgent ??= request()->userAgent();
        $publication = ResolveLatestDocumentPublicationAction::run($documentKey);
        $documentVersion = $publication instanceof DocumentPublication
            ? $publication->version_label
            : (string) config('legal.documents.' . $documentKey, config('legal.terms_version'));

        return DocumentAcceptance::query()->create([
            'acceptor_type' => $this->morphType($acceptor),
            'acceptor_id' => $acceptor?->getKey(),
            'subject_type' => $this->morphType($subject),
            'subject_id' => $subject?->getKey(),
            'document_key' => $documentKey,
            'document_version' => $documentVersion,
            'document_publication_id' => $publication?->getKey(),
            'document_hash' => $publication?->content_hash,
            'legal_bundle_version' => config('legal.bundle_version'),
            'legal_bundle_hash' => config('legal.bundle_hash'),
            'legal_document_versions' => config('legal.documents'),
            'accepted_at' => $acceptedAt,
            'context' => $context,
            'ip_hash' => $this->hashNullable($ipAddress),
            'user_agent_hash' => $this->hashNullable($userAgent),
            'metadata' => $metadata === [] ? null : $metadata,
        ]);
    }

    private function morphType(?Model $model): ?string
    {
        if (! $model instanceof Model) {
            return null;
        }

        $alias = array_search($model::class, Relation::morphMap(), true);

        return is_string($alias) ? $alias : $model::class;
    }

    private function hashNullable(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return hash('sha256', config('app.key') . ':' . $value);
    }
}
