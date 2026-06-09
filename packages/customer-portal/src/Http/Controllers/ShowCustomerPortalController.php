<?php

declare(strict_types=1);

namespace Capell\CustomerPortal\Http\Controllers;

use BackedEnum;
use Capell\CustomerPortal\Actions\ResolvePortalDashboardItemsAction;
use Capell\CustomerPortal\Actions\ResolvePortalPreferenceOptionsAction;
use Capell\CustomerPortal\Actions\ResolvePortalProfileAction;
use Capell\CustomerPortal\Actions\ResolvePortalSelfServiceItemsAction;
use Capell\CustomerPortal\Data\PortalDashboardItemData;
use Capell\CustomerPortal\Data\PortalPreferenceOptionData;
use Capell\CustomerPortal\Data\PortalProfileData;
use Capell\CustomerPortal\Data\PortalSelfServiceItemData;
use Capell\CustomerPortal\Enums\SupportRequestPriority;
use Capell\CustomerPortal\Enums\SupportRequestStatus;
use Capell\CustomerPortal\Http\Controllers\Concerns\ResolvesPortalAccount;
use Capell\CustomerPortal\Models\PortalSupportRequest;
use Capell\CustomerPortal\Models\PortalSupportRequestReply;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Stringable;
use UnitEnum;

final class ShowCustomerPortalController
{
    use ResolvesPortalAccount;

    public function __invoke(Request $request): Response
    {
        $portalAccount = $this->portalAccount($request);
        $profileData = ResolvePortalProfileAction::run($portalAccount);
        $preferenceOptions = ResolvePortalPreferenceOptionsAction::run();
        $dashboardItems = ResolvePortalDashboardItemsAction::run($portalAccount);
        $selfServiceItems = ResolvePortalSelfServiceItemsAction::run($portalAccount);
        $supportRequests = $portalAccount
            ->supportRequests()
            ->with('replies')
            ->latest('submitted_at')
            ->limit(5)
            ->get()
            ->map(static fn (PortalSupportRequest $supportRequest): array => [
                'id' => self::modelKey($supportRequest),
                'subject' => $supportRequest->subject,
                'status' => $supportRequest->status->getLabel(),
                'priority' => $supportRequest->priority->getLabel(),
                'submitted_at' => $supportRequest->submitted_at?->toDayDateTimeString(),
                'message' => $supportRequest->message,
                'replies' => $supportRequest->replies
                    ->sortBy('submitted_at')
                    ->map(static fn (PortalSupportRequestReply $reply): array => [
                        'sender_type' => $reply->sender_type,
                        'message' => $reply->message,
                        'submitted_at' => $reply->submitted_at->toDayDateTimeString(),
                    ])
                    ->values()
                    ->all(),
            ])
            ->all();

        return $this->noStore(response()->view('capell-customer-portal::dashboard', [
            'displayName' => $profileData->displayName ?? $profileData->email,
            'email' => $profileData->email,
            'profileFields' => $this->profileFields($profileData),
            'preferences' => $portalAccount->preferences ?? [],
            'preferenceOptions' => array_map(
                static fn (PortalPreferenceOptionData $option): array => [
                    'key' => $option->key,
                    'label' => $option->label,
                ],
                $preferenceOptions,
            ),
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

    private static function modelKey(PortalSupportRequest $supportRequest): int
    {
        $key = $supportRequest->getKey();

        if (is_int($key)) {
            return $key;
        }

        return is_string($key) && ctype_digit($key) ? (int) $key : 0;
    }

    /**
     * @return list<array{label: string, value: string}>
     */
    private function profileFields(PortalProfileData $profileData): array
    {
        $fields = [
            [
                'label' => __('capell-customer-portal::generic.frontend.profile_name'),
                'value' => $profileData->displayName,
            ],
            [
                'label' => __('capell-customer-portal::generic.frontend.profile_email'),
                'value' => $profileData->email,
            ],
            [
                'label' => __('capell-customer-portal::generic.frontend.profile_status'),
                'value' => $profileData->status->getLabel(),
            ],
        ];

        foreach ($profileData->profile as $key => $value) {
            if (! is_string($key)) {
                continue;
            }

            if ($this->shouldHideProfileField($key)) {
                continue;
            }

            $displayValue = $this->formatProfileValue($value);

            if ($displayValue === null) {
                continue;
            }

            $fields[] = [
                'label' => Str::headline($key),
                'value' => $displayValue,
            ];
        }

        return array_values(array_filter(
            $fields,
            static fn (array $field): bool => is_string($field['value']) && trim($field['value']) !== '',
        ));
    }

    private function shouldHideProfileField(string $key): bool
    {
        $normalizedKey = Str::of($key)->lower()->replace('-', '_')->toString();

        return in_array($normalizedKey, [
            'id',
            'account_id',
            'site_id',
            'owner_id',
            'email_hash',
        ], true)
            || str_contains($normalizedKey, 'password')
            || str_contains($normalizedKey, 'secret')
            || str_contains($normalizedKey, 'token')
            || str_contains($normalizedKey, 'hash');
    }

    private function formatProfileValue(mixed $value): ?string
    {
        if ($value instanceof UnitEnum) {
            return $value instanceof BackedEnum ? (string) $value->value : $value->name;
        }

        if ($value instanceof Stringable) {
            return (string) $value;
        }

        if (is_bool($value)) {
            return $value
                ? __('capell-customer-portal::generic.frontend.yes')
                : __('capell-customer-portal::generic.frontend.no');
        }

        if (is_int($value) || is_float($value) || is_string($value)) {
            $displayValue = trim((string) $value);

            return $displayValue === '' ? null : $displayValue;
        }

        return null;
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
