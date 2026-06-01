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
            'segments.*' => ['integer'],
        ]);

        $segmentIds = collect($validated['segments'] ?? [])
            ->map(static fn (mixed $segmentId): int => (int) $segmentId)
            ->values()
            ->all();

        $preferences = UpdatePreferenceCenterAction::run($token, new PreferenceCenterUpdateData($segmentIds));

        abort_if($preferences === null, 404, __('capell-newsletter::messages.invalid_token'));

        return to_route('capell-newsletter.preferences.show', ['token' => $token])
            ->with('newsletter_status', __('capell-newsletter::messages.preferences_saved'));
    }
}
