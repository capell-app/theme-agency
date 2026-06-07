<?php

declare(strict_types=1);

namespace Capell\Newsletter\Http\Controllers;

use Capell\Core\Models\Site;
use Capell\Frontend\Facades\Frontend;
use Capell\Newsletter\Actions\SubscribeFromPublicRequestAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;

class SubscribeController extends BaseController
{
    public function __invoke(Request $request): JsonResponse|RedirectResponse
    {
        $site = Frontend::site();

        abort_unless($site instanceof Site, Response::HTTP_NOT_FOUND);

        /** @var array{email: string, first_name?: string|null, last_name?: string|null, source?: string|null} $payload */
        $payload = $request->validate([
            'email' => ['required', 'email:rfc,dns', 'max:255'],
            'first_name' => ['nullable', 'string', 'max:120'],
            'last_name' => ['nullable', 'string', 'max:120'],
            'source' => ['nullable', 'string', 'max:120'],
        ]);

        SubscribeFromPublicRequestAction::run($site, $payload, $request);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => __('capell-newsletter::messages.subscribed'),
            ]);
        }

        return redirect()
            ->back()
            ->with('newsletter_status', __('capell-newsletter::messages.subscribed'));
    }
}
