<?php

declare(strict_types=1);

namespace Capell\FrontendOptimizer\Support;

use Capell\FrontendOptimizer\Enums\AssetKind;
use Capell\FrontendOptimizer\Enums\AssetLoadingStrategy;
use Capell\FrontendOptimizer\Models\FrontendRenderProfile;
use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Support\HtmlString;

class RenderProfileAssetRenderer
{
    private const string STABILITY_RESET_CSS = 'body { margin: 0; }';

    public function __construct(
        private readonly Factory $filesystems,
        private readonly CriticalCssSettings $criticalCssSettings,
    ) {}

    public function render(string $profileHash): HtmlString
    {
        $profile = FrontendRenderProfile::query()->where('hash', $profileHash)->first();

        if (! $profile instanceof FrontendRenderProfile) {
            return new HtmlString('');
        }

        $html = [];
        $hasInlineCriticalCss = false;

        foreach ($this->resourceHintsFromProfile($profile) as $hint) {
            $html[] = $this->renderResourceHint($hint);
        }

        if ($this->shouldInlineCriticalCss($profile)) {
            $hasInlineCriticalCss = true;
            $html[] = '<style data-critical-css>' . $this->escapeStyleContents($this->criticalCssContents($profile)) . '</style>';
        }

        foreach ($this->assetsFromProfile($profile) as $asset) {
            $html[] = $this->renderAsset($asset, $hasInlineCriticalCss);
        }

        return new HtmlString(implode(PHP_EOL, array_filter($html, static fn (string $tag): bool => $tag !== '')));
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function resourceHintsFromProfile(FrontendRenderProfile $profile): array
    {
        $resourceHints = $profile->signature['resource_hints'] ?? [];

        if (! is_array($resourceHints)) {
            return [];
        }

        $hints = [];

        foreach ($resourceHints as $resourceHint) {
            if (is_array($resourceHint)) {
                $hints[] = $this->stringKeyMap($resourceHint);
            }
        }

        return $hints;
    }

    /** @param array<string, mixed> $hint */
    private function renderResourceHint(array $hint): string
    {
        $rel = $this->escape($this->stringValue($hint, 'rel'));
        $href = $this->escape($this->stringValue($hint, 'href'));

        if ($rel === '' || $href === '') {
            return '';
        }

        $attributes = [
            'rel' => $rel,
            'href' => $href,
            'as' => $this->escape($this->stringValue($hint, 'as')),
            'type' => $this->escape($this->stringValue($hint, 'type')),
            'crossorigin' => $this->escape($this->stringValue($hint, 'crossorigin')),
            'fetchpriority' => $this->escape($this->stringValue($hint, 'fetchpriority')),
        ];

        return '<link ' . collect($attributes)
            ->filter(static fn (string $value): bool => $value !== '')
            ->map(static fn (string $value, string $key): string => sprintf('%s="%s"', $key, $value))
            ->implode(' ') . '>';
    }

    private function shouldInlineCriticalCss(FrontendRenderProfile $profile): bool
    {
        if (! $this->criticalCssSettings->enabled()) {
            return false;
        }

        if ($this->criticalCssSettings->profileDisablesCriticalCss($profile->signature)) {
            return false;
        }

        if (! is_string($profile->critical_css_path) || $profile->critical_css_path === '') {
            return false;
        }

        if (! $this->filesystems->disk('local')->exists($profile->critical_css_path)) {
            return false;
        }

        return strlen((string) $this->filesystems->disk('local')->get($profile->critical_css_path)) <= $this->criticalCssSettings->maxInlineCssBytes();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function assetsFromProfile(FrontendRenderProfile $profile): array
    {
        $assets = $profile->signature['assets'] ?? [];

        if (! is_array($assets)) {
            return [];
        }

        $normalizedAssets = [];

        foreach ($assets as $asset) {
            if (is_array($asset)) {
                $normalizedAssets[] = $this->stringKeyMap($asset);
            }
        }

        return $normalizedAssets;
    }

    /** @param array<string, mixed> $asset */
    private function renderAsset(array $asset, bool $hasInlineCriticalCss): string
    {
        $kind = AssetKind::tryFrom($this->stringValue($asset, 'kind'));

        return match ($kind) {
            AssetKind::Css => $this->renderCss($asset, $hasInlineCriticalCss),
            AssetKind::Js => $this->renderJs($asset),
            default => '',
        };
    }

    /** @param array<string, mixed> $asset */
    private function renderCss(array $asset, bool $hasInlineCriticalCss): string
    {
        $href = $this->escape($this->stringValue($asset, 'path'));
        $strategy = AssetLoadingStrategy::tryFrom($this->stringValue($asset, 'loading_strategy')) ?? AssetLoadingStrategy::Deferred;

        if ($href === '') {
            return '';
        }

        if ($this->shouldBlockUntilCriticalCssExists($asset, $hasInlineCriticalCss)) {
            return sprintf('<link rel="stylesheet" href="%s">', $href);
        }

        return match ($strategy) {
            AssetLoadingStrategy::Critical => $hasInlineCriticalCss ? '' : sprintf('<link rel="stylesheet" href="%s">', $href),
            AssetLoadingStrategy::Blocking => sprintf('<link rel="stylesheet" href="%s">', $href),
            AssetLoadingStrategy::Preload => sprintf('<link rel="preload" as="style" href="%s" onload="this.onload=null;this.rel=\'stylesheet\'"><noscript><link rel="stylesheet" href="%s"></noscript>', $href, $href),
            AssetLoadingStrategy::Deferred,
            AssetLoadingStrategy::Lazy,
            AssetLoadingStrategy::Interaction,
            AssetLoadingStrategy::Idle => sprintf('<link rel="stylesheet" href="%s" media="print" onload="this.media=\'all\'"><noscript><link rel="stylesheet" href="%s"></noscript>', $href, $href),
        };
    }

    /** @param array<string, mixed> $asset */
    private function shouldBlockUntilCriticalCssExists(array $asset, bool $hasInlineCriticalCss): bool
    {
        if ($hasInlineCriticalCss) {
            return false;
        }

        if (($asset['critical_eligible'] ?? false) !== true) {
            return false;
        }

        $strategy = AssetLoadingStrategy::tryFrom($this->stringValue($asset, 'loading_strategy')) ?? AssetLoadingStrategy::Deferred;

        return $strategy === AssetLoadingStrategy::Deferred;
    }

    /** @param array<string, mixed> $asset */
    private function renderJs(array $asset): string
    {
        $src = $this->escape($this->stringValue($asset, 'path'));
        $strategy = AssetLoadingStrategy::tryFrom($this->stringValue($asset, 'loading_strategy')) ?? AssetLoadingStrategy::Deferred;

        if ($src === '') {
            return '';
        }

        return match ($strategy) {
            AssetLoadingStrategy::Blocking => sprintf('<script src="%s"></script>', $src),
            AssetLoadingStrategy::Interaction => $this->renderIdleScript($src),
            AssetLoadingStrategy::Idle => $this->renderIdleScript($src),
            AssetLoadingStrategy::Lazy,
            AssetLoadingStrategy::Critical,
            AssetLoadingStrategy::Preload,
            AssetLoadingStrategy::Deferred => sprintf('<script type="module" defer src="%s"></script>', $src),
        };
    }

    private function renderIdleScript(string $src): string
    {
        $jsonSrc = json_encode($src, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

        if (! is_string($jsonSrc)) {
            return '';
        }

        return sprintf(
            '<script type="module">(()=>{const load=()=>import(%s);("requestIdleCallback"in window?window.requestIdleCallback(load,{timeout:1800}):window.setTimeout(load,900));})();</script>',
            $jsonSrc,
        );
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    private function escapeStyleContents(string $value): string
    {
        return str_ireplace('</style', '<\\/style', $value);
    }

    /**
     * @param  array<array-key, mixed>  $values
     * @return array<string, mixed>
     */
    private function stringKeyMap(array $values): array
    {
        $map = [];

        foreach ($values as $key => $value) {
            if (is_string($key)) {
                $map[$key] = $value;
            }
        }

        return $map;
    }

    /**
     * @param  array<string, mixed>  $values
     */
    private function stringValue(array $values, string $key): string
    {
        $value = $values[$key] ?? null;

        return is_string($value) ? $value : '';
    }

    private function criticalCssContents(FrontendRenderProfile $profile): string
    {
        return self::STABILITY_RESET_CSS . PHP_EOL . $this->sanitizeInlineCriticalCss(
            (string) $this->filesystems->disk('local')->get((string) $profile->critical_css_path),
        );
    }

    private function sanitizeInlineCriticalCss(string $css): string
    {
        $css = preg_replace('/\s*@property\s+--[^{]+\{[^}]*\}/', '', $css) ?? $css;

        $css = preg_replace(
            '/\s*\.\\\\@container,\s*\.\\\\\[container-type\\\\:inline-size\\\\\]\s*\{\s*container-type:\s*inline-size;\s*\}/',
            '',
            $css,
        ) ?? $css;

        return preg_replace(
            '/\s+and\s+\(not\s+\(margin-trim:\s*inline\)\)/',
            ' and (not (color:rgb(from red r g b)))',
            $css,
        ) ?? $css;
    }
}
