<?php

declare(strict_types=1);

namespace Capell\Hero\Actions;

use Capell\Core\Models\Theme;
use Capell\Hero\Data\HeroBackgroundData;
use Capell\LayoutBuilder\Models\Widget;
use Capell\LayoutBuilder\Models\WidgetAsset;
use Lorisleiva\Actions\Concerns\AsObject;

/**
 * @method static HeroBackgroundData run(?Theme $theme = null, ?Widget $widget = null, ?WidgetAsset $asset = null)
 */
final class ResolveHeroBackgroundDataAction
{
    use AsObject;

    public function handle(?Theme $theme = null, ?Widget $widget = null, ?WidgetAsset $asset = null): HeroBackgroundData
    {
        return $this->forAsset($this->base($theme, $widget), $asset);
    }

    public function base(?Theme $theme = null, ?Widget $widget = null): HeroBackgroundData
    {
        return $this->resolveLayers($this->layers($theme, $widget));
    }

    public function forAsset(HeroBackgroundData $base, ?WidgetAsset $asset = null): HeroBackgroundData
    {
        return $this->resolveLayers($this->layers(asset: $asset), $base);
    }

    /**
     * @param  list<array<string, mixed>>  $layers
     */
    private function resolveLayers(array $layers, ?HeroBackgroundData $base = null): HeroBackgroundData
    {
        $background = $base ?? HeroBackgroundData::defaults();

        foreach ($layers as $layer) {
            $mode = $this->stringValue($layer['mode'] ?? null);

            if ($mode === HeroBackgroundData::ModeOff) {
                return HeroBackgroundData::disabled();
            }

            if (! in_array($mode, [HeroBackgroundData::ModeDefault, HeroBackgroundData::ModeCustom], true)) {
                continue;
            }

            if ($mode === HeroBackgroundData::ModeCustom) {
                $background = $this->merge($background, $layer);
            }
        }

        return $background;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function layers(?Theme $theme = null, ?Widget $widget = null, ?WidgetAsset $asset = null): array
    {
        return array_values(array_filter([
            $this->settings($theme?->getMeta('hero_background', [])),
            $this->settings($widget?->getMeta('hero_background', [])),
            $this->settings($asset?->getMeta('hero_background', [])),
        ]));
    }

    /**
     * @return array<string, mixed>|null
     */
    private function settings(mixed $settings): ?array
    {
        return is_array($settings) ? $settings : null;
    }

    /**
     * @param  array<string, mixed>  $settings
     */
    private function merge(HeroBackgroundData $background, array $settings): HeroBackgroundData
    {
        return new HeroBackgroundData(
            enabled: true,
            backgroundColor: $this->hexColor($settings['background_color'] ?? null, $background->backgroundColor),
            overlayStyle: $this->overlayStyle($settings['overlay_style'] ?? null, $background->overlayStyle),
            overlayOpacity: $this->opacity($settings['overlay_opacity'] ?? null, $background->overlayOpacity),
            accentColor: $this->hexColor($settings['accent_color'] ?? null, $background->accentColor),
            accentColorAlt: $this->hexColor($settings['accent_color_alt'] ?? null, $background->accentColorAlt),
        );
    }

    private function hexColor(mixed $value, string $fallback): string
    {
        $value = $this->stringValue($value);

        if ($value === null || ! preg_match('/^#[0-9a-fA-F]{3}([0-9a-fA-F]{3})?$/', $value)) {
            return $fallback;
        }

        return strtolower($value);
    }

    private function overlayStyle(mixed $value, string $fallback): string
    {
        $value = $this->stringValue($value);

        return in_array($value, HeroBackgroundData::OverlayStyles, true) ? $value : $fallback;
    }

    private function opacity(mixed $value, float $fallback): float
    {
        if (! is_numeric($value)) {
            return $fallback;
        }

        return max(0.0, min(1.0, round((float) $value, 2)));
    }

    private function stringValue(mixed $value): ?string
    {
        return is_string($value) && $value !== '' ? $value : null;
    }
}
