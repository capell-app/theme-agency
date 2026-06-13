<?php

declare(strict_types=1);

namespace Capell\LiveChat\Http\Controllers;

use Illuminate\Http\Response;

final class LiveChatWidgetScriptController
{
    public function __invoke(): Response
    {
        return response()
            ->view('capell-live-chat::script')
            ->header('Content-Type', 'application/javascript; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=300');
    }
}
