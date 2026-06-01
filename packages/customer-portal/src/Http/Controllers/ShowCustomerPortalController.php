<?php

declare(strict_types=1);

namespace Capell\CustomerPortal\Http\Controllers;

use Capell\CustomerPortal\Actions\ResolvePortalDashboardItemsAction;
use Capell\CustomerPortal\Actions\ResolvePortalSelfServiceItemsAction;
use Capell\CustomerPortal\Data\PortalDashboardItemData;
use Capell\CustomerPortal\Data\PortalSelfServiceItemData;
use Capell\CustomerPortal\Enums\SupportRequestPriority;
use Capell\CustomerPortal\Enums\SupportRequestStatus;
use Capell\CustomerPortal\Http\Controllers\Concerns\ResolvesPortalAccount;
use Capell\CustomerPortal\Models\PortalSupportRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class ShowCustomerPortalController
{
    use ResolvesPortalAccount;

    public function __invoke(Request $request): Response
    {
        $portalAccount = $this->portalAccount($request);
        $dashboardItems = ResolvePortalDashboardItemsAction::run($portalAccount);
        $selfServiceItems = ResolvePortalSelfServiceItemsAction::run($portalAccount);
        $supportRequests = $portalAccount
            ->supportRequests()
            ->latest('submitted_at')
            ->limit(5)
            ->get()
            ->map(static fn (PortalSupportRequest $supportRequest): array => [
                'subject' => $supportRequest->subject,
                'status' => $supportRequest->status->getLabel(),
                'priority' => $supportRequest->priority->getLabel(),
                'submitted_at' => $supportRequest->submitted_at?->toDayDateTimeString(),
            ])
            ->all();

        return $this->noStore(response()->view('capell-customer-portal::dashboard', [
            'displayName' => $portalAccount->display_name ?? $portalAccount->email,
            'email' => $portalAccount->email,
            'preferences' => $portalAccount->preferences ?? [],
            'dashboardItems' => array_map(
                static fn (PortalDashboardItemData $item): array => [
                    'label' => $item->label,
                    'description' => $item->description,
                    'url' => $item->url,
                    'count' => $item->count,
                ],
                $dashboardItems,
            ),
            'selfServiceItems' => array_map(
                static fn (PortalSelfServiceItemData $item): array => [
                    'label' => $item->label,
                    'type' => $item->type->getLabel(),
                    'description' => $item->description,
                    'url' => $item->url,
                    'status' => $item->status,
                    'occurred_at' => $item->occurredAt?->toDayDateTimeString(),
                ],
                $selfServiceItems,
            ),
            'supportRequests' => $supportRequests,
            'priorityOptions' => $this->priorityOptions(),
            'statusOptions' => array_map(
                static fn (SupportRequestStatus $status): string => $status->getLabel(),
                SupportRequestStatus::cases(),
            ),
        ]));
    }

    /**
     * @return array<string, string>
     */
    private function priorityOptions(): array
    {
        $options = [];

        foreach (SupportRequestPriority::cases() as $priority) {
            $options[$priority->value] = $priority->getLabel();
        }

        return $options;
    }

    private function noStore(Response $response): Response
    {
        $response->headers->set('Cache-Control', 'private, no-store');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow');

        return $response;
    }
}
