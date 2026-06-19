<?php

declare(strict_types=1);

namespace Capell\CampaignStudio\Health;

use Capell\CampaignStudio\Actions\CaptureCampaignConversionAction;
use Capell\CampaignStudio\Actions\SyncCampaignStatusesAction;
use Capell\CampaignStudio\Models\CampaignConversion;
use Capell\CampaignStudio\Models\CampaignConversionGoal;
use Capell\CampaignStudio\Models\CampaignGroup;
use Capell\CampaignStudio\Models\CampaignLandingPage;
use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

final class CampaignStudioHealthCheck implements ChecksExtensionHealth
{
    /** @var list<string> */
    private const array REQUIRED_TABLES = [
        'campaign_groups',
        'campaign_landing_pages',
        'campaign_conversion_goals',
        'campaign_conversions',
    ];

    /** @var list<class-string> */
    private const array REQUIRED_CLASSES = [
        CaptureCampaignConversionAction::class,
        SyncCampaignStatusesAction::class,
        CampaignGroup::class,
        CampaignLandingPage::class,
        CampaignConversionGoal::class,
        CampaignConversion::class,
    ];

    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }

    /**
     * @return Collection<int, DoctorCheckResultData>
     */
    public static function runDiagnostics(): Collection
    {
        $missingTables = array_values(array_filter(
            self::REQUIRED_TABLES,
            static fn (string $table): bool => ! Schema::hasTable($table),
        ));
        $missingClasses = array_values(array_filter(
            self::REQUIRED_CLASSES,
            static fn (string $className): bool => ! class_exists($className),
        ));

        return collect([
            new DoctorCheckResultData(
                label: 'Campaign Studio tables',
                passed: $missingTables === [],
                message: $missingTables === []
                    ? 'Required Campaign Studio tables are present.'
                    : sprintf('Missing Campaign Studio table(s): %s.', implode(', ', $missingTables)),
                remediation: $missingTables === [] ? null : 'Run the Campaign Studio migrations.',
            ),
            new DoctorCheckResultData(
                label: 'Campaign Studio runtime classes',
                passed: $missingClasses === [],
                message: $missingClasses === []
                    ? 'Campaign Studio runtime actions and models are loadable.'
                    : sprintf('Missing Campaign Studio class(es): %s.', implode(', ', $missingClasses)),
                remediation: $missingClasses === [] ? null : 'Refresh Composer autoloading and verify the package files are installed.',
            ),
        ]);
    }
}
