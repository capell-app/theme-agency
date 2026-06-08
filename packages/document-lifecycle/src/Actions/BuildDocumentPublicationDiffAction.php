<?php

declare(strict_types=1);

namespace Capell\DocumentLifecycle\Actions;

use Capell\DocumentLifecycle\Models\DocumentPublication;
use Illuminate\Database\Eloquent\Model;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static string run(DocumentPublication $publication)
 */
final class BuildDocumentPublicationDiffAction
{
    use AsAction;

    public function handle(DocumentPublication $publication): string
    {
        $publication->loadMissing('document');

        $previousPublication = $publication->document
            ->publications()
            ->where('published_at', '<=', $publication->published_at)
            ->where('id', '<', $publication->getKey())
            ->latest('published_at')
            ->latest('id')
            ->first();

        return json_encode([
            'document' => [
                'key' => $publication->document->key,
                'title' => $publication->document->title,
            ],
            'from' => $this->publicationPayload($previousPublication),
            'to' => $this->publicationPayload($publication),
            'diff' => $this->diff(
                $this->snapshot($previousPublication),
                $this->snapshot($publication),
            ),
        ], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @return array{version: string|null, publication_id: int|null, content_hash: string|null, published_at: string|null, snapshot_available: bool}
     */
    private function publicationPayload(?DocumentPublication $publication): array
    {
        return [
            'version' => $publication?->version_label,
            'publication_id' => $publication instanceof DocumentPublication ? $this->modelKey($publication) : null,
            'content_hash' => $publication?->content_hash,
            'published_at' => $publication?->published_at?->toISOString(),
            'snapshot_available' => $this->snapshot($publication) !== null,
        ];
    }

    /**
     * @return list<array{line: int, before: string|null, after: string|null}>
     */
    private function diff(?string $before, ?string $after): array
    {
        if ($after === null) {
            return [];
        }

        $beforeLines = $before === null ? [] : explode("\n", $before);
        $afterLines = explode("\n", $after);
        $maxLines = max(count($beforeLines), count($afterLines));
        $changes = [];

        for ($line = 0; $line < $maxLines; $line++) {
            $beforeLine = $beforeLines[$line] ?? null;
            $afterLine = $afterLines[$line] ?? null;

            if ($beforeLine === $afterLine) {
                continue;
            }

            $changes[] = [
                'line' => $line + 1,
                'before' => $beforeLine,
                'after' => $afterLine,
            ];
        }

        return $changes;
    }

    private function snapshot(?DocumentPublication $publication): ?string
    {
        if (! $publication instanceof DocumentPublication || ! is_array($publication->metadata)) {
            return null;
        }

        $snapshot = $publication->metadata['content_snapshot'] ?? null;

        return is_string($snapshot) ? $snapshot : null;
    }

    private function modelKey(Model $model): int
    {
        $key = $model->getKey();

        return is_int($key) ? $key : 0;
    }
}
