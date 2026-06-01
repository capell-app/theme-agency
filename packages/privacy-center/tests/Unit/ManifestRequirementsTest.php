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
    $manifest = json_decode((string) file_get_contents(dirname(__DIR__, 2) . '/capell.json'), true);

    expect($manifest)->toBeArray()
        ->and($manifest['name'])->toBe('capell-app/privacy-center')
        ->and($manifest['namespace'])->toBe('Capell\\PrivacyCenter')
        ->and($manifest['database']['requiredTables'])->toContain('privacy_consent_records')
        ->and($manifest['performance']['cacheSafety']['sensitiveOutput'])->toBeTrue()
        ->and($manifest['dependencies']['requires'])->toContain('capell-app/admin')
        ->and($manifest['providers']['admin'])->toBe([AdminServiceProvider::class])
        ->and($manifest['capabilities'])->toContain(
            'privacy-center-consent',
            'privacy-center-cookie-categories',
            'privacy-center-retention',
            'privacy-center-retention-execution',
            'privacy-center-privacy-exports',
            'privacy-center-delete-workflows',
            'privacy-center-policy-acceptance',
            'privacy-center-subject-requests',
        )
        ->and($manifest['contributes'])->toContain([
            'type' => 'admin-resource',
            'class' => ConsentPolicyResourceContribution::class,
            'resourceClass' => ConsentPolicyResource::class,
        ])
        ->and($manifest['contributes'])->toContain([
            'type' => 'admin-resource',
            'class' => ConsentRecordResourceContribution::class,
            'resourceClass' => ConsentRecordResource::class,
        ])
        ->and($manifest['contributes'])->toContain([
            'type' => 'admin-resource',
            'class' => PolicyAcceptanceResourceContribution::class,
            'resourceClass' => PolicyAcceptanceResource::class,
        ])
        ->and($manifest['contributes'])->toContain([
            'type' => 'admin-resource',
            'class' => PrivacyRequestResourceContribution::class,
            'resourceClass' => PrivacyRequestResource::class,
        ])
        ->and($manifest['contributes'])->toContain([
            'type' => 'admin-resource',
            'class' => RetentionRuleResourceContribution::class,
            'resourceClass' => RetentionRuleResource::class,
        ])
        ->and($manifest['contributes'])->toContain([
            'type' => 'dashboard-widget',
            'class' => PrivacyCenterOverviewWidgetContribution::class,
            'widgetClass' => PrivacyCenterOverviewWidget::class,
        ])
        ->and($manifest['contributes'])->toContain([
            'type' => 'model',
            'class' => PrivacyCenterModelsContribution::class,
        ])
        ->and($manifest['contributes'])->toContain([
            'type' => 'scheduled-job',
            'class' => PrivacyRetentionScheduleContribution::class,
        ])
        ->and(class_implements(ConsentPolicyResourceContribution::class))->toContain(RegistersExtensionAdminResource::class)
        ->and(class_implements(ConsentRecordResourceContribution::class))->toContain(RegistersExtensionAdminResource::class)
        ->and(class_implements(PolicyAcceptanceResourceContribution::class))->toContain(RegistersExtensionAdminResource::class)
        ->and(class_implements(PrivacyRequestResourceContribution::class))->toContain(RegistersExtensionAdminResource::class)
        ->and(class_implements(RetentionRuleResourceContribution::class))->toContain(RegistersExtensionAdminResource::class)
        ->and(class_implements(PrivacyCenterOverviewWidgetContribution::class))->toContain(RegistersExtensionWidget::class)
        ->and(class_implements(PrivacyRetentionScheduleContribution::class))->toContain(RunsScheduledExtensionJob::class)
        ->and($manifest['actions'])->toMatchArray([
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
        ->and($manifest['contributionTraceability']['deferredContributions'])->toBe([]);
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
