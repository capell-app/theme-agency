<?php

declare(strict_types=1);

use Capell\Newsletter\Data\ConsentEvidenceData;
use Capell\Newsletter\Data\FormMappingData;
use Capell\Newsletter\Data\PreferenceCenterData;
use Capell\Newsletter\Data\PreferenceCenterSegmentData;
use Capell\Newsletter\Data\PreferenceCenterUpdateData;
use Capell\Newsletter\Data\ProviderAudienceData;
use Capell\Newsletter\Data\ProviderInterestData;
use Capell\Newsletter\Data\ProviderSubscriberData;
use Capell\Newsletter\Data\ProviderSyncResultData;
use Capell\Newsletter\Data\ProviderWebhookEventData;
use Capell\Newsletter\Data\SubscriberData;
use Capell\Newsletter\Enums\AuthType;
use Capell\Newsletter\Enums\ConfirmationMode;
use Capell\Newsletter\Enums\ConsentEventType;
use Capell\Newsletter\Enums\ImportBatchStatus;
use Capell\Newsletter\Enums\ImportBatchType;
use Capell\Newsletter\Enums\ProviderType;
use Capell\Newsletter\Enums\PublicTokenType;
use Capell\Newsletter\Enums\ResubscribePolicy;
use Capell\Newsletter\Enums\SegmentType;
use Capell\Newsletter\Enums\SubscriberStatus;
use Capell\Newsletter\Enums\SyncStatus;

it('keeps newsletter subscriber and provider data typed across boundaries', function (): void {
    $interest = new ProviderInterestData(5, 'remote-interest', 'group', 'Product updates');
    $subscriber = new SubscriberData(1, 'ben@example.com', SubscriberStatus::Subscribed, 'Ben', profile: ['role' => 'admin']);
    $providerSubscriber = new ProviderSubscriberData(
        email: 'ben@example.com',
        status: SubscriberStatus::Subscribed,
        firstName: 'Ben',
        interests: [$interest],
        remoteId: 'remote-subscriber',
    );
    $mapping = new FormMappingData(1, 'email', fixedTagIds: [5], requiresDoubleOptIn: false);
    $consent = new ConsentEvidenceData('form', '42', 'I agree', ipAddress: '127.0.0.1');
    $preferenceCenter = new PreferenceCenterData(
        subscriberId: 1,
        siteId: 1,
        email: 'ben@example.com',
        status: SubscriberStatus::Subscribed,
        canReceiveNewsletter: true,
        segments: [new PreferenceCenterSegmentData(5, 'Product updates', 'product-updates', true)],
    );
    $preferenceUpdate = new PreferenceCenterUpdateData(segmentHandles: ['product-updates']);

    expect($subscriber->status->isSendable())->toBeTrue()
        ->and(SubscriberStatus::Pending->isSendable())->toBeFalse()
        ->and($providerSubscriber->interests[0]->remoteId)->toBe('remote-interest')
        ->and($mapping->confirmationMode)->toBe(ConfirmationMode::CapellOwned)
        ->and($consent->sourceType)->toBe('form')
        ->and(new ProviderAudienceData('aud-1', 'Main'))->remoteId->toBe('aud-1')
        ->and(new ProviderSyncResultData(false, errorMessage: 'No'))->errorMessage->toBe('No')
        ->and($preferenceCenter->segments[0]->selected)->toBeTrue()
        ->and($preferenceUpdate->segmentHandles)->toBe(['product-updates'])
        ->and(new ProviderWebhookEventData('ben@example.com', SubscriberStatus::Unsubscribed, 'unsubscribe'))->eventType
        ->toBe('unsubscribe');
});

it('exposes newsletter labels and persisted enum values', function (): void {
    expect(AuthType::ApiKey->getLabel())->toBeString()
        ->and(ConfirmationMode::ProviderOwned->getLabel())->toBeString()
        ->and(ConsentEventType::ProviderWebhook->getLabel())->toBeString()
        ->and(ProviderType::Mailchimp->getLabel())->toBeString()
        ->and(ResubscribePolicy::AllowWithConsent->getLabel())->toBeString()
        ->and(SegmentType::SavedFilter->getLabel())->toBeString()
        ->and(SyncStatus::RetryScheduled->getLabel())->toBeString()
        ->and(ImportBatchStatus::DryRun->value)->toBe('dry_run')
        ->and(ImportBatchStatus::Processing->value)->toBe('processing')
        ->and(ImportBatchType::Export->value)->toBe('export')
        ->and(PublicTokenType::PreferenceCenter->value)->toBe('preference_center')
        ->and(PublicTokenType::Unsubscribe->value)->toBe('unsubscribe');
});
