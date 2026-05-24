<?php

declare(strict_types=1);

namespace Capell\Newsletter\Support\Providers;

use Capell\Newsletter\Contracts\NewsletterProviderAdapter;
use Capell\Newsletter\Data\ProviderAudienceData;
use Capell\Newsletter\Data\ProviderSubscriberData;
use Capell\Newsletter\Data\ProviderSyncResultData;
use Capell\Newsletter\Data\ProviderWebhookEventData;
use Capell\Newsletter\Enums\SubscriberStatus;
use Capell\Newsletter\Models\ProviderAudience;
use Capell\Newsletter\Models\ProviderConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CampaignMonitorProviderAdapter implements NewsletterProviderAdapter
{
    public function supportsOAuth(): bool
    {
        return true;
    }

    public function supportsProviderOwnedConfirmation(): bool
    {
        return true;
    }

    public function listAudiences(ProviderConnection $connection): array
    {
        $clientId = $this->credentials($connection)['client_id'] ?? null;

        if (! is_string($clientId) || $clientId === '') {
            return [];
        }

        $response = Http::withBasicAuth($this->apiKey($connection), '')
            ->timeout((int) config('capell-newsletter.http.timeout', 15))
            ->retry(
                (int) config('capell-newsletter.http.retry_times', 3),
                (int) config('capell-newsletter.http.retry_delay_ms', 500),
                throw: false,
            )
            ->get('https://api.createsend.com/api/v3.3/clients/' . $clientId . '/lists.json');

        if (! $response->successful()) {
            return [];
        }

        $audiences = $response->json();

        return collect(is_array($audiences) ? $audiences : [])
            ->filter(static fn (mixed $audience): bool => is_array($audience))
            ->map(static fn (array $audience): ProviderAudienceData => new ProviderAudienceData(
                remoteId: (string) ($audience['ListID'] ?? ''),
                name: (string) ($audience['Name'] ?? ''),
                settings: $audience,
            ))
            ->filter(static fn (ProviderAudienceData $audience): bool => $audience->remoteId !== '')
            ->values()
            ->all();
    }

    public function syncSubscriber(
        ProviderConnection $connection,
        ProviderAudience $audience,
        ProviderSubscriberData $subscriber,
    ): ProviderSyncResultData {
        $payload = [
            'EmailAddress' => $subscriber->email,
            'Name' => trim(implode(' ', array_filter(
                [$subscriber->firstName, $subscriber->lastName],
                static fn (?string $name): bool => $name !== null && $name !== '',
            ))),
            'Resubscribe' => $subscriber->status === SubscriberStatus::Subscribed,
            'ConsentToTrack' => 'Unchanged',
            'CustomFields' => collect($subscriber->profile)
                ->map(static fn (mixed $value, string $key): array => [
                    'Key' => $key,
                    'Value' => is_scalar($value) ? (string) $value : '',
                ])
                ->values()
                ->all(),
        ];

        $response = Http::withBasicAuth($this->apiKey($connection), '')
            ->timeout((int) config('capell-newsletter.http.timeout', 15))
            ->retry(
                (int) config('capell-newsletter.http.retry_times', 3),
                (int) config('capell-newsletter.http.retry_delay_ms', 500),
                throw: false,
            )
            ->post('https://api.createsend.com/api/v3.3/subscribers/' . $audience->remote_id . '.json', $payload);

        return new ProviderSyncResultData(
            successful: $response->successful(),
            remoteId: $subscriber->email,
            remoteStatus: $subscriber->status->value,
            errorMessage: $response->successful() ? null : $response->body(),
            payload: $payload,
        );
    }

    public function verifyWebhook(ProviderConnection $connection, Request $request): bool
    {
        $secret = $connection->webhook_secret;

        if (! is_string($secret) || $secret === '') {
            return false;
        }

        $headerName = (string) config('capell-newsletter.webhooks.signature_headers.campaign_monitor', 'X-CM-Signature');
        $signature = (string) $request->header($headerName);

        if ($signature !== '') {
            $expected = hash_hmac('sha256', $request->getContent(), $secret);

            if (str_starts_with($signature, 'sha256=')) {
                $signature = substr($signature, 7);
            }

            return hash_equals($expected, $signature);
        }

        return hash_equals($secret, (string) $request->query('secret'));
    }

    public function normalizeWebhook(ProviderConnection $connection, Request $request): ?ProviderWebhookEventData
    {
        $email = $request->input('Events.0.EmailAddress') ?? $request->input('EmailAddress');

        if (! is_string($email) || trim($email) === '') {
            return null;
        }

        $eventName = (string) ($request->input('Events.0.Type') ?? $request->input('Type', 'Update'));

        return new ProviderWebhookEventData(
            email: $email,
            status: match (mb_strtolower($eventName)) {
                'unsubscribe' => SubscriberStatus::Unsubscribed,
                'bounce' => SubscriberStatus::Bounced,
                default => SubscriberStatus::Subscribed,
            },
            eventType: $eventName,
            remoteId: $email,
            payload: $request->all(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function credentials(ProviderConnection $connection): array
    {
        return is_array($connection->credentials) ? $connection->credentials : [];
    }

    private function apiKey(ProviderConnection $connection): string
    {
        return (string) ($this->credentials($connection)['api_key'] ?? '');
    }
}
