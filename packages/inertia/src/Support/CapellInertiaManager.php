<?php

declare(strict_types=1);

namespace Capell\Inertia\Support;

use Capell\Inertia\Actions\RenderInertiaResponseAction;
use Symfony\Component\HttpFoundation\Response;

class CapellInertiaManager
{
    /**
     * @param  array<string, mixed>  $props
     */
    public function render(string $component, array $props = [], ?int $status = null): Response
    {
        return RenderInertiaResponseAction::run($component, $props, $status);
    }
}
