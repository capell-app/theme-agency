<?php

declare(strict_types=1);

namespace Capell\CampaignStudio\Actions;

use Capell\CampaignStudio\Providers\CampaignStudioServiceProvider;
use Illuminate\Support\Facades\Cache;
use Lorisleiva\Actions\Concerns\AsAction;
use ReflectionClass;
use RuntimeException;

final class GetCampaignStudioTrackerScriptAction
{
    use AsAction;

    public function handle(): string
    {
        $providerPath = (new ReflectionClass(CampaignStudioServiceProvider::class))->getFileName();
        $path = dirname(is_string($providerPath) ? $providerPath : __DIR__, 3) . '/resources/js/capell-campaigns.js';
        $modifiedAt = file_exists($path) ? (int) filemtime($path) : 0;

        return Cache::rememberForever(
            sprintf('capell-campaigns.tracker-script.%s', $modifiedAt),
            static function () use ($path): string {
                $contents = file_get_contents($path);

                throw_unless(is_string($contents), RuntimeException::class, 'Unable to read Capell campaign tracker script.');

                return $contents;
            },
        );
    }
}
