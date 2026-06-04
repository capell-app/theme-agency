<?php

declare(strict_types=1);

use Capell\PrivacyCenter\Actions\AnonymizePrivacySubjectAction;
use Capell\PrivacyCenter\Actions\ApplyRetentionRulesAction;
use Capell\PrivacyCenter\Actions\BuildPrivacyExportAction;
use Capell\PrivacyCenter\Actions\CreateRetentionRuleAction;
use Capell\PrivacyCenter\Actions\MarkPrivacyRequestFulfilledAction;
use Capell\PrivacyCenter\Actions\MarkPrivacyRequestVerifiedAction;
use Capell\PrivacyCenter\Actions\OpenPrivacyRequestAction;
use Capell\PrivacyCenter\Actions\RecordConsentAction;
use Capell\PrivacyCenter\Actions\RecordPolicyAcceptanceAction;
use Capell\PrivacyCenter\Actions\RegisterConsentPolicyAction;
use Capell\PrivacyCenter\Actions\RejectPrivacyRequestAction;
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
use Capell\PrivacyCenter\Models\RetentionRule;
use Capell\PrivacyCenter\Tests\Fixtures\PrivacyCenterTestSubject;
use Capell\PrivacyCenter\Tests\PrivacyCenterTestCase;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Date;
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

it('marks privacy requests verified and rejected through workflow actions', function (): void {
    $siteId = $this->createPrivacyCenterSite();
    $submittedAt = CarbonImmutable::parse('2026-06-01 09:00:00', 'UTC');
    $verifiedAt = CarbonImmutable::parse('2026-06-01 10:00:00', 'UTC');
    $rejectedAt = CarbonImmutable::parse('2026-06-01 11:00:00', 'UTC');

    $requestForVerification = OpenPrivacyRequestAction::run(new PrivacyRequestData(
        type: PrivacyRequestType::Access,
        siteId: $siteId,
        email: 'verify@example.test',
        submittedAt: $submittedAt,
    ));
    $requestForRejection = OpenPrivacyRequestAction::run(new PrivacyRequestData(
        type: PrivacyRequestType::Delete,
        siteId: $siteId,
        email: 'reject@example.test',
        submittedAt: $submittedAt,
    ));

    $verifiedRequest = MarkPrivacyRequestVerifiedAction::run($requestForVerification, $verifiedAt);
    $rejectedRequest = RejectPrivacyRequestAction::run($requestForRejection, 'Unable to verify identity.', $rejectedAt);

    expect($verifiedRequest->status)->toBe(PrivacyRequestStatus::Processing)
        ->and($verifiedRequest->verified_at?->equalTo($verifiedAt))->toBeTrue()
        ->and($verifiedRequest->fulfilled_at)->toBeNull()
        ->and($rejectedRequest->status)->toBe(PrivacyRequestStatus::Rejected)
        ->and($rejectedRequest->rejected_at?->equalTo($rejectedAt))->toBeTrue()
        ->and($rejectedRequest->rejection_reason)->toBe('Unable to verify identity.')
        ->and($rejectedRequest->fulfilled_at)->toBeNull();
});

it('applies active retention rules to expired privacy records', function (): void {
    request()->server->set('REMOTE_ADDR', '203.0.113.42');
    request()->headers->set('User-Agent', 'Privacy Center Test Browser');

    $siteId = $this->createPrivacyCenterSite();
    $subject = PrivacyCenterTestSubject::query()->create(['name' => 'Retention Subject']);
    $now = Date::parse('2026-05-31 12:00:00', 'UTC');

    $oldConsent = RecordConsentAction::run(new ConsentRecordData(
        category: CookieCategory::Analytics,
        decision: ConsentDecision::Granted,
        siteId: $siteId,
        decidedAt: $now->copy()->subDays(45),
        evidence: ['surface' => 'old-banner'],
    ), $subject);
    $recentConsent = RecordConsentAction::run(new ConsentRecordData(
        category: CookieCategory::Marketing,
        decision: ConsentDecision::Granted,
        siteId: $siteId,
        decidedAt: $now->copy()->subDays(5),
        evidence: ['surface' => 'recent-banner'],
    ), $subject);
    CreateRetentionRuleAction::run(new RetentionRuleData(
        dataDomain: 'privacy-center',
        retentionDays: 30,
        siteId: $siteId,
        recordType: ConsentRecord::class,
        action: RetentionAction::Anonymize,
    ));

    $results = ApplyRetentionRulesAction::run($now);

    expect($results)->toHaveCount(1)
        ->and($results->first()?->matchedRecords)->toBe(1)
        ->and($results->first()?->affectedRecords)->toBe(1)
        ->and($oldConsent->refresh()->subject_type)->toBeNull()
        ->and($oldConsent->ip_hash)->toBeNull()
        ->and($oldConsent->evidence)->toBeNull()
        ->and($recentConsent->refresh()->subject_type)->toBe(PrivacyCenterTestSubject::class)
        ->and($recentConsent->evidence)->toBe(['surface' => 'recent-banner']);
});

it('runs active retention rules through the console command', function (): void {
    request()->server->set('REMOTE_ADDR', '203.0.113.42');
    request()->headers->set('User-Agent', 'Privacy Center Retention Command Test');

    $siteId = $this->createPrivacyCenterSite();
    $subject = PrivacyCenterTestSubject::query()->create(['name' => 'Command Retention Subject']);
    $now = CarbonImmutable::parse('2026-05-31 12:00:00', 'UTC');

    CarbonImmutable::setTestNow($now);

    try {
        $oldConsent = RecordConsentAction::run(new ConsentRecordData(
            category: CookieCategory::Analytics,
            decision: ConsentDecision::Granted,
            siteId: $siteId,
            decidedAt: $now->subDays(45),
            evidence: ['surface' => 'old-banner'],
        ), $subject);
        RecordConsentAction::run(new ConsentRecordData(
            category: CookieCategory::Marketing,
            decision: ConsentDecision::Granted,
            siteId: $siteId,
            decidedAt: $now->subDays(5),
            evidence: ['surface' => 'recent-banner'],
        ), $subject);
        CreateRetentionRuleAction::run(new RetentionRuleData(
            dataDomain: 'privacy-center',
            retentionDays: 30,
            siteId: $siteId,
            recordType: ConsentRecord::class,
            action: RetentionAction::Anonymize,
        ));

        $exitCode = Artisan::call('privacy:apply-retention', ['--json' => true]);
        $rows = json_decode(Artisan::output(), associative: true, flags: JSON_THROW_ON_ERROR);

        expect($exitCode)->toBe(0)
            ->and($rows)->toBeArray()
            ->and($rows[0]['matched_records'] ?? null)->toBe(1)
            ->and($rows[0]['affected_records'] ?? null)->toBe(1)
            ->and($oldConsent->refresh()->subject_type)->toBeNull()
            ->and($oldConsent->ip_hash)->toBeNull()
            ->and($oldConsent->evidence)->toBeNull();
    } finally {
        CarbonImmutable::setTestNow();
    }
});

it('marks expired privacy records for retention review without deleting them', function (): void {
    $siteId = $this->createPrivacyCenterSite();
    $now = Date::parse('2026-05-31 12:00:00', 'UTC');
    $request = OpenPrivacyRequestAction::run(new PrivacyRequestData(
        type: PrivacyRequestType::Export,
        siteId: $siteId,
        email: 'review@example.test',
        submittedAt: $now->copy()->subDays(60),
    ));

    CreateRetentionRuleAction::run(new RetentionRuleData(
        dataDomain: 'privacy-requests',
        retentionDays: 30,
        siteId: $siteId,
        recordType: PrivacyRequest::class,
        action: RetentionAction::Review,
    ));

    ApplyRetentionRulesAction::run($now);

    $metadata = $request->refresh()->metadata;

    expect($metadata['retention_review']['data_domain'] ?? null)->toBe('privacy-requests')
        ->and($metadata['retention_review']['rule_id'] ?? null)->toBe(RetentionRule::query()->value('id'))
        ->and($metadata['retention_review']['marked_at'] ?? null)->toBeString()
        ->and(RetentionRule::query()->count())->toBe(1);
});
