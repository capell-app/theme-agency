<?php

declare(strict_types=1);

namespace Capell\Inertia\Rendering;

use Capell\Core\Contracts\Pageable;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Frontend\Actions\AssertPublicHtmlContainsNoAuthoringSurfaceAction;
use Capell\Frontend\Contracts\FrontendResponseRenderer;
use Capell\Frontend\Data\FrontendRenderContextData;
use Capell\Inertia\Actions\BuildInertiaPagePropsAction;
use Capell\Inertia\Actions\ResolveInertiaComponentNameAction;
use Capell\Inertia\Actions\ResolveInertiaRootViewAction;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

final class CapellInertiaResponseRenderer implements FrontendResponseRenderer
{
    public function runtime(): FrontendRuntime
    {
        return FrontendRuntime::Inertia;
    }

    public function render(FrontendRenderContextData $context): Response
    {
        if (! $context->page instanceof Pageable) {
            return response()->noContent($context->status ?? 404);
        }

        Inertia::setRootView(ResolveInertiaRootViewAction::run());

        $response = Inertia::render(
            ResolveInertiaComponentNameAction::run(),
            BuildInertiaPagePropsAction::run($context),
        )->toResponse(request());

        if ($context->status !== null) {
            $response->setStatusCode($context->status);
        }

        AssertPublicHtmlContainsNoAuthoringSurfaceAction::run($response);

        return $response;
    }
}
