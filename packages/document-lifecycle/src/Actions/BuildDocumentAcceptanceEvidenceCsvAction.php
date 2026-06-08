<?php

declare(strict_types=1);

namespace Capell\DocumentLifecycle\Actions;

use Capell\DocumentLifecycle\Models\Document;
use Capell\DocumentLifecycle\Models\DocumentAcceptance;
use Capell\DocumentLifecycle\Models\DocumentPublication;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static string run(Document $document, DocumentPublication|null $publication = null)
 */
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
                    self::modelKey($acceptance),
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
                    $acceptance->accepted_at->toISOString(),
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

    private static function modelKey(Model $model): int
    {
        $key = $model->getKey();

        return is_int($key) ? $key : 0;
    }

    /**
     * @return Builder<DocumentAcceptance>
     */
    private function query(Document $document, ?DocumentPublication $publication): Builder
    {
        $query = DocumentAcceptance::query()
            ->where('document_key', $document->key)
            ->latest('accepted_at')
            ->latest('id');

        if ($publication instanceof DocumentPublication) {
            $query->where('document_publication_id', self::modelKey($publication));
        }

        return $query;
    }
}
