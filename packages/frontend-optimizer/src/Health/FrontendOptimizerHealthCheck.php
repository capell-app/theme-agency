<?php

declare(strict_types=1);

namespace Capell\FrontendOptimizer\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\Frontend\Contracts\FrontendAssetManifestRenderer;
use Capell\FrontendOptimizer\Support\CapellFrontendAssetManifestRenderer;
use Capell\FrontendOptimizer\Support\CriticalCssSettings;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Throwable;

final class FrontendOptimizerHealthCheck implements ChecksExtensionHealth
{
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
            $check->rendererBindingCheck(),
            $check->storageDiskWritableCheck(),
            $check->generationQueueDriverCheck(),
        ]);
    }

    public static function passed(): bool
    {
        return self::runDiagnostics()
            ->every(static fn (DoctorCheckResultData $result): bool => $result->passed);
    }

    /**
     * Asserts the public asset-manifest renderer contract is bound to the optimizer's
     * implementation, so optimization is actually applied to anonymous page output.
     */
    public function rendererBindingCheck(): DoctorCheckResultData
    {
        $isBound = $this->optimizerRendererIsBound();

        return new DoctorCheckResultData(
            label: 'Frontend Optimizer asset renderer binding',
            passed: $isBound,
            message: $isBound
                ? 'The public asset manifest renderer is bound to the Frontend Optimizer.'
                : 'The public asset manifest renderer is not bound to the Frontend Optimizer; pages render with the default renderer and receive no optimization.',
            remediation: $isBound
                ? null
                : 'Ensure the Frontend Optimizer package is installed so its service provider rebinds the FrontendAssetManifestRenderer contract.',
        );
    }

    /**
     * Asserts the storage disk used for render-profile manifests and generated
     * critical CSS is writable, since both are written to disk during operation.
     */
    public function storageDiskWritableCheck(): DoctorCheckResultData
    {
        $isWritable = $this->storageDiskIsWritable();

        return new DoctorCheckResultData(
            label: 'Frontend Optimizer storage disk',
            passed: $isWritable,
            message: $isWritable
                ? 'The local storage disk is writable for render-profile manifests and critical CSS.'
                : 'The local storage disk is not writable; render-profile manifests and generated critical CSS cannot be persisted.',
            remediation: $isWritable
                ? null
                : 'Check filesystem permissions on the local disk so the optimizer can write manifest and critical-CSS files.',
        );
    }

    /**
     * Asserts critical-CSS generation is not bound to the synchronous queue while
     * automatic generation is enabled, which would run the Playwright generator
     * inside the public request and blow the render budget.
     */
    public function generationQueueDriverCheck(): DoctorCheckResultData
    {
        $isSafe = ! $this->generationRunsOnSyncQueue();

        return new DoctorCheckResultData(
            label: 'Frontend Optimizer generation queue driver',
            passed: $isSafe,
            message: $isSafe
                ? 'Critical-CSS generation is dispatched to an asynchronous queue.'
                : 'Automatic critical-CSS generation is enabled while the queue driver is "sync"; generation would run the real browser inside the public request.',
            remediation: $isSafe
                ? null
                : 'Configure a non-sync queue connection or disable automatic critical-CSS generation so the Playwright generator runs out of band.',
        );
    }

    public function optimizerRendererIsBound(): bool
    {
        try {
            return app()->getAlias(FrontendAssetManifestRenderer::class) === CapellFrontendAssetManifestRenderer::class;
        } catch (Throwable) {
            return false;
        }
    }

    public function storageDiskIsWritable(): bool
    {
        try {
            $disk = $this->storageDisk();
            $probePath = $this->manifestDirectory() . '/.frontend-optimizer-health-probe';

            $disk->put($probePath, (string) now()->getTimestamp());
            $exists = $disk->exists($probePath);
            $disk->delete($probePath);

            return $exists;
        } catch (Throwable) {
            return false;
        }
    }

    public function generationRunsOnSyncQueue(): bool
    {
        if (! resolve(CriticalCssSettings::class)->automaticGenerationEnabled()) {
            return false;
        }

        $connection = config('queue.default');

        if (! is_string($connection)) {
            return true;
        }

        return config(sprintf('queue.connections.%s.driver', $connection)) === 'sync';
    }

    private function storageDisk(): Filesystem
    {
        return Storage::disk('local');
    }

    private function manifestDirectory(): string
    {
        $directory = config('capell-frontend-optimizer.paths.manifests', 'capell/frontend-optimizer/manifests');

        return is_string($directory) && $directory !== ''
            ? trim($directory, '/')
            : 'capell/frontend-optimizer/manifests';
    }
}
