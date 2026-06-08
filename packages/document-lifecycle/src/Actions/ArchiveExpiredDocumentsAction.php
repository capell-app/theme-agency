<?php

declare(strict_types=1);

namespace Capell\DocumentLifecycle\Actions;

use Capell\DocumentLifecycle\Enums\DocumentStatusEnum;
use Capell\DocumentLifecycle\Models\Document;
use Carbon\CarbonInterface;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static int run(CarbonInterface|null $now = null)
 */
final class ArchiveExpiredDocumentsAction
{
    use AsAction;

    public function handle(?CarbonInterface $now = null): int
    {
        $now ??= now();
        $archived = 0;

        Document::query()
            ->where('status', DocumentStatusEnum::Active->value)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', $now)
            ->eachById(function (Document $document) use (&$archived): void {
                ArchiveDocumentAction::run($document);

                $archived++;
            });

        return $archived;
    }
}
