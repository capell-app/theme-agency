<?php

declare(strict_types=1);

namespace Capell\AiCreator\Actions;

use Capell\AiCreator\Data\SiteSpec\CapellSiteSpecData;
use Capell\Core\Models\Site;
use Capell\Core\Support\Json\JsonCodec;
use Illuminate\Support\Facades\Storage;
use Lorisleiva\Actions\Concerns\AsAction;
use RuntimeException;

/**
 * Shared reader for the prompt-to-site local path: resolve a spec file
 * (absolute / base_path-relative / local-disk `ai-creator/exports`), validate
 * it into a CapellSiteSpecData, and build the site. Inference-free.
 *
 * Both the core post-install listener (BuildSiteFromInstalledSpecListener) and
 * the capell-app `capell:install-from-spec` command delegate here, so the
 * file-resolution + validation + build path lives in exactly one place.
 *
 * @method static Site run(string $path)
 */
final class BuildSiteFromSpecFileAction
{
    use AsAction;

    public function handle(string $path): Site
    {
        $contents = $this->readSpecFile($path);

        if ($contents === null) {
            throw new RuntimeException("Could not read a spec file at: {$path}");
        }

        $decoded = JsonCodec::decodeArray($contents);

        $spec = CapellSiteSpecData::validateAndCreate($decoded);

        return BuildCapellSiteFromSpecAction::run($spec);
    }

    private function readSpecFile(string $path): ?string
    {
        if (is_file($path)) {
            return (string) file_get_contents($path);
        }

        $relativePath = base_path($path);

        if (is_file($relativePath)) {
            return (string) file_get_contents($relativePath);
        }

        $exportPath = 'ai-creator/exports/' . ltrim($path, '/');

        if (Storage::disk('local')->exists($exportPath)) {
            return (string) Storage::disk('local')->get($exportPath);
        }

        return null;
    }
}
