<?php

declare(strict_types=1);

namespace Capell\Inertia\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Frontend\Support\Render\FrontendResponseRendererRegistry;
use Capell\Frontend\Support\Routing\FrontendRouteMiddlewareRegistry;
use Capell\Inertia\Http\Middleware\HandleInertiaRequests;

final class InertiaHealthCheck implements ChecksExtensionHealth
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }

    public function passes(): bool
    {
        return $this->rendererRegistered() && $this->middlewareRegistered();
    }

    public function rendererRegistered(): bool
    {
        return app()->bound(FrontendResponseRendererRegistry::class)
            && resolve(FrontendResponseRendererRegistry::class)->has(FrontendRuntime::Inertia);
    }

    public function middlewareRegistered(): bool
    {
        return app()->bound(FrontendRouteMiddlewareRegistry::class)
            && in_array(HandleInertiaRequests::class, resolve(FrontendRouteMiddlewareRegistry::class)->all(), true);
    }
}
