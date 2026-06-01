<?php

declare(strict_types=1);

namespace Capell\Newsletter\Support\CustomerPortal;

use Capell\CustomerPortal\Contracts\PortalSelfServiceItemProvider;
use Capell\CustomerPortal\Data\PortalSelfServiceItemData;
use Capell\CustomerPortal\Enums\PortalSelfServiceItemType;
use Capell\CustomerPortal\Models\PortalAccount;
use Capell\Newsletter\Actions\CreatePreferenceCenterTokenAction;
use Capell\Newsletter\Models\Subscriber;
use Illuminate\Support\Facades\Route;

final class NewsletterPortalSelfServiceItemProvider implements PortalSelfServiceItemProvider
{
    /**
     * @return iterable<PortalSelfServiceItemData>
     */
    public function selfServiceItemsFor(PortalAccount $portalAccount): iterable
    {
        $subscriber = $this->subscriberFor($portalAccount);

        if (! $subscriber instanceof Subscriber) {
            return [];
        }

        return [
            new PortalSelfServiceItemData(
                key: 'newsletter.preferences.' . $subscriber->getKey(),
                type: PortalSelfServiceItemType::NewsletterPreference,
                label: __('capell-newsletter::generic.portal.preferences_label'),
                description: __('capell-newsletter::generic.portal.preferences_description', [
                    'email' => $subscriber->email,
                ]),
                url: $this->preferenceCenterUrl($subscriber),
                status: $subscriber->status->getLabel(),
                occurredAt: $subscriber->updated_at,
                meta: [
                    'subscriber_id' => (int) $subscriber->getKey(),
                    'status' => $subscriber->status->value,
                ],
            ),
        ];
    }

    private function subscriberFor(PortalAccount $portalAccount): ?Subscriber
    {
        if (! is_string($portalAccount->email) || trim($portalAccount->email) === '') {
            return null;
        }

        return Subscriber::query()
            ->where('site_id', $portalAccount->site_id)
            ->where('email_hash', Subscriber::emailHash($portalAccount->email))
            ->latest('updated_at')
            ->latest('id')
            ->first();
    }

    private function preferenceCenterUrl(Subscriber $subscriber): ?string
    {
        if (! Route::has('capell-newsletter.preferences.show')) {
            return null;
        }

        return route('capell-newsletter.preferences.show', [
            'token' => CreatePreferenceCenterTokenAction::run($subscriber),
        ]);
    }
}
