<?php

declare(strict_types=1);

use Capell\CampaignStudio\Actions\BuildCampaignConversionFunnelAction;
use Capell\CampaignStudio\Actions\BuildCampaignLandingPageVariantsAction;
use Capell\CampaignStudio\Actions\ResolveCampaignLandingPageVariantAction;
use Capell\CampaignStudio\Actions\SyncCampaignExperimentAction;
use Capell\CampaignStudio\Filament\Resources\CampaignGroups\CampaignGroupResource;
use Capell\CampaignStudio\Filament\Resources\CampaignLandingPages\CampaignLandingPageResource;
use Capell\CampaignStudio\Manifest\CampaignOverviewStatsContribution;
use Capell\CampaignStudio\Manifest\CampaignStudioAdminResourcesContribution;
use Capell\CampaignStudio\Manifest\CampaignStudioDashboardWidgetsContribution;
use Capell\CampaignStudio\Manifest\CampaignStudioModelsContribution;
use Capell\CampaignStudio\Manifest\CampaignWidgetConfiguratorsContribution;

function campaignStudioManifest(): array
{
    return json_decode(
        (string) file_get_contents(__DIR__ . '/../../capell.json'),
        true,
        flags: JSON_THROW_ON_ERROR,
    );
}

it('declares implemented campaign studio contribution surfaces', function (): void {
    $manifest = campaignStudioManifest();
    $contributions = collect($manifest['contributes']);

    expect($manifest['database']['requiredTables'])->toContain(
        'campaign_groups',
        'campaign_landing_pages',
        'campaign_conversion_goals',
        'campaign_conversions',
    )
        ->and($manifest['contributionTraceability']['deferredContributions'])->toBe([])
        ->and($contributions->pluck('class')->all())->toContain(
            CampaignStudioAdminResourcesContribution::class,
            CampaignWidgetConfiguratorsContribution::class,
            CampaignStudioDashboardWidgetsContribution::class,
            CampaignOverviewStatsContribution::class,
            CampaignStudioModelsContribution::class,
        );

    $resources = $contributions->firstWhere('class', CampaignStudioAdminResourcesContribution::class);
    $stats = $contributions->firstWhere('class', CampaignOverviewStatsContribution::class);

    expect($resources['resourceClasses'])->toContain(
        CampaignGroupResource::class,
        CampaignLandingPageResource::class,
    )
        ->and($stats['keys'])->toContain(
            'campaign_overview',
            'campaign_overview.conversions',
            'campaign_overview.conversion_rate',
        );
});

it('declares experiments, variants, audience targeting, and funnel reporting capabilities', function (): void {
    $manifest = campaignStudioManifest();

    expect($manifest['actions'])->toHaveKey('syncCampaignExperiment', SyncCampaignExperimentAction::class)
        ->and($manifest['actions'])->toHaveKey('buildCampaignLandingPageVariants', BuildCampaignLandingPageVariantsAction::class)
        ->and($manifest['actions'])->toHaveKey('resolveCampaignLandingPageVariant', ResolveCampaignLandingPageVariantAction::class)
        ->and($manifest['actions'])->toHaveKey('buildCampaignConversionFunnel', BuildCampaignConversionFunnelAction::class)
        ->and($manifest['capabilities'])->toContain(
            'campaign-experiment-sync',
            'landing-page-variant-experiments',
            'landing-page-variant-management',
            'campaign-audience-targeting',
            'campaign-conversion-funnel-reporting',
        );
});
