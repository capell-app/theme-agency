<?php

declare(strict_types=1);

namespace Capell\Inertia\Support;

use Capell\Frontend\Actions\AssertPublicHtmlContainsNoAuthoringSurfaceAction;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class CapellInertiaManager
{
    /**
     * @param  array<string, mixed>  $props
     */
    public function render(string $component, array $props = [], ?int $status = null): Response
    {
        Inertia::setRootView((string) config('capell-inertia.root_view', 'capell-inertia::app'));

        $response = Inertia::render($component, $props)->toResponse(request());

        if ($status !== null) {
            $response->setStatusCode($status);
        }

        AssertPublicHtmlContainsNoAuthoringSurfaceAction::run($response);

        return $response;
    }
}
