<?php

declare(strict_types=1);

namespace Capell\FilamentPeek\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\FilamentPeek\Actions\CreatePagePreviewSnapshotAction;
use Capell\FilamentPeek\Actions\RenderPagePreviewSnapshotAction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Pboivin\FilamentPeek\FilamentPeekPlugin;
use Throwable;

final class FilamentPeekHealthCheck implements ChecksExtensionHealth
{
    /**
     * The signed preview route the controller and snapshot URLs depend on.
     */
    private const string PREVIEW_ROUTE_NAME = 'capell-filament-peek.preview';

    /**
     * Core actions the controller resolves on every preview render.
     *
     * @var list<class-string>
     */
    private const array REQUIRED_ACTIONS = [
        CreatePagePreviewSnapshotAction::class,
        RenderPagePreviewSnapshotAction::class,
    ];

    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }

    /**
     * @return Collection<int, DoctorCheckResultData>
     */
    public static function runDiagnostics(): Collection
    {
        $check = new self;

        return collect([
            $check->previewRouteCheck(),
            $check->previewActionsCheck(),
            $check->upstreamPluginCheck(),
            $check->previewCacheStoreCheck(),
        ]);
    }

    public static function passed(): bool
    {
        return self::runDiagnostics()
            ->every(static fn (DoctorCheckResultData $result): bool => $result->passed);
    }

    /**
     * Asserts the signed preview route is registered so snapshot URLs resolve.
     */
    public function previewRouteCheck(): DoctorCheckResultData
    {
        $registered = Route::has(self::PREVIEW_ROUTE_NAME);

        return new DoctorCheckResultData(
            label: 'Filament Peek preview route',
            passed: $registered,
            message: $registered
                ? 'The signed preview route is registered.'
                : 'The signed preview route is not registered.',
            remediation: $registered
                ? null
                : 'Ensure FilamentPeekServiceProvider loads the package web routes and capell-filament-peek.enabled is true.',
        );
    }

    /**
     * Asserts the snapshot create and render actions resolve from the container.
     */
    public function previewActionsCheck(): DoctorCheckResultData
    {
        $unresolvableActions = $this->unresolvableActions();

        return new DoctorCheckResultData(
            label: 'Filament Peek preview actions',
            passed: $unresolvableActions === [],
            message: $unresolvableActions === []
                ? 'The snapshot create and render actions are resolvable.'
                : 'Unresolvable preview actions: ' . implode(', ', $unresolvableActions) . '.',
            remediation: $unresolvableActions === []
                ? null
                : 'Ensure the Filament Peek package autoloader and service provider are registered.',
        );
    }

    /**
     * Asserts the upstream plugin used by the panel extender is available.
     */
    public function upstreamPluginCheck(): DoctorCheckResultData
    {
        $available = class_exists(FilamentPeekPlugin::class);

        return new DoctorCheckResultData(
            label: 'Filament Peek upstream plugin',
            passed: $available,
            message: $available
                ? 'The upstream pboivin/filament-peek plugin is installed.'
                : 'The upstream pboivin/filament-peek plugin is not installed.',
            remediation: $available
                ? null
                : 'Run composer install to restore the pboivin/filament-peek dependency.',
        );
    }

    /**
     * Asserts the configured snapshot cache store can be resolved.
     */
    public function previewCacheStoreCheck(): DoctorCheckResultData
    {
        $storeName = $this->configuredCacheStoreName();
        $reachable = $this->cacheStoreReachable();

        $storeLabel = $storeName ?? 'default';

        return new DoctorCheckResultData(
            label: 'Filament Peek preview cache store',
            passed: $reachable,
            message: $reachable
                ? sprintf('The preview cache store (%s) is reachable.', $storeLabel)
                : sprintf('The preview cache store (%s) could not be resolved.', $storeLabel),
            remediation: $reachable
                ? null
                : 'Set capell-filament-peek.preview.cache_store to a configured cache store, or leave it empty to use the default store.',
        );
    }

    /**
     * @return list<class-string>
     */
    public function unresolvableActions(): array
    {
        $actions = [];

        foreach (self::REQUIRED_ACTIONS as $actionClass) {
            try {
                if (! resolve($actionClass) instanceof $actionClass) {
                    $actions[] = $actionClass;
                }
            } catch (Throwable) {
                $actions[] = $actionClass;
            }
        }

        return $actions;
    }

    public function cacheStoreReachable(): bool
    {
        try {
            Cache::store($this->configuredCacheStoreName());

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function configuredCacheStoreName(): ?string
    {
        $store = config('capell-filament-peek.preview.cache_store');

        return is_string($store) && $store !== '' ? $store : null;
    }
}
