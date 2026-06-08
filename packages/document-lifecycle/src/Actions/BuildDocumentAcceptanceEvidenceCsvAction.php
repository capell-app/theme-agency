<?php

declare(strict_types=1);

namespace Capell\DocumentLifecycle\Actions;

use Capell\DocumentLifecycle\Models\Document;
use Capell\DocumentLifecycle\Models\DocumentAcceptance;
use Capell\DocumentLifecycle\Models\DocumentPublication;
use Illuminate\Database\Eloquent\Builder;
use Lorisleiva\Actions\Concerns\AsAction;

final class BuildDocumentAcceptanceEvidenceCsvAction
{
    use AsAction;

    public function handle(Document $document, ?DocumentPublication $publication = null): string
    {
        $stream = fopen('php://temp', 'r+');

        if ($stream === false) {
            return '';
        }

        fputcsv($stream, [
            'id',
            'document_key',
            'document_title',
            'document_version',
            'document_publication_id',
            'document_hash',
            'acceptor_type',
            'acceptor_id',
            'subject_type',
            'subject_id',
            'context',
            'accepted_at',
            'ip_hash',
            'user_agent_hash',
            'legal_bundle_version',
            'legal_bundle_hash',
            'metadata',
        ]);

        $this->query($document, $publication)
            ->cursor()
            ->each(static function (DocumentAcceptance $acceptance) use ($document, $stream): void {
                fputcsv($stream, [
                    $acceptance->getKey(),
                    $acceptance->document_key,
                    $document->title,
                    $acceptance->document_version,
                    $acceptance->document_publication_id,
                    $acceptance->document_hash,
                    $acceptance->acceptor_type,
                    $acceptance->acceptor_id,
                    $acceptance->subject_type,
                    $acceptance->subject_id,
                    $acceptance->context,
                    $acceptance->accepted_at?->toISOString(),
                    $acceptance->ip_hash,
                    $acceptance->user_agent_hash,
                    $acceptance->legal_bundle_version,
                    $acceptance->legal_bundle_hash,
                    self::json($acceptance->metadata),
                ]);
            });

        rewind($stream);

        $contents = stream_get_contents($stream);
        fclose($stream);

        return is_string($contents) ? $contents : '';
    }

    /**
     * @param  array<array-key, mixed>|null  $value
     */
    private static function json(?array $value): string
    {
        if ($value === null || $value === []) {
            return '';
        }

        return json_encode($value, JSON_THROW_ON_ERROR);
    }

    /**
     * @return Builder<DocumentAcceptance>
     */
    private function query(Document $document, ?DocumentPublication $publication): Builder
    {
        return DocumentAcceptance::query()
            ->where('document_key', $document->key)
            ->when($publication instanceof DocumentPublication, static fn (Builder $query): Builder => $query
                ->where('document_publication_id', $publication->getKey()))
            ->latest('accepted_at')
            ->latest('id');
    }
}
