<?php

declare(strict_types=1);

use Capell\PrivacyCenter\Actions\AnonymizePrivacySubjectAction;
use Capell\PrivacyCenter\Actions\BuildPrivacyExportAction;
use Capell\PrivacyCenter\Actions\CreateRetentionRuleAction;
use Capell\PrivacyCenter\Actions\MarkPrivacyRequestFulfilledAction;
use Capell\PrivacyCenter\Actions\OpenPrivacyRequestAction;
use Capell\PrivacyCenter\Actions\RecordConsentAction;
use Capell\PrivacyCenter\Actions\RecordPolicyAcceptanceAction;
use Capell\PrivacyCenter\Actions\RegisterConsentPolicyAction;
use Capell\PrivacyCenter\Data\ConsentPolicyData;
use Capell\PrivacyCenter\Data\ConsentRecordData;
use Capell\PrivacyCenter\Data\PolicyAcceptanceData;
use Capell\PrivacyCenter\Data\PrivacyRequestData;
use Capell\PrivacyCenter\Data\RetentionRuleData;
use Capell\PrivacyCenter\Enums\ConsentDecision;
use Capell\PrivacyCenter\Enums\CookieCategory;
use Capell\PrivacyCenter\Enums\PolicyType;
use Capell\PrivacyCenter\Enums\PrivacyRequestStatus;
use Capell\PrivacyCenter\Enums\PrivacyRequestType;
use Capell\PrivacyCenter\Enums\RetentionAction;
use Capell\PrivacyCenter\Models\ConsentRecord;
use Capell\PrivacyCenter\Models\PolicyAcceptance;
use Capell\PrivacyCenter\Models\PrivacyRequest;
use Capell\PrivacyCenter\Tests\PrivacyCenterTestCase;
use Capell\PrivacyCenter\Tests\PrivacyCenterTestSubject;
use Illuminate\Support\Facades\Schema;

require_once dirname(__DIR__) . '/autoload.php';

uses(PrivacyCenterTestCase::class);

it('loads the privacy center foundation tables', function (): void {
    expect(Schema::hasTable('privacy_consent_policies'))->toBeTrue()
        ->and(Schema::hasTable('privacy_consent_records'))->toBeTrue()
        ->and(Schema::hasTable('privacy_policy_acceptances'))->toBeTrue()
        ->and(Schema::hasTable('privacy_retention_rules'))->toBeTrue()
        ->and(Schema::hasTable('privacy_requests'))->toBeTrue();
});

it('records consent policy acceptance privacy requests retention and export workflows', function (): void {
    request()->server->set('REMOTE_ADDR', '203.0.113.42');
    request()->headers->set('User-Agent', 'Privacy Center Test Browser');

    $siteId = $this->createPrivacyCenterSite();
    $subject = PrivacyCenterTestSubject::query()->create(['name' => 'Ada Example']);

    $policy = RegisterConsentPolicyAction::run(new ConsentPolicyData(
        key: 'privacy',
        version: '2026-05-31',
        title: 'Privacy Policy',
        type: PolicyType::Privacy,
        siteId: $siteId,
        contentHash: str_repeat('a', 64),
        publishedAt: now(),
    ));

    $consent = RecordConsentAction::run(new ConsentRecordData(
        category: CookieCategory::Analytics,
        decision: ConsentDecision::Granted,
        siteId: $siteId,
        policyId: (int) $policy->getKey(),
        policyVersion: $policy->version,
        jurisdiction: 'GB',
        evidence: ['surface' => 'cookie-banner'],
    ), $subject);

    $acceptance = RecordPolicyAcceptanceAction::run(new PolicyAcceptanceData(
        policyKey: $policy->key,
        policyVersion: $policy->version,
        policyType: $policy->type,
        siteId: $siteId,
        policyId: (int) $policy->getKey(),
        context: 'registration',
    ), $subject);

    $retentionRule = CreateRetentionRuleAction::run(new RetentionRuleData(
        dataDomain: 'privacy-center',
        retentionDays: 365,
        siteId: $siteId,
        recordType: ConsentRecord::class,
        action: RetentionAction::Anonymize,
        legalBasis: 'consent-audit',
    ));

    $privacyRequest = OpenPrivacyRequestAction::run(new PrivacyRequestData(
        type: PrivacyRequestType::Export,
        siteId: $siteId,
        email: 'Ada@Example.test',
        workflowPayload: ['source' => 'self-service'],
    ), subject: $subject);

    $export = BuildPrivacyExportAction::run($subject);
    $fulfilledRequest = MarkPrivacyRequestFulfilledAction::run($privacyRequest, ['exported' => true]);

    expect($consent->category)->toBe(CookieCategory::Analytics)
        ->and($consent->decision)->toBe(ConsentDecision::Granted)
        ->and($consent->subject_type)->toBe(PrivacyCenterTestSubject::class)
        ->and($consent->ip_hash)->toHaveLength(64)
        ->and($consent->user_agent_hash)->toHaveLength(64)
        ->and($acceptance->policy_key)->toBe('privacy')
        ->and($acceptance->accepted_at)->not->toBeNull()
        ->and($retentionRule->action)->toBe(RetentionAction::Anonymize)
        ->and($privacyRequest->reference)->toStartWith('PR-')
        ->and($privacyRequest->email_hash)->toHaveLength(64)
        ->and($fulfilledRequest->status)->toBe(PrivacyRequestStatus::Fulfilled)
        ->and($export->isEmpty())->toBeFalse()
        ->and($export->consentRecords)->toHaveCount(1)
        ->and($export->policyAcceptances)->toHaveCount(1)
        ->and($export->privacyRequests)->toHaveCount(1)
        ->and(array_key_exists('ip_hash', $export->consentRecords[0]))->toBeFalse()
        ->and(array_key_exists('email_hash', $export->privacyRequests[0]))->toBeFalse();
});

it('anonymizes package-owned subject links for delete workflows', function (): void {
    $siteId = $this->createPrivacyCenterSite();
    $subject = PrivacyCenterTestSubject::query()->create(['name' => 'Delete Me']);

    RecordConsentAction::run(new ConsentRecordData(
        category: CookieCategory::Marketing,
        decision: ConsentDecision::Withdrawn,
        siteId: $siteId,
        evidence: ['surface' => 'footer-link'],
    ), $subject);

    RecordPolicyAcceptanceAction::run(new PolicyAcceptanceData(
        policyKey: 'cookie',
        policyVersion: '2026-05-31',
        policyType: PolicyType::Cookie,
        siteId: $siteId,
    ), $subject);

    OpenPrivacyRequestAction::run(new PrivacyRequestData(
        type: PrivacyRequestType::Delete,
        siteId: $siteId,
        email: 'delete@example.test',
    ), subject: $subject);

    $affectedRecords = AnonymizePrivacySubjectAction::run($subject);

    expect($affectedRecords)->toBe(3)
        ->and(ConsentRecord::query()->first()?->subject_type)->toBeNull()
        ->and(PolicyAcceptance::query()->first()?->subject_type)->toBeNull()
        ->and(PrivacyRequest::query()->first()?->email_hash)->toBeNull();
});
