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
use Symfony\Component\Process\Process;
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
            $check->manifestStorageWritableCheck(),
            $check->criticalCssStorageWritableCheck(),
            $check->generatorReadinessCheck(),
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
            label: (string) __('capell-frontend-optimizer::health.renderer_binding.label'),
            passed: $isBound,
            message: $isBound
                ? (string) __('capell-frontend-optimizer::health.renderer_binding.passed')
                : (string) __('capell-frontend-optimizer::health.renderer_binding.failed'),
            remediation: $isBound
                ? null
                : (string) __('capell-frontend-optimizer::health.renderer_binding.remediation'),
        );
    }

    /**
     * Asserts the storage disk used for render-profile manifests and generated
     * critical CSS is writable, since both are written to disk during operation.
     */
    public function manifestStorageWritableCheck(): DoctorCheckResultData
    {
        $isWritable = $this->storagePathIsWritable($this->manifestDirectory());

        return new DoctorCheckResultData(
            label: (string) __('capell-frontend-optimizer::health.manifest_storage.label'),
            passed: $isWritable,
            message: $isWritable
                ? (string) __('capell-frontend-optimizer::health.manifest_storage.passed')
                : (string) __('capell-frontend-optimizer::health.manifest_storage.failed'),
            remediation: $isWritable
                ? null
                : (string) __('capell-frontend-optimizer::health.manifest_storage.remediation'),
        );
    }

    public function criticalCssStorageWritableCheck(): DoctorCheckResultData
    {
        $isWritable = $this->storagePathIsWritable($this->criticalCssDirectory());

        return new DoctorCheckResultData(
            label: (string) __('capell-frontend-optimizer::health.critical_css_storage.label'),
            passed: $isWritable,
            message: $isWritable
                ? (string) __('capell-frontend-optimizer::health.critical_css_storage.passed')
                : (string) __('capell-frontend-optimizer::health.critical_css_storage.failed'),
            remediation: $isWritable
                ? null
                : (string) __('capell-frontend-optimizer::health.critical_css_storage.remediation'),
        );
    }

    public function generatorReadinessCheck(): DoctorCheckResultData
    {
        $isReady = $this->generatorIsReady();

        return new DoctorCheckResultData(
            label: (string) __('capell-frontend-optimizer::health.generator.label'),
            passed: $isReady,
            message: $isReady
                ? (string) __('capell-frontend-optimizer::health.generator.passed')
                : (string) __('capell-frontend-optimizer::health.generator.failed'),
            remediation: $isReady
                ? null
                : (string) __('capell-frontend-optimizer::health.generator.remediation'),
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
            label: (string) __('capell-frontend-optimizer::health.queue_driver.label'),
            passed: $isSafe,
            message: $isSafe
                ? (string) __('capell-frontend-optimizer::health.queue_driver.passed')
                : (string) __('capell-frontend-optimizer::health.queue_driver.failed'),
            remediation: $isSafe
                ? null
                : (string) __('capell-frontend-optimizer::health.queue_driver.remediation'),
        );
    }

    public function optimizerRendererIsBound(): bool
    {
        try {
            return is_a(app(FrontendAssetManifestRenderer::class), CapellFrontendAssetManifestRenderer::class);
        } catch (Throwable) {
            return false;
        }
    }

    public function storageDiskIsWritable(): bool
    {
        return $this->storagePathIsWritable($this->manifestDirectory())
            && $this->storagePathIsWritable($this->criticalCssDirectory());
    }

    public function generatorIsReady(): bool
    {
        return $this->nodeBinaryCanRun()
            && $this->generatorScriptExists()
            && $this->playwrightDependencyIsDeclared();
    }

    public function nodeBinaryCanRun(): bool
    {
        try {
            $process = new Process([$this->nodeBinary(), '--version']);
            $process->setTimeout(5);
            $process->run();

            return $process->isSuccessful()
                && preg_match('/^v?\d+\.\d+\.\d+/', trim($process->getOutput())) === 1;
        } catch (Throwable) {
            return false;
        }
    }

    public function generatorScriptExists(): bool
    {
        $script = config('capell-frontend-optimizer.playwright.script');

        return is_string($script) && $script !== '' && is_file($script);
    }

    public function playwrightDependencyIsDeclared(): bool
    {
        try {
            $packageJson = json_decode((string) file_get_contents($this->packagePath('package.json')), true, flags: JSON_THROW_ON_ERROR);

            if (! is_array($packageJson)) {
                return false;
            }

            $dependencies = $packageJson['dependencies'] ?? [];
            $developmentDependencies = $packageJson['devDependencies'] ?? [];

            return (is_array($dependencies) && array_key_exists('playwright', $dependencies))
                || (is_array($developmentDependencies) && array_key_exists('playwright', $developmentDependencies));
        } catch (Throwable) {
            return false;
        }
    }

    public function storagePathIsWritable(string $directory): bool
    {
        try {
            $disk = $this->storageDisk();
            $probePath = trim($directory, '/') . '/.frontend-optimizer-health-probe-' . bin2hex(random_bytes(8));

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

    private function criticalCssDirectory(): string
    {
        $directory = config('capell-frontend-optimizer.paths.critical_css', 'capell/frontend-optimizer/critical-css');

        return is_string($directory) && $directory !== ''
            ? trim($directory, '/')
            : 'capell/frontend-optimizer/critical-css';
    }

    private function nodeBinary(): string
    {
        $nodeBinary = config('capell-frontend-optimizer.playwright.node_binary', 'node');

        return is_string($nodeBinary) && $nodeBinary !== '' ? $nodeBinary : 'node';
    }

    private function packagePath(string $path): string
    {
        return dirname(__DIR__, 2) . '/' . ltrim($path, '/');
    }
}
