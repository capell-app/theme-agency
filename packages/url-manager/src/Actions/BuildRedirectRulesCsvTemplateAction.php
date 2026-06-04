<?php

declare(strict_types=1);

namespace Capell\UrlManager\Actions;

use Lorisleiva\Actions\Concerns\AsAction;

final class BuildRedirectRulesCsvTemplateAction
{
    use AsAction;

    public function handle(): string
    {
        return implode(',', [
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
        ]) . PHP_EOL;
    }
}
