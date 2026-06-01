<?php

declare(strict_types=1);

namespace Capell\Events\Support\CustomerPortal;

use Capell\CustomerPortal\Contracts\PortalSelfServiceItemProvider;
use Capell\CustomerPortal\Data\PortalSelfServiceItemData;
use Capell\CustomerPortal\Enums\PortalSelfServiceItemType;
use Capell\CustomerPortal\Models\PortalAccount;
use Capell\Events\Models\EventRegistration;
use Illuminate\Database\Eloquent\Builder;

final class EventsPortalSelfServiceItemProvider implements PortalSelfServiceItemProvider
{
    /**
     * @return iterable<PortalSelfServiceItemData>
     */
    public function selfServiceItemsFor(PortalAccount $portalAccount): iterable
    {
        if (! is_string($portalAccount->email) || trim($portalAccount->email) === '') {
            return [];
        }

        return EventRegistration::query()
            ->with(['occurrence.event.pageUrl'])
            ->whereRaw('lower(email) = ?', [mb_strtolower(trim($portalAccount->email))])
            ->whereHas('occurrence.event', function (Builder $query) use ($portalAccount): void {
                $query->where('site_id', $portalAccount->site_id);
            })
            ->latest('registered_at')
            ->latest('id')
            ->limit(10)
            ->get()
            ->map(fn (EventRegistration $registration): PortalSelfServiceItemData => new PortalSelfServiceItemData(
                key: 'events.registration.' . $registration->getKey(),
                type: PortalSelfServiceItemType::EventRegistration,
                label: $registration->occurrence->event->name,
                description: __('capell-events::generic.portal.registration_description', [
                    'quantity' => $registration->quantity,
                    'date' => $registration->occurrence->starts_at->toDayDateTimeString(),
                ]),
                url: $registration->occurrence->occurrenceUrl(),
                status: $registration->status->getLabel(),
                occurredAt: $registration->registered_at,
                meta: [
                    'registration_id' => (int) $registration->getKey(),
                    'occurrence_id' => (int) $registration->occurrence->getKey(),
                    'status' => $registration->status->value,
                ],
            ))
            ->all();
    }
}
