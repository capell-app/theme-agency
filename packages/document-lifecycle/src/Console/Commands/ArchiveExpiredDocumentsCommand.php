<?php

declare(strict_types=1);

namespace Capell\DocumentLifecycle\Console\Commands;

use Capell\DocumentLifecycle\Actions\ArchiveExpiredDocumentsAction;
use Illuminate\Console\Command;

final class ArchiveExpiredDocumentsCommand extends Command
{
    protected $signature = 'capell:document-lifecycle:archive-expired {--json : Output the archived count as JSON}';

    protected $description = 'Archive active controlled documents whose expiry date has passed.';

    public function handle(): int
    {
        $archived = ArchiveExpiredDocumentsAction::run();

        if ($this->option('json') === true) {
            $this->line(json_encode(['archived' => $archived], JSON_THROW_ON_ERROR));

            return self::SUCCESS;
        }

        $this->components->info((string) __('capell-document-lifecycle::navigation.commands.archive_expired.summary', [
            'count' => $archived,
        ]));

        return self::SUCCESS;
    }
}
