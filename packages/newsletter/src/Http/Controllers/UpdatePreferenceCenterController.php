<?php

declare(strict_types=1);

namespace Capell\Newsletter\Http\Controllers;

use Capell\Newsletter\Actions\UpdatePreferenceCenterAction;
use Capell\Newsletter\Data\PreferenceCenterUpdateData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class UpdatePreferenceCenterController
{
    public function __invoke(Request $request, string $token): RedirectResponse
    {
        $validated = $request->validate([
            'segments' => ['array'],
            'segments.*' => ['string'],
        ]);

        $segmentHandles = collect($validated['segments'] ?? [])
            ->filter(static fn (mixed $segmentHandle): bool => is_string($segmentHandle) && $segmentHandle !== '')
            ->map(static fn (string $segmentHandle): string => $segmentHandle)
            ->values()
            ->all();

        $preferences = UpdatePreferenceCenterAction::run($token, new PreferenceCenterUpdateData(array_values($segmentHandles)));

        abort_if($preferences === null, 404, __('capell-newsletter::messages.invalid_token'));

        return to_route('capell-newsletter.preferences.show', ['token' => $token])
            ->with('newsletter_status', __('capell-newsletter::messages.preferences_saved'));
    }
}
