<?php

declare(strict_types=1);

namespace Capell\Inertia\Providers;

use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Packages\AbstractPackageServiceProvider;
use Capell\Frontend\Support\Render\FrontendResponseRendererRegistry;
use Capell\Frontend\Support\Routing\FrontendRouteMiddlewareRegistry;
use Capell\Inertia\Http\Middleware\HandleInertiaRequests;
use Capell\Inertia\Rendering\CapellInertiaResponseRenderer;
use Capell\Inertia\Support\CapellInertiaManager;
use Capell\Inertia\Support\InertiaAdapterRegistry;
use Illuminate\Support\Facades\Route;
use Inertia\ServiceProvider as LaravelInertiaServiceProvider;
use Override;
use Spatie\LaravelPackageTools\Package;

final class InertiaServiceProvider extends AbstractPackageServiceProvider
{
    public static string $name = 'capell-inertia';

    public static string $packageName = 'capell-app/inertia';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(self::$name)
            ->hasConfigFile()
            ->hasViews(self::$name);
    }

    public function packageRegistered(): void
    {
        if (class_exists(LaravelInertiaServiceProvider::class) && $this->app->getProvider(LaravelInertiaServiceProvider::class) === null) {
            $this->app->register(LaravelInertiaServiceProvider::class);
        }

        $this->app->singleton('capell-inertia', fn (): CapellInertiaManager => new CapellInertiaManager);
        $this->app->singleton(InertiaAdapterRegistry::class);
    }

    public function packageBooted(): void
    {
        Route::aliasMiddleware('capell.inertia', HandleInertiaRequests::class);

        if (! $this->isPackageInstalled()) {
            return;
        }

        $this->registerRenderer();
        $this->registerFrontendMiddleware();
    }

    #[Override]
    protected function isPackageInstalled(): bool
    {
        return CapellCore::isPackageInstalled(self::$packageName);
    }

    private function registerRenderer(): void
    {
        $configure = static function (FrontendResponseRendererRegistry $registry): void {
            $registry->registerClass(FrontendRuntime::Inertia, CapellInertiaResponseRenderer::class);
        };

        $this->app->afterResolving(FrontendResponseRendererRegistry::class, $configure);

        if ($this->app->resolved(FrontendResponseRendererRegistry::class)) {
            $configure($this->app->make(FrontendResponseRendererRegistry::class));
        }
    }

    private function registerFrontendMiddleware(): void
    {
        $configure = static function (FrontendRouteMiddlewareRegistry $registry): void {
            $registry->insertAfter('web', [HandleInertiaRequests::class]);
        };

        $this->app->afterResolving(FrontendRouteMiddlewareRegistry::class, $configure);

        if ($this->app->resolved(FrontendRouteMiddlewareRegistry::class)) {
            $configure($this->app->make(FrontendRouteMiddlewareRegistry::class));
        }
    }
}
