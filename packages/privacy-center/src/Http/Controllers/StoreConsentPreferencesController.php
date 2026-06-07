<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Http\Controllers;

use Capell\PrivacyCenter\Actions\RecordConsentAction;
use Capell\PrivacyCenter\Data\ConsentRecordData;
use Capell\PrivacyCenter\Enums\ConsentDecision;
use Capell\PrivacyCenter\Enums\CookieCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

final class StoreConsentPreferencesController
{
    public function __invoke(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'categories' => ['array'],
            'categories.*' => [Rule::enum(CookieCategory::class)],
        ]);

        $grantedCategories = array_map(
            static fn (mixed $category): string => (string) $category,
            Arr::wrap($validated['categories'] ?? []),
        );

        foreach (CookieCategory::cases() as $category) {
            RecordConsentAction::run(new ConsentRecordData(
                category: $category,
                decision: $this->decision($category, $grantedCategories),
                evidence: [
                    'surface' => 'public_preferences',
                ],
            ));
        }

        return redirect()
            ->route('capell-privacy-center.consent.show')
            ->with('capell_privacy_center_consent_saved', true)
            ->cookie('capell_privacy_consent_saved', '1', 60 * 24 * 180, '/', null, null, false, false, 'Lax');
    }

    /**
     * @param  list<string>  $grantedCategories
     */
    private function decision(CookieCategory $category, array $grantedCategories): ConsentDecision
    {
        if ($category === CookieCategory::Essential) {
            return ConsentDecision::Granted;
        }

        return in_array($category->value, $grantedCategories, true)
            ? ConsentDecision::Granted
            : ConsentDecision::Denied;
    }
}
