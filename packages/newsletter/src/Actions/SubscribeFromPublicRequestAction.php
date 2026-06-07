<?php

declare(strict_types=1);

namespace Capell\Newsletter\Actions;

use Capell\Core\Models\Site;
use Capell\Newsletter\Data\ConsentEvidenceData;
use Capell\Newsletter\Data\SubscriberData;
use Capell\Newsletter\Enums\ConfirmationMode;
use Capell\Newsletter\Enums\SubscriberStatus;
use Capell\Newsletter\Models\Subscriber;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;

class SubscribeFromPublicRequestAction
{
    use AsAction;

    /**
     * @param  array{email: string, first_name?: string|null, last_name?: string|null, source?: string|null}  $payload
     */
    public function handle(Site $site, array $payload, Request $request): Subscriber
    {
        $requiresDoubleOptIn = (bool) config('capell-newsletter.double_opt_in.enabled_by_default', true);
        $targetStatus = $requiresDoubleOptIn ? SubscriberStatus::Pending : SubscriberStatus::Subscribed;
        $source = trim((string) ($payload['source'] ?? 'public_subscribe'));
        $source = $source !== '' ? $source : 'public_subscribe';

        $evidence = new ConsentEvidenceData(
            sourceType: $source,
            consentText: __('capell-newsletter::messages.public_subscribe_consent'),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
            url: $request->fullUrl(),
            referer: $request->headers->get('referer'),
        );

        $subscriber = UpsertSubscriberAction::run(new SubscriberData(
            siteId: (int) $site->getKey(),
            email: $payload['email'],
            status: $targetStatus,
            firstName: $payload['first_name'] ?? null,
            lastName: $payload['last_name'] ?? null,
            sourceFormHandle: $source,
        ), $evidence);

        if ($targetStatus === SubscriberStatus::Subscribed) {
            QueueProviderSyncAction::run($subscriber);

            return $subscriber;
        }

        if ($this->confirmationMode() === ConfirmationMode::CapellOwned) {
            RequestDoubleOptInAction::run($subscriber, $evidence);

            return $subscriber;
        }

        QueueProviderSyncAction::run($subscriber);

        return $subscriber;
    }

    private function confirmationMode(): ConfirmationMode
    {
        $configuredMode = config('capell-newsletter.double_opt_in.default_confirmation_mode', ConfirmationMode::CapellOwned->value);

        return ConfirmationMode::tryFrom((string) $configuredMode) ?? ConfirmationMode::CapellOwned;
    }
}
