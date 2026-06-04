<?php

declare(strict_types=1);

namespace Capell\CustomerPortal\Http\Controllers;

use Capell\CustomerPortal\Actions\ResolvePortalPreferenceOptionsAction;
use Capell\CustomerPortal\Actions\UpdatePortalPreferencesAction;
use Capell\CustomerPortal\Data\PortalPreferenceOptionData;
use Capell\CustomerPortal\Data\PortalPreferencesData;
use Capell\CustomerPortal\Http\Controllers\Concerns\ResolvesPortalAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class UpdatePortalPreferencesController
{
    use ResolvesPortalAccount;

    public function __invoke(Request $request): RedirectResponse
    {
        $preferenceOptions = ResolvePortalPreferenceOptionsAction::run();
        $validated = $request->validate([
            'preferences' => ['array'],
            'preferences.*' => ['nullable', 'boolean'],
        ]);

        $preferences = $this->normalizePreferences($preferenceOptions, $validated['preferences'] ?? []);

        UpdatePortalPreferencesAction::run(
            portalAccount: $this->portalAccount($request),
            preferencesData: new PortalPreferencesData(values: $preferences, replace: true),
        );

        return to_route('capell-customer-portal.dashboard')
            ->with('customer_portal_status', __('capell-customer-portal::generic.frontend.preferences_saved'));
    }

    /**
     * @param  array<int, PortalPreferenceOptionData>  $preferenceOptions
     * @param  array<string, mixed>  $submittedPreferences
     * @return array<string, bool>
     */
    private function normalizePreferences(array $preferenceOptions, array $submittedPreferences): array
    {
        $preferences = [];

        foreach ($preferenceOptions as $preferenceOption) {
            $preferences[$preferenceOption->key] = (bool) ($submittedPreferences[$preferenceOption->key] ?? false);
        }

        return $preferences;
    }
}
