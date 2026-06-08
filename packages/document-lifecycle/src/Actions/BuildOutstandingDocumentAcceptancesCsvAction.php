<?php

declare(strict_types=1);

namespace Capell\DocumentLifecycle\Actions;

use Capell\DocumentLifecycle\Models\Document;
use Capell\DocumentLifecycle\Models\DocumentAcceptance;
use Capell\DocumentLifecycle\Models\DocumentPublication;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static string run(Document $document)
 */
final class BuildOutstandingDocumentAcceptancesCsvAction
{
    use AsAction;

    public function handle(Document $document): string
    {
        $stream = fopen('php://temp', 'r+');

        if ($stream === false) {
            return '';
        }

        fputcsv($stream, [
            'document_key',
            'document_title',
            'required_publication_id',
            'required_version',
            'required_hash',
            'subject_type',
            'subject_id',
            'acceptor_type',
            'acceptor_id',
            'last_accepted_publication_id',
            'last_accepted_version',
            'last_accepted_hash',
            'last_accepted_at',
            'context',
        ]);

        $latestPublication = $document->latestPublication();

        if ($latestPublication instanceof DocumentPublication) {
            $latestPublicationId = self::modelKey($latestPublication);

            $this->latestKnownAcceptances($document)
                ->filter(static fn (DocumentAcceptance $acceptance): bool => $acceptance->document_publication_id !== $latestPublicationId
                    || $acceptance->document_hash !== $latestPublication->content_hash)
                ->each(static function (DocumentAcceptance $acceptance) use ($document, $latestPublication, $stream): void {
                    fputcsv($stream, [
                        $document->key,
                        $document->title,
                        self::modelKey($latestPublication),
                        $latestPublication->version_label,
                        $latestPublication->content_hash,
                        $acceptance->subject_type,
                        $acceptance->subject_id,
                        $acceptance->acceptor_type,
                        $acceptance->acceptor_id,
                        $acceptance->document_publication_id,
                        $acceptance->document_version,
                        $acceptance->document_hash,
                        $acceptance->accepted_at->toISOString(),
                        $acceptance->context,
                    ]);
                });
        }

        rewind($stream);

        $contents = stream_get_contents($stream);
        fclose($stream);

        return is_string($contents) ? $contents : '';
    }

    private static function modelKey(Model $model): int
    {
        $key = $model->getKey();

        return is_int($key) ? $key : 0;
    }

    /**
     * @return Collection<string, DocumentAcceptance>
     */
    private function latestKnownAcceptances(Document $document): Collection
    {
        return DocumentAcceptance::query()
            ->where('document_key', $document->key)
            ->latest('accepted_at')
            ->latest('id')
            ->get()
            ->reduce(static function (Collection $acceptances, DocumentAcceptance $acceptance): Collection {
                $subjectType = $acceptance->subject_type ?: $acceptance->acceptor_type ?: 'unknown';
                $subjectId = $acceptance->subject_id ?: $acceptance->acceptor_id ?: 'unknown';
                $key = $subjectType . ':' . $subjectId;

                if (! $acceptances->has($key)) {
                    $acceptances->put($key, $acceptance);
                }

                return $acceptances;
            }, collect());
    }
}
