<?php

declare(strict_types=1);

namespace Capell\CampaignStudio\Providers;

use Capell\Admin\Contracts\Extenders\PageSchemaExtender;
use Capell\CampaignStudio\Console\Commands\InstallCampaignLayoutsCommand;
use Capell\CampaignStudio\Enums\CampaignBlockComponentEnum;
use Capell\CampaignStudio\Filament\Extenders\Page\CampaignPageSchemaExtender;
use Capell\CampaignStudio\Listeners\RecordFormSubmissionConversion;
use Capell\CampaignStudio\Listeners\SyncCampaignLandingPageFromPage;
use Capell\CampaignStudio\Models\CampaignConversion;
use Capell\CampaignStudio\Models\CampaignConversionGoal;
use Capell\CampaignStudio\Models\CampaignCtaBlock;
use Capell\CampaignStudio\Models\CampaignGroup;
use Capell\CampaignStudio\Models\CampaignLandingPage;
use Capell\CampaignStudio\Policies\CampaignConversionGoalPolicy;
use Capell\CampaignStudio\Policies\CampaignCtaBlockPolicy;
use Capell\CampaignStudio\Policies\CampaignGroupPolicy;
use Capell\CampaignStudio\Policies\CampaignLandingPagePolicy;
use Capell\Core\Data\VendorAssetData;
use Capell\Core\Events\PageSaved;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Packages\AbstractPackageServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Spatie\LaravelPackageTools\Package;

final class CampaignStudioServiceProvider extends AbstractPackageServiceProvider
{
    public static string $name = 'capell-campaign-studio';

    public static string $packageName = 'capell-app/campaign-studio';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(self::$name)
            ->hasConfigFile('capell-campaign-studio')
            ->hasTranslations()
            ->hasViews(self::$name)
            ->hasCommand(InstallCampaignLayoutsCommand::class)
            ->hasMigrations([
                '2026_05_10_190843_01_create_campaign_groups_table',
                '2026_05_10_190843_03_create_campaign_landing_pages_table',
                '2026_05_10_190843_04_create_campaign_cta_blocks_table',
                '2026_05_10_190843_02_create_campaign_conversion_goals_table',
                '2026_05_10_190843_05_create_campaign_conversions_table',
            ]);
    }

    public function registeringPackage(): void
    {
        $this->app->register(AdminServiceProvider::class);

        $this->app->booted(function (): void {
            if (! $this->isPackageInstalled()) {
                return;
            }

            $this->bootInstalledPackage();
        });
    }

    protected function isPackageInstalled(): bool
    {
        return CapellCore::isPackageInstalled(self::$packageName);
    }

    private function bootInstalledPackage(): self
    {
        return $this
            ->registerModels()
            ->registerPolicies()
            ->registerComponents()
            ->registerSchemaExtenders()
            ->registerPackageAssets()
            ->registerProtectedTables()
            ->registerListeners();
    }

    private function registerPackageAssets(): self
    {
        CapellCore::registerVendorAsset(
            VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName),
        );

        return $this;
    }

    private function registerModels(): self
    {
        CapellCore::registerModels([
            CampaignGroup::class,
            CampaignLandingPage::class,
            CampaignCtaBlock::class,
            CampaignConversionGoal::class,
            CampaignConversion::class,
        ]);

        return $this;
    }

    private function registerPolicies(): self
    {
        Gate::policy(CampaignGroup::class, CampaignGroupPolicy::class);
        Gate::policy(CampaignLandingPage::class, CampaignLandingPagePolicy::class);
        Gate::policy(CampaignCtaBlock::class, CampaignCtaBlockPolicy::class);
        Gate::policy(CampaignConversionGoal::class, CampaignConversionGoalPolicy::class);

        return $this;
    }

    private function registerComponents(): self
    {
        Blade::componentNamespace('Capell\\CampaignStudio\\View\\Components', 'capell-campaign-studio');

        CapellCore::registerComponents('Block', CampaignBlockComponentEnum::cases());

        return $this;
    }

    private function registerSchemaExtenders(): self
    {
        $this->app->singleton(CampaignPageSchemaExtender::class);
        $this->app->tag(CampaignPageSchemaExtender::class, PageSchemaExtender::TAG);

        return $this;
    }

    private function registerProtectedTables(): self
    {
        $tableNames = config('capell-campaign-studio.tables', []);

        if (! is_array($tableNames)) {
            return $this;
        }

        foreach ($tableNames as $tableName) {
            if (! is_string($tableName)) {
                continue;
            }

            if ($tableName === '') {
                continue;
            }

            CapellCore::registerProtectedTable(fn (): string => $tableName);
        }

        return $this;
    }

    private function registerListeners(): self
    {
        Event::listen(PageSaved::class, SyncCampaignLandingPageFromPage::class);

        $formSubmittedEvent = implode('\\', ['Capell', 'FormBuilder', 'Events', 'FormSubmitted']);

        if (class_exists($formSubmittedEvent)) {
            Event::listen($formSubmittedEvent, RecordFormSubmissionConversion::class);
        }

        return $this;
    }
}
