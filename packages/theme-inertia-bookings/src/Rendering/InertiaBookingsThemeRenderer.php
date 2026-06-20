<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\InertiaBookings\Rendering;

use Capell\Core\ThemeStudio\Contracts\ThemeRenderer;
use Capell\Core\ThemeStudio\Contracts\ThemeSection;
use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Capell\Core\ThemeStudio\Data\ThemePageData;
use Capell\Inertia\Facades\CapellInertia;
use Capell\ThemeStudio\InertiaBookings\Providers\InertiaBookingsThemeServiceProvider;

class InertiaBookingsThemeRenderer implements ThemeRenderer
{
    public function themeKey(): string
    {
        return InertiaBookingsThemeServiceProvider::THEME_KEY;
    }

    public function render(ThemePageData $page): string
    {
        return (string) CapellInertia::render(
            $this->stringConfig('capell-inertia.page_component', 'Capell/Page'),
            $this->props($page),
        )->getContent();
    }

    /**
     * @return array<string, mixed>
     */
    public function props(ThemePageData $page): array
    {
        return [
            'page' => [
                'url' => $this->currentUrl(),
                'title' => $page->title,
                'content' => $this->introContent($page),
                'meta' => [
                    'eyebrow' => $this->heroEyebrow($page),
                    'theme' => self::themeKeyValue(),
                ],
                'layout' => [
                    'containers' => [
                        [
                            'key' => 'theme-sections',
                            'layout_widgets' => $this->widgets($page),
                        ],
                    ],
                ],
            ],
            'runtime' => [
                'adapter' => $this->stringConfig('capell-inertia.adapter', 'vue'),
            ],
            'theme' => [
                'key' => self::themeKeyValue(),
                'brand' => $this->brandTokens($page->brand),
            ],
        ];
    }

    private static function themeKeyValue(): string
    {
        return InertiaBookingsThemeServiceProvider::THEME_KEY;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function widgets(ThemePageData $page): array
    {
        $widgets = [];

        foreach ($page->allSections() as $sectionIndex => $section) {
            $widgets[] = $this->widget($section, $sectionIndex + 1);
        }

        return $widgets;
    }

    /**
     * @return array<string, mixed>
     */
    private function widget(ThemeSection $section, int $occurrence): array
    {
        $sectionData = $this->publicData($section);
        $title = $this->firstString($sectionData, ['heading', 'title', 'brandName', 'label']);

        return [
            'key' => $section->key(),
            'occurrence' => $occurrence,
            'component' => 'Capell/Widgets/Content',
            'data' => array_filter([
                ...$sectionData,
                'title' => $title,
                'content' => $this->sectionContent($sectionData),
            ], static fn (mixed $value): bool => $value !== null && $value !== [] && $value !== ''),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function publicData(ThemeSection $section): array
    {
        $data = method_exists($section, 'toArray') ? $section->toArray() : $section->toViewData();

        if (! is_array($data)) {
            return [];
        }

        /** @var array<string, mixed> $normalised */
        $normalised = $this->normalise($data);

        unset($normalised['section']);

        return $normalised;
    }

    private function normalise(mixed $value): mixed
    {
        if ($value instanceof ThemeSection) {
            return $this->publicData($value);
        }

        if ($value instanceof BrandProfileData) {
            return $this->brandTokens($value);
        }

        if (is_array($value)) {
            $normalised = [];

            foreach ($value as $key => $item) {
                $normalisedItem = $this->normalise($item);

                if ($normalisedItem !== null) {
                    $normalised[$key] = $normalisedItem;
                }
            }

            return $normalised;
        }

        if (is_bool($value) || is_int($value) || is_float($value) || is_string($value) || $value === null) {
            return $value;
        }

        return null;
    }

    /**
     * @return array<string, string>
     */
    private function brandTokens(BrandProfileData $brand): array
    {
        return [
            'primaryColor' => $brand->primaryColor,
            'accentColor' => $brand->accentColor,
            'neutralColor' => $brand->neutralColor,
            'surfaceColor' => $brand->surfaceColor,
            'foregroundColor' => $brand->foregroundColor,
            'headingFont' => $brand->headingFont,
            'bodyFont' => $brand->bodyFont,
            'radius' => $brand->radius,
        ];
    }

    private function introContent(ThemePageData $page): ?string
    {
        foreach ($page->sections as $section) {
            if ($section->key() !== 'hero') {
                continue;
            }

            $sectionData = $this->publicData($section);
            $summary = $this->firstString($sectionData, ['summary']);

            return $summary === null ? null : '<p>' . $this->escape($summary) . '</p>';
        }

        return null;
    }

    private function heroEyebrow(ThemePageData $page): ?string
    {
        foreach ($page->sections as $section) {
            if ($section->key() !== 'hero') {
                continue;
            }

            return $this->firstString($this->publicData($section), ['eyebrow']);
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, string>  $keys
     */
    private function firstString(array $data, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = $data[$key] ?? null;

            if (is_string($value) && trim($value) !== '') {
                return $value;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function sectionContent(array $data): ?string
    {
        $summary = $this->firstString($data, ['summary', 'description']);
        $html = $summary === null ? '' : '<p>' . $this->escape($summary) . '</p>';
        $items = $this->itemLabels($data);

        if ($items !== []) {
            $html .= '<ul>';

            foreach ($items as $item) {
                $html .= '<li>' . $this->escape($item) . '</li>';
            }

            $html .= '</ul>';
        }

        return $html === '' ? null : $html;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<int, string>
     */
    private function itemLabels(array $data): array
    {
        $collection = $data['features'] ?? $data['items'] ?? $data['actions'] ?? null;

        if (! is_array($collection)) {
            return [];
        }

        $labels = [];

        foreach ($collection as $item) {
            if (! is_array($item)) {
                continue;
            }

            $label = $this->firstString($this->stringKeyedArray($item), ['title', 'name', 'label', 'metric', 'quote']);

            if ($label !== null) {
                $labels[] = $label;
            }
        }

        return $labels;
    }

    private function stringConfig(string $key, string $default): string
    {
        $value = config($key, $default);

        return is_string($value) ? $value : $default;
    }

    /**
     * @param  array<mixed>  $values
     * @return array<string, mixed>
     */
    private function stringKeyedArray(array $values): array
    {
        $stringKeyed = [];

        foreach ($values as $key => $value) {
            if (is_string($key)) {
                $stringKeyed[$key] = $value;
            }
        }

        return $stringKeyed;
    }

    private function currentUrl(): string
    {
        if (! function_exists('request')) {
            return '/';
        }

        $path = trim((string) request()->path(), '/');

        return $path === '' ? '/' : '/' . $path;
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
