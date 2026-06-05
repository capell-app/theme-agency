<?php

declare(strict_types=1);

namespace Capell\FrontendAuthoring\Providers;

use Capell\Core\Facades\CapellCore;
use Capell\Core\Models\PageUrl;
use Capell\Frontend\Contracts\FrontendRuntimeManifestContributor;
use Capell\FrontendAuthoring\Data\EditableRegionPayloadData;
use Capell\FrontendAuthoring\Http\Middleware\PassThroughActivityMiddleware;
use Capell\FrontendAuthoring\Livewire\EditRegionField;
use Capell\FrontendAuthoring\Support\EditableRegionRegistry;
use Capell\FrontendAuthoring\Support\EditableRegionSigner;
use Capell\FrontendAuthoring\Support\EditorSurfaceRegistry;
use Capell\FrontendAuthoring\Support\EditorSurfaces\FieldEditorSurface;
use Capell\FrontendAuthoring\Support\FrontendAuthoringRuntimeManifestContributor;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Override;

class FrontendAuthoringServiceProvider extends ServiceProvider
{
    public static string $packageName = 'capell-app/frontend-authoring';

    public function boot(): void
    {
        if (! $this->isPackageInstalled()) {
            return;
        }

        $this->loadTranslationsFrom(__DIR__ . '/../../resources/lang', 'capell-frontend-authoring');
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'capell');
        $this->registerFallbackMiddlewareAliases();
        $this->registerAuthorizationGates();
        Livewire::component('capell-frontend-authoring.edit-region-field', EditRegionField::class);
        $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');
    }

    #[Override]
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/capell-frontend-authoring.php', 'capell-frontend-authoring');
        $this->app->singleton(EditableRegionRegistry::class, fn (): EditableRegionRegistry => new EditableRegionRegistry);
        $this->app->singleton(EditableRegionSigner::class, fn (): EditableRegionSigner => new EditableRegionSigner);
        $this->app->singleton(EditorSurfaceRegistry::class, function (): EditorSurfaceRegistry {
            $registry = new EditorSurfaceRegistry;
            $registry->register(new FieldEditorSurface);

            return $registry;
        });
        $this->app->tag([FrontendAuthoringRuntimeManifestContributor::class], FrontendRuntimeManifestContributor::TAG);
    }

    protected function isPackageInstalled(): bool
    {
        return CapellCore::isPackageInstalled(static::$packageName);
    }

    private function registerFallbackMiddlewareAliases(): void
    {
        if (array_key_exists('frontend.activity', Route::getMiddleware())) {
            return;
        }

        Route::aliasMiddleware('frontend.activity', PassThroughActivityMiddleware::class);
    }

    private function registerAuthorizationGates(): void
    {
        if (Gate::has('frontend-authoring.edit')) {
            return;
        }

        Gate::define(
            'frontend-authoring.edit',
            static fn (
                AuthenticatableContract $user,
                ?PageUrl $pageUrl = null,
                ?EditableRegionPayloadData $payload = null,
            ): bool => false,
        );
    }
}
