<?php

declare(strict_types=1);

namespace Capell\LiveChat\Http\Controllers;

use Capell\LiveChat\Contracts\LiveChatWidgetRenderer;
use Symfony\Component\HttpFoundation\Response;

final class ShowLiveChatWidgetController
{
    public function __construct(private readonly LiveChatWidgetRenderer $renderer) {}

    public function __invoke(): Response
    {
        return $this->renderer->render();
    }
}
