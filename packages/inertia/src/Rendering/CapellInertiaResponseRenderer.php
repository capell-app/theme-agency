<?php

declare(strict_types=1);

namespace Capell\Inertia\Rendering;

use Capell\Core\Contracts\Pageable;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Frontend\Contracts\FrontendResponseRenderer;
use Capell\Frontend\Data\FrontendRenderContextData;
use Capell\Inertia\Actions\BuildInertiaPagePropsAction;
use Capell\Inertia\Actions\RenderInertiaResponseAction;
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

        return RenderInertiaResponseAction::run(
            config('capell-inertia.page_component'),
            BuildInertiaPagePropsAction::run($context),
            $context->status,
        );
    }
}
