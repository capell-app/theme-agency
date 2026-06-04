<?php

declare(strict_types=1);

namespace Capell\Inertia\Support;

use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class CapellInertiaManager
{
    /**
     * @param  array<string, mixed>  $props
     */
    public function render(string $component, array $props = [], ?int $status = null): Response
    {
        $response = Inertia::render($component, $props)->toResponse(request());

        if ($status !== null) {
            $response->setStatusCode($status);
        }

        return $response;
    }
}
