<?php

declare(strict_types=1);

use Capell\Core\Contracts\Extensions\RegistersExtensionAdminResource;
use Capell\Core\Contracts\Extensions\RegistersExtensionWidget;
use Capell\Core\Contracts\Extensions\RunsScheduledExtensionJob;
use Capell\PrivacyCenter\Actions\AnonymizePrivacySubjectAction;
use Capell\PrivacyCenter\Actions\ApplyRetentionRulesAction;
use Capell\PrivacyCenter\Actions\BuildPrivacyCenterOverviewStatsAction;
use Capell\PrivacyCenter\Actions\BuildPrivacyExportAction;
use Capell\PrivacyCenter\Actions\CreateRetentionRuleAction;
use Capell\PrivacyCenter\Actions\MarkPrivacyRequestFulfilledAction;
use Capell\PrivacyCenter\Actions\OpenPrivacyRequestAction;
use Capell\PrivacyCenter\Actions\RecordConsentAction;
use Capell\PrivacyCenter\Actions\RecordPolicyAcceptanceAction;
use Capell\PrivacyCenter\Actions\RegisterConsentPolicyAction;
use Capell\PrivacyCenter\Console\Commands\ApplyRetentionRulesCommand;
use Capell\PrivacyCenter\Enums\CookieCategory;
use Capell\PrivacyCenter\Filament\Resources\ConsentPolicies\ConsentPolicyResource;
use Capell\PrivacyCenter\Filament\Resources\ConsentRecords\ConsentRecordResource;
use Capell\PrivacyCenter\Filament\Resources\PolicyAcceptances\PolicyAcceptanceResource;
use Capell\PrivacyCenter\Filament\Resources\PrivacyRequests\PrivacyRequestResource;
use Capell\PrivacyCenter\Filament\Resources\RetentionRules\RetentionRuleResource;
use Capell\PrivacyCenter\Filament\Widgets\PrivacyCenterOverviewWidget;
use Capell\PrivacyCenter\Manifest\ConsentPolicyResourceContribution;
use Capell\PrivacyCenter\Manifest\ConsentRecordResourceContribution;
use Capell\PrivacyCenter\Manifest\PolicyAcceptanceResourceContribution;
use Capell\PrivacyCenter\Manifest\PrivacyCenterModelsContribution;
use Capell\PrivacyCenter\Manifest\PrivacyCenterOverviewWidgetContribution;
use Capell\PrivacyCenter\Manifest\PrivacyRequestResourceContribution;
use Capell\PrivacyCenter\Manifest\PrivacyRetentionScheduleContribution;
use Capell\PrivacyCenter\Manifest\RetentionRuleResourceContribution;
use Capell\PrivacyCenter\Providers\AdminServiceProvider;
use Capell\PrivacyCenter\Tests\PrivacyCenterTestCase;

require_once dirname(__DIR__) . '/autoload.php';

uses(PrivacyCenterTestCase::class);

it('declares privacy center manifest ownership and cache safety', function (): void {
    $manifest = capell_json_file_array(dirname(__DIR__, 2) . '/capell.json');

    expect($manifest)->toBeArray()
        ->and($manifest['name'])->toBe('capell-app/privacy-center')
        ->and($manifest['namespace'])->toBe('Capell\\PrivacyCenter')
        ->and(data_get($manifest, 'database.requiredTables'))->toContain('privacy_consent_records')
        ->and(data_get($manifest, 'performance.frontendRenderBudgetMs'))->toBe(40)
        ->and(data_get($manifest, 'performance.cacheSafety.sensitiveOutput'))->toBeTrue()
        ->and(data_get($manifest, 'dependencies.requires'))->toContain('capell-app/admin')
        ->and(data_get($manifest, 'providers.admin'))->toBe([AdminServiceProvider::class])
        ->and(data_get($manifest, 'commands.retention'))->toBe('privacy:apply-retention')
        ->and(data_get($manifest, 'surfaces'))->toContain('public')
        ->and(data_get($manifest, 'capabilities'))->toContain('privacy-center-cookie-categories')
        ->and(data_get($manifest, 'capabilities'))->toContain(
            'privacy-center-consent',
            'privacy-center-retention',
            'privacy-center-retention-execution',
            'privacy-center-privacy-exports',
            'privacy-center-delete-workflows',
            'privacy-center-policy-acceptance',
            'privacy-center-subject-requests',
        )
        ->and(data_get($manifest, 'contributes'))->toContain([
            'type' => 'admin-resource',
            'class' => ConsentPolicyResourceContribution::class,
            'resourceClass' => ConsentPolicyResource::class,
        ])
        ->and(data_get($manifest, 'contributes'))->toContain([
            'type' => 'admin-resource',
            'class' => ConsentRecordResourceContribution::class,
            'resourceClass' => ConsentRecordResource::class,
        ])
        ->and(data_get($manifest, 'contributes'))->toContain([
            'type' => 'admin-resource',
            'class' => PolicyAcceptanceResourceContribution::class,
            'resourceClass' => PolicyAcceptanceResource::class,
        ])
        ->and(data_get($manifest, 'contributes'))->toContain([
            'type' => 'admin-resource',
            'class' => PrivacyRequestResourceContribution::class,
            'resourceClass' => PrivacyRequestResource::class,
        ])
        ->and(data_get($manifest, 'contributes'))->toContain([
            'type' => 'admin-resource',
            'class' => RetentionRuleResourceContribution::class,
            'resourceClass' => RetentionRuleResource::class,
        ])
        ->and(data_get($manifest, 'contributes'))->toContain([
            'type' => 'dashboard-widget',
            'class' => PrivacyCenterOverviewWidgetContribution::class,
            'widgetClass' => PrivacyCenterOverviewWidget::class,
        ])
        ->and(data_get($manifest, 'contributes'))->toContain([
            'type' => 'model',
            'class' => PrivacyCenterModelsContribution::class,
        ])
        ->and(data_get($manifest, 'contributes'))->toContain([
            'type' => 'scheduled-job',
            'class' => PrivacyRetentionScheduleContribution::class,
            'command' => 'privacy:apply-retention',
            'frequency' => 'daily',
        ])
        ->and(data_get($manifest, 'commands.retention'))->toBe('privacy:apply-retention')
        ->and((new ApplyRetentionRulesCommand)->getName())->toBe('privacy:apply-retention')
        ->and(class_implements(ConsentPolicyResourceContribution::class))->toContain(RegistersExtensionAdminResource::class)
        ->and(class_implements(ConsentRecordResourceContribution::class))->toContain(RegistersExtensionAdminResource::class)
        ->and(class_implements(PolicyAcceptanceResourceContribution::class))->toContain(RegistersExtensionAdminResource::class)
        ->and(class_implements(PrivacyRequestResourceContribution::class))->toContain(RegistersExtensionAdminResource::class)
        ->and(class_implements(RetentionRuleResourceContribution::class))->toContain(RegistersExtensionAdminResource::class)
        ->and(class_implements(PrivacyCenterOverviewWidgetContribution::class))->toContain(RegistersExtensionWidget::class)
        ->and(class_implements(PrivacyRetentionScheduleContribution::class))->toContain(RunsScheduledExtensionJob::class)
        ->and(data_get($manifest, 'actions'))->toMatchArray([
            'anonymizePrivacySubject' => AnonymizePrivacySubjectAction::class,
            'applyRetentionRules' => ApplyRetentionRulesAction::class,
            'buildPrivacyCenterOverviewStats' => BuildPrivacyCenterOverviewStatsAction::class,
            'buildPrivacyExport' => BuildPrivacyExportAction::class,
            'createRetentionRule' => CreateRetentionRuleAction::class,
            'markPrivacyRequestFulfilled' => MarkPrivacyRequestFulfilledAction::class,
            'openPrivacyRequest' => OpenPrivacyRequestAction::class,
            'recordConsent' => RecordConsentAction::class,
            'recordPolicyAcceptance' => RecordPolicyAcceptanceAction::class,
            'registerConsentPolicy' => RegisterConsentPolicyAction::class,
        ])
        ->and(data_get($manifest, 'contributionTraceability.deferredContributions'))->toBe([]);
});

it('declares cookie consent categories', function (): void {
    expect(array_map(
        static fn (CookieCategory $category): string => $category->value,
        CookieCategory::cases(),
    ))->toBe([
        'essential',
        'analytics',
        'marketing',
        'preferences',
        'functional',
    ]);
});

it('documents shipped privacy center surfaces without overclaiming deferred workflows', function (): void {
    $packagePath = dirname(__DIR__, 2);
    $readme = (string) file_get_contents($packagePath . '/README.md');
    $overview = (string) file_get_contents($packagePath . '/docs/overview.md');
    $changelog = (string) file_get_contents($packagePath . '/CHANGELOG.md');

    expect($readme)
        ->toContain('Privacy Center currently ships admin and console surfaces')
        ->toContain('public cookie consent preference center')
        ->toContain('does not ship a public DSAR intake form')
        ->toContain('BuildPrivacyExportAction')
        ->toContain('AnonymizePrivacySubjectAction')
        ->toContain('CAPELL_PRIVACY_CENTER_HASH_SECRET')
        ->toContain('The admin provider contributes these Filament surfaces')
        ->and($overview)
        ->toContain('public cookie consent preference center')
        ->toContain('cross-package subject-data export/erasure registry')
        ->toContain('`RecordConsentAction` can infer a subject from a source model')
        ->toContain('cache-safe public preference center')
        ->and($changelog)
        ->toContain('Expanded README and overview documentation')
        ->and($readme)
        ->not->toContain('generic cookie banner is table-stakes')
        ->not->toContain('one erasure call wipes contacts');
});
