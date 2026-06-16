<?php

declare(strict_types=1);

namespace Capell\Inertia\Support;

use Capell\Frontend\Actions\AssertPublicHtmlContainsNoAuthoringSurfaceAction;
use Capell\Inertia\Actions\ResolveInertiaComponentNameAction;
use Capell\Inertia\Actions\ResolveInertiaRootViewAction;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class CapellInertiaManager
{
    /**
     * @param  array<string, mixed>  $props
     */
    public function render(string $component, array $props = [], ?int $status = null): Response
    {
        Inertia::setRootView(ResolveInertiaRootViewAction::run());

        $response = Inertia::render(ResolveInertiaComponentNameAction::run($component), $props)->toResponse(request());

        if ($status !== null) {
            $response->setStatusCode($status);
        }

        AssertPublicHtmlContainsNoAuthoringSurfaceAction::run($response);

        return $response;
    }
}
