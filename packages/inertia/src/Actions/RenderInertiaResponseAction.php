<?php

declare(strict_types=1);

namespace Capell\Inertia\Actions;

use Capell\Frontend\Actions\AssertPublicHtmlContainsNoAuthoringSurfaceAction;
use Inertia\Inertia;
use Lorisleiva\Actions\Concerns\AsObject;
use Symfony\Component\HttpFoundation\Response;

final class RenderInertiaResponseAction
{
    use AsObject;

    /**
     * @param  array<string, mixed>  $props
     */
    public function handle(mixed $component, array $props = [], ?int $status = null): Response
    {
        Inertia::setRootView(ResolveInertiaRootViewAction::run());

        $response = Inertia::render(
            ResolveInertiaComponentNameAction::run($component),
            $props,
        )->toResponse(request());

        if ($status !== null) {
            $response->setStatusCode($status);
        }

        AssertPublicHtmlContainsNoAuthoringSurfaceAction::run($response);

        return $response;
    }
}
