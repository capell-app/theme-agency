<?php

declare(strict_types=1);

namespace Capell\CustomerPortal\Http\Controllers;

use Capell\CustomerPortal\Actions\UpdatePortalPreferencesAction;
use Capell\CustomerPortal\Data\PortalPreferencesData;
use Capell\CustomerPortal\Http\Controllers\Concerns\ResolvesPortalAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class UpdatePortalPreferencesController
{
    use ResolvesPortalAccount;

    public function __invoke(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'preferences' => ['array'],
            'preferences.email_updates' => ['nullable', 'boolean'],
            'preferences.product_updates' => ['nullable', 'boolean'],
            'preferences.event_reminders' => ['nullable', 'boolean'],
        ]);

        $preferences = array_map(
            static fn (mixed $value): bool => (bool) $value,
            $validated['preferences'] ?? [],
        );

        UpdatePortalPreferencesAction::run(
            portalAccount: $this->portalAccount($request),
            preferencesData: new PortalPreferencesData(values: $preferences),
        );

        return to_route('capell-customer-portal.dashboard')
            ->with('customer_portal_status', __('capell-customer-portal::generic.frontend.preferences_saved'));
    }
}
