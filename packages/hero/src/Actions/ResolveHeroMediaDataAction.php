<?php

declare(strict_types=1);

namespace Capell\Hero\Actions;

use Capell\Core\Models\Media;
use Capell\Core\Models\Theme;
use Capell\Hero\Data\HeroMediaData;
use Capell\LayoutBuilder\Models\Widget;
use Capell\LayoutBuilder\Models\WidgetAsset;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsObject;

/**
 * @method static HeroMediaData run(?Theme $theme = null, ?Widget $widget = null, ?WidgetAsset $asset = null)
 */
final class ResolveHeroMediaDataAction
{
    use AsObject;

    public function handle(?Theme $theme = null, ?Widget $widget = null, ?WidgetAsset $asset = null): HeroMediaData
    {
        $settings = [
            'enabled' => false,
            'autoplay' => true,
            'loop' => true,
            'muted' => true,
            'pause_when_out_of_view' => true,
            'preload' => HeroMediaData::PreloadMetadata,
        ];

        $videos = [];
        $images = [];

        foreach ($this->layers($theme, $widget, $asset) as $layer) {
            $mode = $this->stringValue($layer['settings']['mode'] ?? null);

            if ($mode === HeroMediaData::ModeOff) {
                return HeroMediaData::disabled();
            }

            if ($mode !== HeroMediaData::ModeCustom) {
                continue;
            }

            $settings = $this->mergeSettings($settings, $layer['settings']);
            $videos = $this->mergeMedia($videos, $this->resolveMedia($layer['model'], HeroMediaData::videoCollections()));
            $images = $this->mergeMedia($images, $this->resolveMedia($layer['model'], HeroMediaData::imageCollections()));
        }

        return new HeroMediaData(
            enabled: (bool) $settings['enabled'],
            autoplay: (bool) $settings['autoplay'],
            loop: (bool) $settings['loop'],
            muted: (bool) $settings['muted'],
            pauseWhenOutOfView: (bool) $settings['pause_when_out_of_view'],
            preload: (string) $settings['preload'],
            videos: $videos,
            images: $images,
        );
    }

    /**
     * @return list<array{model: Theme|Widget|WidgetAsset, settings: array<string, mixed>}>
     */
    private function layers(?Theme $theme, ?Widget $widget, ?WidgetAsset $asset): array
    {
        return array_values(collect([$theme, $widget, $asset])
            ->filter(fn (Theme|Widget|WidgetAsset|null $model): bool => $model !== null)
            ->map(function (Theme|Widget|WidgetAsset $model): ?array {
                $settings = $model->getMeta('hero_media', []);

                return is_array($settings) ? ['model' => $model, 'settings' => $settings] : null;
            })
            ->filter()
            ->values()
            ->all());
    }

    /**
     * @param  array<string, mixed>  $base
     * @param  array<string, mixed>  $settings
     * @return array<string, mixed>
     */
    private function mergeSettings(array $base, array $settings): array
    {
        foreach (['autoplay', 'loop', 'muted', 'pause_when_out_of_view'] as $key) {
            if (array_key_exists($key, $settings)) {
                $base[$key] = filter_var($settings[$key], FILTER_VALIDATE_BOOL);
            }
        }

        $preload = $this->stringValue($settings['preload'] ?? null);
        if (in_array($preload, [HeroMediaData::PreloadNone, HeroMediaData::PreloadMetadata, HeroMediaData::PreloadAuto], true)) {
            $base['preload'] = $preload;
        }

        $base['enabled'] = true;

        return $base;
    }

    /**
     * @param  array<string, Media|null>  $base
     * @param  array<string, Media|null>  $media
     * @return array<string, Media|null>
     */
    private function mergeMedia(array $base, array $media): array
    {
        foreach ($media as $viewport => $item) {
            if ($item instanceof Media) {
                $base[$viewport] = $item;
            }
        }

        return $base;
    }

    /**
     * @param  array<string, string>  $collections
     * @return array<string, Media|null>
     */
    private function resolveMedia(Theme|Widget|WidgetAsset $model, array $collections): array
    {
        if (! $model->relationLoaded('media')) {
            return [];
        }

        $loadedMedia = $model->getRelation('media');
        if (! $loadedMedia instanceof Collection) {
            return [];
        }

        return collect($collections)
            ->mapWithKeys(fn (string $collection, string $viewport): array => [
                $viewport => $loadedMedia->firstWhere('collection_name', $collection),
            ])
            ->filter(fn (mixed $media): bool => $media instanceof Media)
            ->all();
    }

    private function stringValue(mixed $value): ?string
    {
        return is_string($value) && $value !== '' ? $value : null;
    }
}
