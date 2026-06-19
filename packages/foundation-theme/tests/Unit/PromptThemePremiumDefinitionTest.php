<?php

declare(strict_types=1);
use Capell\ThemeStudio\AeoAnalytics\AeoAnalyticsThemeServiceProvider;
use Capell\ThemeStudio\AiAgent\AiAgentThemeServiceProvider;
use Capell\ThemeStudio\AiLab\AiLabThemeServiceProvider;
use Capell\ThemeStudio\ApiPlatform\ApiPlatformThemeServiceProvider;
use Capell\ThemeStudio\AutomotiveDealer\AutomotiveDealerThemeServiceProvider;
use Capell\ThemeStudio\BeautySpa\BeautySpaThemeServiceProvider;
use Capell\ThemeStudio\ConferenceEvent\ConferenceEventThemeServiceProvider;
use Capell\ThemeStudio\ConstructionTrades\ConstructionTradesThemeServiceProvider;
use Capell\ThemeStudio\CreatorNewsletter\CreatorNewsletterThemeServiceProvider;
use Capell\ThemeStudio\CryptoDefi\CryptoDefiThemeServiceProvider;
use Capell\ThemeStudio\DesignStudio\DesignStudioThemeServiceProvider;
use Capell\ThemeStudio\DevtoolOss\DevtoolOssThemeServiceProvider;
use Capell\ThemeStudio\EditorialSerif\EditorialSerifThemeServiceProvider;
use Capell\ThemeStudio\FinancialAdvisory\FinancialAdvisoryThemeServiceProvider;
use Capell\ThemeStudio\FintechTrust\FintechTrustThemeServiceProvider;
use Capell\ThemeStudio\FitnessWellness\FitnessWellnessThemeServiceProvider;
use Capell\ThemeStudio\LawFirm\LawFirmThemeServiceProvider;
use Capell\ThemeStudio\Manufacturing\ManufacturingThemeServiceProvider;
use Capell\ThemeStudio\NewsroomMagazine\NewsroomMagazineThemeServiceProvider;
use Capell\ThemeStudio\PackagingSupplier\PackagingSupplierThemeServiceProvider;
use Capell\ThemeStudio\PersonalDev\PersonalDevThemeServiceProvider;
use Capell\ThemeStudio\PodcastShow\PodcastShowThemeServiceProvider;
use Capell\ThemeStudio\ProductStudio\ProductStudioThemeServiceProvider;
use Capell\ThemeStudio\PropertyDeveloper\PropertyDeveloperThemeServiceProvider;
use Capell\ThemeStudio\QuantTrading\QuantTradingThemeServiceProvider;
use Capell\ThemeStudio\RecruitmentJobs\RecruitmentJobsThemeServiceProvider;
use Capell\ThemeStudio\RoboticsHardware\RoboticsHardwareThemeServiceProvider;
use Capell\ThemeStudio\TravelTourism\TravelTourismThemeServiceProvider;

it('gives every prompt-built theme premium custom sections', function (string $providerClass): void {
    $definition = $providerClass::definition();
    $standardSections = ['navigation', 'hero', 'features', 'proof', 'content-listing', 'cta', 'footer'];
    $customSections = array_values(array_diff($definition->includedSections, $standardSections));

    expect($definition->includedSections)
        ->toContain(...$standardSections)
        ->and($customSections)
        ->toHaveCount(count($customSections))
        ->and(count($customSections))
        ->toBeGreaterThanOrEqual(3);
})->with([
    AiLabThemeServiceProvider::class,
    ApiPlatformThemeServiceProvider::class,
    AiAgentThemeServiceProvider::class,
    AeoAnalyticsThemeServiceProvider::class,
    FintechTrustThemeServiceProvider::class,
    CryptoDefiThemeServiceProvider::class,
    QuantTradingThemeServiceProvider::class,
    DevtoolOssThemeServiceProvider::class,
    RoboticsHardwareThemeServiceProvider::class,
    ManufacturingThemeServiceProvider::class,
    PackagingSupplierThemeServiceProvider::class,
    ConferenceEventThemeServiceProvider::class,
    PodcastShowThemeServiceProvider::class,
    NewsroomMagazineThemeServiceProvider::class,
    DesignStudioThemeServiceProvider::class,
    ProductStudioThemeServiceProvider::class,
    PersonalDevThemeServiceProvider::class,
    CreatorNewsletterThemeServiceProvider::class,
    LawFirmThemeServiceProvider::class,
    FinancialAdvisoryThemeServiceProvider::class,
    ConstructionTradesThemeServiceProvider::class,
    FitnessWellnessThemeServiceProvider::class,
    BeautySpaThemeServiceProvider::class,
    TravelTourismThemeServiceProvider::class,
    AutomotiveDealerThemeServiceProvider::class,
    PropertyDeveloperThemeServiceProvider::class,
    RecruitmentJobsThemeServiceProvider::class,
    EditorialSerifThemeServiceProvider::class,
]);
