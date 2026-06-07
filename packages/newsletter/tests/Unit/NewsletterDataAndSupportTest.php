<?php

declare(strict_types=1);

use Capell\Core\Models\Site;
use Capell\Newsletter\Actions\ParseSubscriberCsvRowsAction;
use Capell\Newsletter\Actions\SyncNewsletterSubscriberContactAction;
use Capell\Newsletter\Contracts\NewsletterAudienceProvider;
use Capell\Newsletter\Data\ConsentEvidenceData;
use Capell\Newsletter\Data\FormMappingData;
use Capell\Newsletter\Data\ProviderAudienceData;
use Capell\Newsletter\Data\ProviderInterestData;
use Capell\Newsletter\Data\ProviderSubscriberData;
use Capell\Newsletter\Data\ProviderSyncResultData;
use Capell\Newsletter\Data\ProviderWebhookEventData;
use Capell\Newsletter\Data\SubscriberData;
use Capell\Newsletter\Enums\AuthType;
use Capell\Newsletter\Enums\ConfirmationMode;
use Capell\Newsletter\Enums\ConsentEventType;
use Capell\Newsletter\Enums\NewsletterSendStatus;
use Capell\Newsletter\Enums\ProviderType;
use Capell\Newsletter\Enums\ResubscribePolicy;
use Capell\Newsletter\Enums\SegmentType;
use Capell\Newsletter\Enums\SubscriberStatus;
use Capell\Newsletter\Enums\SyncStatus;
use Capell\Newsletter\Health\NewsletterHealthCheck;
use Capell\Newsletter\Models\Segment;
use Capell\Newsletter\Settings\NewsletterSettings;
use Capell\Newsletter\Support\CapellNewsletterManager;
use Capell\Newsletter\Support\NewsletterAudienceRegistry;
use Capell\Newsletter\Support\NewsletterSettingsResolver;
use Capell\Newsletter\Support\SegmentAudienceProvider;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

it('maps newsletter data objects across snake case boundaries', function (): void {
    $subscriber = SubscriberData::from([
        'site_id' => 12,
        'email' => 'ada@example.com',
        'status' => 'subscribed',
        'first_name' => 'Ada',
        'last_name' => 'Lovelace',
        'profile' => ['role' => 'editor'],
        'source_form_id' => 44,
        'source_form_handle' => 'newsletter-footer',
    ]);
    $consent = ConsentEvidenceData::from([
        'source_type' => 'form',
        'source_id' => 'footer',
        'consent_text' => 'Send me updates',
        'consent_version' => 'v2',
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Pest',
        'url' => 'https://capell.test',
        'referer' => 'https://referer.test',
        'extra' => ['checkbox' => true],
    ]);
    $webhook = ProviderWebhookEventData::from([
        'email' => 'ada@example.com',
        'status' => 'unsubscribed',
        'event_type' => 'unsubscribe',
        'remote_id' => 'remote-1',
        'payload' => ['id' => 'remote-1'],
    ]);
    $syncResult = ProviderSyncResultData::from([
        'successful' => false,
        'remote_id' => 'remote-2',
        'remote_status' => 'error',
        'error_message' => 'Rejected',
        'payload' => ['code' => 409],
    ]);

    expect($subscriber->status)->toBe(SubscriberStatus::Subscribed)
        ->and($subscriber->firstName)->toBe('Ada')
        ->and($subscriber->toArray())->toMatchArray([
            'site_id' => 12,
            'source_form_handle' => 'newsletter-footer',
        ])
        ->and($consent->ipAddress)->toBe('127.0.0.1')
        ->and($consent->toArray())->toHaveKey('consent_version', 'v2')
        ->and($webhook->status)->toBe(SubscriberStatus::Unsubscribed)
        ->and($webhook->toArray())->toHaveKey('event_type', 'unsubscribe')
        ->and($syncResult->successful)->toBeFalse()
        ->and($syncResult->toArray())->toHaveKey('error_message', 'Rejected');
});

it('declares contacts source adapter capabilities in the package manifest', function (): void {
    $manifest = json_decode(
        (string) file_get_contents(__DIR__ . '/../../capell.json'),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    expect($manifest['dependencies']['supports'] ?? [])->toContain('capell-app/contacts')
        ->and($manifest['actions'])->toHaveKey(
            'syncNewsletterSubscriberContact',
            SyncNewsletterSubscriberContactAction::class,
        )
        ->and($manifest['capabilities'])->toContain('newsletter-contacts-source-adapter');
});

it('declares customer portal preference feed capabilities in the package manifest', function (): void {
    $manifest = json_decode(
        (string) file_get_contents(__DIR__ . '/../../capell.json'),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    expect($manifest['dependencies']['supports'] ?? [])->toContain('capell-app/customer-portal')
        ->and($manifest['capabilities'])->toContain('newsletter-customer-portal-preferences-feed')
        ->and($manifest['capabilities'])->toContain('newsletter-public-preference-center');
});

it('maps provider and form integration data objects', function (): void {
    $audience = ProviderAudienceData::from([
        'remote_id' => 'aud-1',
        'name' => 'Main Audience',
        'settings' => ['region' => 'eu'],
    ]);
    $interest = ProviderInterestData::from([
        'tag_id' => 5,
        'remote_id' => 'int-1',
        'remote_type' => 'interest',
        'name' => 'Product',
    ]);
    $providerSubscriber = ProviderSubscriberData::from([
        'email' => 'ada@example.com',
        'status' => 'subscribed',
        'remote_id' => 'remote-1',
        'first_name' => 'Ada',
        'profile' => ['company' => 'Capell'],
        'interests' => [
            [
                'tag_id' => 5,
                'remote_id' => 'int-1',
                'remote_type' => 'interest',
                'name' => 'Product',
            ],
        ],
    ]);
    $mapping = FormMappingData::from([
        'site_id' => 11,
        'email_field' => 'email',
        'form_id' => 9,
        'form_handle' => 'footer-newsletter',
        'first_name_field' => 'first_name',
        'last_name_field' => 'last_name',
        'consent_field' => 'consent',
        'consent_text' => 'Send me updates',
        'fixed_tag_ids' => [4, 5],
        'field_tag_mappings' => ['interest' => ['product' => 5]],
        'requires_double_opt_in' => false,
        'confirmation_mode' => 'provider_owned',
    ]);

    expect($audience->toArray())->toMatchArray(['remote_id' => 'aud-1', 'settings' => ['region' => 'eu']])
        ->and($interest->toArray())->toMatchArray(['tag_id' => 5, 'remote_type' => 'interest'])
        ->and($providerSubscriber->status)->toBe(SubscriberStatus::Subscribed)
        ->and($providerSubscriber->toArray())->toHaveKey('remote_id', 'remote-1')
        ->and($mapping->toArray())->toMatchArray([
            'first_name_field' => 'first_name',
            'fixed_tag_ids' => [4, 5],
            'requires_double_opt_in' => false,
            'confirmation_mode' => 'provider_owned',
        ]);
});

it('parses subscriber CSV rows while preserving empty optional cells', function (): void {
    $rows = ParseSubscriberCsvRowsAction::run(<<<'CSV'
email, first_name,last_name,,tags
ada@example.com,Ada,Lovelace,,product
"grace@example.com","Grace",,,"engineering, updates"

CSV);

    expect($rows)->toBe([
        [
            'email' => 'ada@example.com',
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'tags' => 'product',
        ],
        [
            'email' => 'grace@example.com',
            'first_name' => 'Grace',
            'last_name' => '',
            'tags' => 'engineering, updates',
        ],
    ]);
});

it('rejects subscriber CSV imports above the configured row limit', function (): void {
    config()->set('capell-newsletter.imports.max_rows', 1);

    ParseSubscriberCsvRowsAction::run(<<<'CSV'
email,first_name
ada@example.com,Ada
grace@example.com,Grace
CSV);
})->throws(ValidationException::class);

it('aggregates newsletter audiences from registered providers in registration order', function (): void {
    $registry = new NewsletterAudienceRegistry;

    $registry->register(new class implements NewsletterAudienceProvider
    {
        public function audiencesForSite(int $siteId): Collection
        {
            return collect([
                ['site_id' => $siteId, 'name' => 'Primary'],
            ]);
        }
    });
    $registry->register(new class implements NewsletterAudienceProvider
    {
        public function audiencesForSite(int $siteId): Collection
        {
            return collect([
                ['site_id' => $siteId, 'name' => 'Secondary'],
            ]);
        }
    });

    expect($registry->audiencesForSite(7)->all())->toBe([
        ['site_id' => 7, 'name' => 'Primary'],
        ['site_id' => 7, 'name' => 'Secondary'],
    ]);
});

it('defines newsletter package metadata and enum labels', function (): void {
    expect(NewsletterHealthCheck::compatibleCapellApiVersion())->toBe('^4.0')
        ->and(CapellNewsletterManager::getMigrations())->toContain(
            '2026_05_10_190861_02_create_newsletter_subscribers_table',
            '2026_05_10_190861_11_create_newsletter_import_batches_table',
            '2026_05_31_120000_13_create_newsletter_sends_table',
        )
        ->and(SubscriberStatus::Subscribed->isSendable())->toBeTrue()
        ->and(SubscriberStatus::Pending->isSendable())->toBeFalse()
        ->and(AuthType::ApiKey->getLabel())->toBe('capell-newsletter::generic.auth_type.api_key')
        ->and(ConfirmationMode::ProviderOwned->getLabel())->toBe('capell-newsletter::generic.confirmation_mode.provider_owned')
        ->and(ConsentEventType::ProviderWebhook->getLabel())->toBe('capell-newsletter::generic.consent_event_type.provider_webhook')
        ->and(ProviderType::Mailchimp->getLabel())->toBe('capell-newsletter::generic.provider.mailchimp')
        ->and(NewsletterSendStatus::Scheduled->getLabel())->toBe('Scheduled')
        ->and(ResubscribePolicy::RequireDoubleOptIn->getLabel())->toBe('capell-newsletter::generic.resubscribe_policy.require_double_opt_in')
        ->and(SegmentType::SavedFilter->getLabel())->toBe('capell-newsletter::generic.segment_type.saved_filter')
        ->and(SyncStatus::RetryScheduled->getLabel())->toBe('Retry scheduled')
        ->and(SyncStatus::Exhausted->getLabel())->toBe('Exhausted');
});

it('resolves newsletter resubscribe policy from settings with safe fallback', function (): void {
    $settings = new NewsletterSettings;
    $settings->default_resubscribe_policy = ResubscribePolicy::AllowWithConsent->value;
    $settings->site_resubscribe_policies = [
        '123' => ResubscribePolicy::BlockSuppressedOnly->value,
        '124' => 'unknown',
    ];

    app()->instance(NewsletterSettings::class, $settings);

    expect((new NewsletterSettingsResolver)->resubscribePolicyForSite(123))
        ->toBe(ResubscribePolicy::BlockSuppressedOnly)
        ->and((new NewsletterSettingsResolver)->resubscribePolicyForSite(124))
        ->toBe(ResubscribePolicy::RequireDoubleOptIn)
        ->and((new NewsletterSettingsResolver)->resubscribePolicyForSite(125))
        ->toBe(ResubscribePolicy::AllowWithConsent);

    $settings->default_resubscribe_policy = 'unknown';

    expect((new NewsletterSettingsResolver)->resubscribePolicyForSite(123))
        ->toBe(ResubscribePolicy::BlockSuppressedOnly)
        ->and((new NewsletterSettingsResolver)->resubscribePolicyForSite(125))
        ->toBe(ResubscribePolicy::RequireDoubleOptIn);
});

it('lists active newsletter segments as audiences for a site', function (): void {
    $site = Site::factory()->create();

    Segment::query()->create([
        'site_id' => $site->getKey(),
        'name' => 'Beta',
        'handle' => 'beta',
        'type' => SegmentType::Static,
        'filters' => [],
        'is_active' => true,
    ]);
    Segment::query()->create([
        'site_id' => $site->getKey(),
        'name' => 'Alpha',
        'handle' => 'alpha',
        'type' => SegmentType::Static,
        'filters' => [],
        'is_active' => true,
    ]);
    Segment::query()->create([
        'site_id' => $site->getKey(),
        'name' => 'Inactive',
        'handle' => 'inactive',
        'type' => SegmentType::Static,
        'filters' => [],
        'is_active' => false,
    ]);

    expect((new SegmentAudienceProvider)->audiencesForSite((int) $site->getKey())->pluck('handle')->all())
        ->toBe(['alpha', 'beta']);
});
