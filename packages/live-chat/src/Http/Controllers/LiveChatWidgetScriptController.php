<?php

declare(strict_types=1);

namespace Capell\LiveChat\Http\Controllers;

use Capell\LiveChat\Actions\ApplyLiveChatCorsHeadersAction;
use Capell\LiveChat\Actions\BuildLiveChatWidgetConfigAction;
use Capell\LiveChat\Actions\GuardLiveChatInstallationOriginAction;
use Capell\LiveChat\Actions\ResolveLiveChatInstallationAction;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class LiveChatWidgetScriptController
{
    public function __invoke(Request $request): Response
    {
        $publicKey = $request->query('key');
        $installation = is_string($publicKey) && trim($publicKey) !== ''
            ? ResolveLiveChatInstallationAction::run($publicKey)
            : null;
        $origin = null;

        if (is_string($publicKey) && trim($publicKey) !== '') {
            abort_if($installation === null, 404);
            $origin = GuardLiveChatInstallationOriginAction::run($installation, $request);
        }

        /** @var Response $response */
        $response = response()
            ->view('capell-live-chat::script', [
                'config' => $installation === null ? null : BuildLiveChatWidgetConfigAction::run($installation),
            ])
            ->header('Content-Type', 'application/javascript; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=300');

        ApplyLiveChatCorsHeadersAction::run($response, $origin);

        return $response;
    }
}
