<?php

declare(strict_types=1);

namespace Capell\Comments\Providers;

use Capell\Comments\Enums\LivewireComponentEnum;
use Capell\Comments\Livewire\CommentThreadComponent;
use Capell\Core\Data\RenderableDefinitionData;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Renderables\RenderableRegistry;
use Capell\Frontend\Data\MainContentRenderHookData;
use Capell\Frontend\Enums\RenderHookLocation;
use Capell\Frontend\Support\Render\RenderHookRegistry;
use Composer\InstalledVersions;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class FrontendServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this
            ->registerLivewireComponents()
            ->registerBladeComponents();

        if (! $this->isPackageInstalled()) {
            return;
        }

        $this
            ->registerRenderables()
            ->registerAutoInjection();
    }

    protected function isPackageInstalled(): bool
    {
        return CapellCore::isPackageInstalled(CommentsServiceProvider::$packageName);
    }

    private function registerLivewireComponents(): self
    {
        if ($this->isLivewireV3()) {
            foreach (LivewireComponentEnum::getComponents() as $name => $component) {
                Livewire::component($name, $component);
            }

            return $this;
        }

        Livewire::addNamespace(
            namespace: 'capell-comments',
            classNamespace: 'Capell\\Comments\\Livewire',
            classPath: __DIR__ . '/../Livewire',
            classViewPath: __DIR__ . '/../../resources/views/livewire',
        );

        return $this;
    }

    private function registerBladeComponents(): self
    {
        Blade::componentNamespace('Capell\\Comments\\View\\Components', 'capell-comments');
        Blade::anonymousComponentNamespace('Capell\\Comments\\View\\Components');

        return $this;
    }

    private function registerRenderables(): self
    {
        if (! $this->app->bound(RenderableRegistry::class)) {
            return $this;
        }

        resolve(RenderableRegistry::class)->register(new RenderableDefinitionData(
            key: 'capell-comments::block.thread',
            type: 'layout-block',
            livewire: CommentThreadComponent::class,
        ));

        return $this;
    }

    private function registerAutoInjection(): self
    {
        if (! (bool) config('capell-comments.auto_inject', false) || ! $this->app->bound(RenderHookRegistry::class)) {
            return $this;
        }

        $this->app->make(RenderHookRegistry::class)->register(
            RenderHookLocation::MainContent,
            static function (mixed $context): string {
                $item = $context->item ?? null;
                if (! $item instanceof MainContentRenderHookData || ! is_object($item->page)) {
                    return '';
                }

                $threadKey = CommentThreadComponent::threadKeyFor($item->page);

                return view('capell-comments::livewire.thread-shell', [
                    'threadKey' => $threadKey,
                ])->render();
            },
            priority: 90,
            scenario: 'frontend-main-layout',
            target: 'capell::layout.main',
        );

        return $this;
    }

    private function isLivewireV3(): bool
    {
        if (! class_exists(InstalledVersions::class) || ! InstalledVersions::isInstalled('livewire/livewire')) {
            return true;
        }

        $version = InstalledVersions::getVersion('livewire/livewire');

        return version_compare($version, '4.0.0', '<');
    }
}
