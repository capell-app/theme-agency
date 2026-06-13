<?php

declare(strict_types=1);

namespace Capell\LiveChat\Rendering;

use Capell\LiveChat\Actions\BuildLiveChatWidgetConfigAction;
use Capell\LiveChat\Contracts\LiveChatWidgetRenderer;
use Symfony\Component\HttpFoundation\Response;

final class BladeLiveChatWidgetRenderer implements LiveChatWidgetRenderer
{
    public function render(): Response
    {
        return response()->view('capell-live-chat::widget', [
            'config' => BuildLiveChatWidgetConfigAction::run(),
        ]);
    }
}
