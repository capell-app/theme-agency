<?php

declare(strict_types=1);

namespace Capell\Newsletter\Support\Providers;

use Capell\Newsletter\Contracts\NewsletterProviderAdapter;
use Capell\Newsletter\Data\ProviderAudienceData;
use Capell\Newsletter\Data\ProviderSubscriberData;
use Capell\Newsletter\Data\ProviderSyncResultData;
use Capell\Newsletter\Data\ProviderWebhookEventData;
use Capell\Newsletter\Models\ProviderAudience;
use Capell\Newsletter\Models\ProviderConnection;
use Illuminate\Http\Request;
use LogicException;

class FakeProviderAdapter implements NewsletterProviderAdapter
{
    public function supportsOAuth(): bool
    {
        return false;
    }

    public function supportsProviderOwnedConfirmation(): bool
    {
        return true;
    }

    public function listAudiences(ProviderConnection $connection): array
    {
        $this->guardAllowed();

        return [
            new ProviderAudienceData(remoteId: 'fake-audience', name: 'Fake Audience'),
        ];
    }

    public function syncSubscriber(
        ProviderConnection $connection,
        ProviderAudience $audience,
        ProviderSubscriberData $subscriber,
    ): ProviderSyncResultData {
        $this->guardAllowed();

        return new ProviderSyncResultData(
            successful: true,
            remoteId: 'fake-' . hash('xxh3', $subscriber->email),
            remoteStatus: $subscriber->status->value,
            payload: $subscriber->toArray(),
        );
    }

    public function verifyWebhook(ProviderConnection $connection, Request $request): bool
    {
        return FakeProviderGuard::isAllowed();
    }

    public function normalizeWebhook(ProviderConnection $connection, Request $request): ?ProviderWebhookEventData
    {
        $this->guardAllowed();

        return ProviderWebhookEventData::from($request->all());
    }

    private function guardAllowed(): void
    {
        if (FakeProviderGuard::isAllowed()) {
            return;
        }

        throw new LogicException('The fake newsletter provider is disabled for this environment.');
    }
}
