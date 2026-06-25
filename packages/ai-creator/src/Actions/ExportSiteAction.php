<?php

declare(strict_types=1);

namespace Capell\AiCreator\Actions;

use Capell\AiCreator\Data\SiteSpec\CapellSiteSpecData;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsObject;

/**
 * Produce the local-export artifact for a finished spec: a portable spec file
 * plus the command that materialises it into a fresh local Capell project. Pure
 * — it computes the artifact (no filesystem IO); the capability persists it.
 * The exported spec is the SAME contract the builder consumes, so a local
 * `capell:install --spec=<file>` reproduces the previewed site exactly.
 *
 * @method static array{project: string, filename: string, spec: array<string, mixed>, install_command: string} run(CapellSiteSpecData $spec, string $projectName)
 */
final class ExportSiteAction
{
    use AsObject;

    /**
     * @return array{project: string, filename: string, spec: array<string, mixed>, install_command: string}
     */
    public function handle(CapellSiteSpecData $spec, string $projectName): array
    {
        $slug = Str::slug($projectName) ?: 'capell-site';
        $filename = $slug . '.capell-spec.json';

        return [
            'project' => $projectName,
            'filename' => $filename,
            'spec' => $spec->toArray(),
            'install_command' => 'php artisan capell:install --spec=' . $filename,
        ];
    }
}
