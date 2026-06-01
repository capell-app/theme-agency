<?php

declare(strict_types=1);

namespace Capell\Newsletter\Http\Controllers;

use Capell\Newsletter\Actions\ResolvePreferenceCenterAction;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class ShowPreferenceCenterController
{
    public function __invoke(Request $request, string $token): Response
    {
        $preferences = ResolvePreferenceCenterAction::run($token);

        if ($preferences === null) {
            return response(__('capell-newsletter::messages.invalid_token'), 404);
        }

        $response = response()->view('capell-newsletter::preference-center', [
            'preferences' => $preferences,
            'token' => $token,
        ]);

        $response->headers->set('Cache-Control', 'private, no-store');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow');

        return $response;
    }
}
