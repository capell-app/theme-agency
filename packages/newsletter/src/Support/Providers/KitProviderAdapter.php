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

class KitProviderAdapter implements NewsletterProviderAdapter
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
        $response = ProviderHttpRetry::apply(
            Http::withHeaders($this->headers($connection))
                ->timeout((int) config('capell-newsletter.http.timeout', 15)),
        )
            ->get('https://api.kit.com/v4/forms');

        if (! $response->successful()) {
            return [];
        }

        $audiences = $response->json('forms', []);

        return collect(is_array($audiences) ? $audiences : [])
            ->filter(static fn (mixed $audience): bool => is_array($audience))
            ->map(static fn (array $audience): ProviderAudienceData => new ProviderAudienceData(
                remoteId: (string) ($audience['id'] ?? ''),
                name: (string) ($audience['name'] ?? ''),
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
            'email_address' => $subscriber->email,
            'first_name' => $subscriber->firstName,
            'fields' => array_filter([
                'last_name' => $subscriber->lastName,
                ...$subscriber->profile,
            ], static fn (mixed $value): bool => $value !== null && $value !== ''),
        ];

        $response = ProviderHttpRetry::apply(
            Http::withHeaders($this->headers($connection))
                ->timeout((int) config('capell-newsletter.http.timeout', 15)),
        )
            ->post('https://api.kit.com/v4/forms/' . $audience->remote_id . '/subscribers', $payload);

        foreach ($subscriber->interests as $interest) {
            ProviderHttpRetry::apply(
                Http::withHeaders($this->headers($connection))
                    ->timeout((int) config('capell-newsletter.http.timeout', 15)),
            )
                ->post('https://api.kit.com/v4/tags/' . $interest->remoteId . '/subscribers', [
                    'email_address' => $subscriber->email,
                ]);
        }

        return new ProviderSyncResultData(
            successful: $response->successful(),
            remoteId: is_string($response->json('subscriber.id')) ? $response->json('subscriber.id') : null,
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

        $secretHeader = (string) $request->header('X-Kit-Webhook-Secret');
        if ($secretHeader !== '') {
            return hash_equals($secret, $secretHeader);
        }

        $headerName = (string) config('capell-newsletter.webhooks.signature_headers.kit', 'X-Kit-Webhook-Signature');
        $signature = (string) $request->header($headerName);

        if ($signature === '') {
            return false;
        }

        $expected = hash_hmac('sha256', $request->getContent(), $secret);

        // Support optional "sha256=" prefix some providers use.
        if (str_starts_with($signature, 'sha256=')) {
            $signature = substr($signature, 7);
        }

        return hash_equals($expected, $signature);
    }

    public function normalizeWebhook(ProviderConnection $connection, Request $request): ?ProviderWebhookEventData
    {
        $email = $request->input('subscriber.email_address') ?? $request->input('email_address');

        if (! is_string($email) || trim($email) === '') {
            return null;
        }

        $eventName = (string) $request->input('event', 'subscriber.updated');

        return new ProviderWebhookEventData(
            email: $email,
            status: str_contains($eventName, 'unsubscribe') ? SubscriberStatus::Unsubscribed : SubscriberStatus::Subscribed,
            eventType: $eventName,
            remoteId: is_string($request->input('subscriber.id')) ? $request->input('subscriber.id') : null,
            payload: $request->all(),
        );
    }

    /**
     * @return array<string, string>
     */
    private function headers(ProviderConnection $connection): array
    {
        $credentials = is_array($connection->credentials) ? $connection->credentials : [];

        return ['X-Kit-Api-Key' => $credentials['api_key'] ?? ''];
    }
}
