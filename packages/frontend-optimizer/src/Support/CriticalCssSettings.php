<?php

declare(strict_types=1);

namespace Capell\FrontendOptimizer\Support;

use Capell\FrontendOptimizer\Enums\OptimizationScope;
use Capell\FrontendOptimizer\Settings\FrontendOptimizerSettings;
use Illuminate\Support\Arr;
use Throwable;

final class CriticalCssSettings
{
    public function enabled(): bool
    {
        if (config('capell-frontend-optimizer.enabled', true) !== true) {
            return false;
        }

        return $this->setting('enable_critical_css', true);
    }

    public function automaticGenerationEnabled(): bool
    {
        return $this->enabled() && $this->setting('automatic_generation', true);
    }

    public function scope(): OptimizationScope
    {
        $scope = $this->setting('profile_scope', (string) config('capell-frontend-optimizer.scope', OptimizationScope::Layout->value));

        return OptimizationScope::tryFrom((string) $scope) ?? OptimizationScope::Layout;
    }

    /** @return array<int, array{width: int, height: int}> */
    public function viewports(): array
    {
        $viewports = $this->setting('viewports', config('capell-frontend-optimizer.playwright.viewports', []));

        if (! is_array($viewports)) {
            return [];
        }

        return collect($viewports)
            ->map(function (mixed $viewport): ?array {
                if (is_string($viewport) && preg_match('/^(?<width>\d+)x(?<height>\d+)$/', $viewport, $matches) === 1) {
                    return ['width' => (int) $matches['width'], 'height' => (int) $matches['height']];
                }

                if (! is_array($viewport)) {
                    return null;
                }

                $width = (int) ($viewport['width'] ?? 0);
                $height = (int) ($viewport['height'] ?? 0);

                return $width > 0 && $height > 0 ? ['width' => $width, 'height' => $height] : null;
            })
            ->filter()
            ->values()
            ->all();
    }

    public function foldMultiplier(): float
    {
        return max(0.1, (float) $this->setting('fold_multiplier', 1.0));
    }

    public function extraFoldPixels(): int
    {
        return max(0, (int) $this->setting('extra_fold_pixels', 0));
    }

    public function playwrightWaitStrategy(): string
    {
        $strategy = (string) $this->setting('playwright_wait_strategy', 'networkidle');

        return in_array($strategy, ['load', 'domcontentloaded', 'networkidle'], true) ? $strategy : 'networkidle';
    }

    public function playwrightTimeout(): int
    {
        return max(1, (int) $this->setting('playwright_timeout', config('capell-frontend-optimizer.playwright.timeout', 120)));
    }

    public function maxInlineCssBytes(): int
    {
        return max(1, (int) $this->setting('max_inline_css_bytes', 20000));
    }

    public function debugQuerySupportEnabled(): bool
    {
        return $this->setting('debug_query_support', true);
    }

    /** @return array<string, mixed> */
    public function signature(): array
    {
        return [
            'enabled' => $this->enabled(),
            'extra_fold_pixels' => $this->extraFoldPixels(),
            'fold_multiplier' => $this->foldMultiplier(),
            'max_inline_css_bytes' => $this->maxInlineCssBytes(),
            'playwright_wait_strategy' => $this->playwrightWaitStrategy(),
            'profile_scope' => $this->scope()->value,
            'viewports' => $this->viewports(),
        ];
    }

    /** @param array<string, mixed> $context */
    public function pageTypeDisablesCriticalCss(array $context): bool
    {
        if (Arr::get($context, 'meta.frontend_optimizer.disable_critical_css') === true) {
            return true;
        }

        if (Arr::get($context, 'page_type.meta.frontend_optimizer.disable_critical_css') === true) {
            return true;
        }

        return Arr::get($context, 'page_type_meta.frontend_optimizer.disable_critical_css') === true;
    }

    public function profileDisablesCriticalCss(mixed $signature): bool
    {
        if (! is_array($signature)) {
            return false;
        }

        $context = $signature['context'] ?? [];

        return is_array($context) && $this->pageTypeDisablesCriticalCss($context);
    }

    private function settings(): ?FrontendOptimizerSettings
    {
        try {
            return resolve(FrontendOptimizerSettings::class);
        } catch (Throwable) {
            return null;
        }
    }

    private function setting(string $key, mixed $default): mixed
    {
        $settings = $this->settings();

        if ($settings instanceof FrontendOptimizerSettings && property_exists($settings, $key)) {
            return $settings->{$key};
        }

        return $default;
    }
}
