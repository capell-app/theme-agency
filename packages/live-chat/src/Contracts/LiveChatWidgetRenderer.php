<?php

declare(strict_types=1);

namespace Capell\LiveChat\Contracts;

use Symfony\Component\HttpFoundation\Response;

interface LiveChatWidgetRenderer
{
    public function render(): Response;
}
