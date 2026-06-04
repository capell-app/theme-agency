<?php

declare(strict_types=1);

namespace Capell\UrlManager\Actions;

use Lorisleiva\Actions\Concerns\AsAction;

final class BuildRedirectRulesCsvAction
{
    use AsAction;

    public function handle(?int $siteId = null, ?int $languageId = null): string
    {
        $rows = ExportRedirectRulesAction::run($siteId, $languageId);
        $stream = fopen('php://temp', 'r+');

        if ($stream === false) {
            return '';
        }

        fputcsv($stream, [
            'source_url',
            'target_url',
            'site_id',
            'language_id',
            'status_code',
            'match_type',
            'status',
            'priority',
            'preserve_query',
            'notes',
        ]);

        foreach ($rows as $row) {
            fputcsv($stream, [
                $row['source_url'],
                $row['target_url'],
                $row['site_id'],
                $row['language_id'],
                $row['status_code'],
                $row['match_type'],
                $row['status'],
                $row['priority'],
                $row['preserve_query'] ? '1' : '0',
                $row['notes'],
            ]);
        }

        rewind($stream);

        $contents = stream_get_contents($stream);
        fclose($stream);

        return is_string($contents) ? $contents : '';
    }
}
