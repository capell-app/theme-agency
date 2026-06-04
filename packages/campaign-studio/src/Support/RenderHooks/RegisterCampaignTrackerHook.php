<?php

declare(strict_types=1);

namespace Capell\CampaignStudio\Support\RenderHooks;

use Capell\CampaignStudio\Providers\CampaignStudioServiceProvider;
use Capell\Frontend\Actions\Performance\RecordExtensionRenderContributionAction;
use Capell\Frontend\Data\RenderHookContext;
use Capell\Frontend\Enums\RenderHookLocation;
use Capell\Frontend\Support\Render\RenderHookRegistry;

final class RegisterCampaignTrackerHook
{
    /** @param RenderHookRegistry<RenderHookContext> $registry */
    public function __construct(private readonly RenderHookRegistry $registry) {}

    public function register(): void
    {
        $this->registry->register(
            RenderHookLocation::BodyEnd,
            static function (): string {
                $startedAt = microtime(true);
                $html = view('capell-campaign-studio::tracker')->render();

                RecordExtensionRenderContributionAction::run(
                    packageName: CampaignStudioServiceProvider::$packageName,
                    surface: 'frontend',
                    contributionType: 'conversion-tracker',
                    contributionClass: self::class,
                    elapsedMilliseconds: (microtime(true) - $startedAt) * 1000,
                    frontendRenderBudgetMs: 20,
                    cacheTags: ['campaign-studio'],
                    cacheable: false,
                    sensitiveOutput: false,
                    variesBy: ['site', 'locale', 'utm_campaign', 'utm_content', 'utm_term'],
                );

                return $html;
            },
        );
    }
}
