<?php

declare(strict_types=1);

namespace Capell\FrontendOptimizer\Actions;

use Illuminate\Support\HtmlString;
use Lorisleiva\Actions\Concerns\AsAction;
use Stringable;

/**
 * @method static HtmlString run(string $src, string $alt = '', ?int $width = null, ?int $height = null, ?string $sizes = null, array<int, int> $widths = [], bool $eager = false, array<string, mixed> $attributes = [])
 */
final class BuildOptimizedImageMarkupAction
{
    use AsAction;

    /**
     * @param  array<int, int>  $widths
     * @param  array<string, mixed>  $attributes
     */
    public function handle(
        string $src,
        string $alt = '',
        ?int $width = null,
        ?int $height = null,
        ?string $sizes = null,
        array $widths = [],
        bool $eager = false,
        array $attributes = [],
    ): HtmlString {
        $source = trim($src);

        if ($source === '' || ! $this->isSafeSource($source)) {
            return new HtmlString('');
        }

        $candidateWidths = $this->candidateWidths($widths, $width);
        $imageAttributes = $this->imageAttributes($source, $alt, $width, $height, $sizes, $candidateWidths, $eager, $attributes);
        $imageHtml = '<img ' . $this->attributes($imageAttributes) . '>';

        if (! config('capell-frontend-optimizer.images.enabled', true) || ! $this->canRenderFormatSources($source) || $candidateWidths === []) {
            return new HtmlString($imageHtml);
        }

        $sources = [];

        foreach ($this->formats() as $format) {
            $sourceSet = $this->sourceSet($source, $candidateWidths, $format);

            if ($sourceSet === '') {
                continue;
            }

            $sourceAttributes = [
                'type' => 'image/' . $format,
                'srcset' => $sourceSet,
                'sizes' => $sizes,
            ];

            $sources[] = '<source ' . $this->attributes($sourceAttributes) . '>';
        }

        if ($sources === []) {
            return new HtmlString($imageHtml);
        }

        return new HtmlString('<picture>' . implode('', $sources) . $imageHtml . '</picture>');
    }

    /**
     * @param  array<int, int>  $widths
     * @return list<int>
     */
    private function candidateWidths(array $widths, ?int $width): array
    {
        $configuredWidths = $widths !== []
            ? $widths
            : config('capell-frontend-optimizer.images.widths', []);

        $candidates = [];

        if (is_array($configuredWidths)) {
            foreach ($configuredWidths as $candidateWidth) {
                if (is_int($candidateWidth) && $candidateWidth > 0) {
                    $candidates[] = $candidateWidth;
                }
            }
        }

        if ($width !== null && $width > 0) {
            $candidates[] = $width;
        }

        $candidates = array_values(array_unique($candidates));
        sort($candidates);

        return $candidates;
    }

    /**
     * @param  list<int>  $candidateWidths
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function imageAttributes(
        string $source,
        string $alt,
        ?int $width,
        ?int $height,
        ?string $sizes,
        array $candidateWidths,
        bool $eager,
        array $attributes,
    ): array {
        return array_merge($attributes, [
            'src' => $source,
            'srcset' => $candidateWidths === [] ? null : $this->sourceSet($source, $candidateWidths),
            'sizes' => $sizes,
            'alt' => $alt,
            'width' => $width,
            'height' => $height,
            'loading' => $eager ? 'eager' : 'lazy',
            'decoding' => 'async',
            'fetchpriority' => $eager ? 'high' : null,
        ]);
    }

    /**
     * @return list<string>
     */
    private function formats(): array
    {
        $configuredFormats = config('capell-frontend-optimizer.images.formats', []);

        if (! is_array($configuredFormats)) {
            return [];
        }

        $formats = [];

        foreach ($configuredFormats as $format) {
            if (! is_string($format)) {
                continue;
            }

            $normalizedFormat = strtolower($format);

            if (in_array($normalizedFormat, ['avif', 'webp'], true)) {
                $formats[] = $normalizedFormat;
            }
        }

        return array_values(array_unique($formats));
    }

    /**
     * @param  list<int>  $widths
     */
    private function sourceSet(string $source, array $widths, ?string $format = null): string
    {
        $candidates = [];

        foreach ($widths as $width) {
            $candidates[] = sprintf('%s %dw', $this->variantUrl($source, $width, $format), $width);
        }

        return implode(', ', $candidates);
    }

    private function variantUrl(string $source, int $width, ?string $format): string
    {
        $parameters = [
            $this->configString('capell-frontend-optimizer.images.width_parameter', 'w') => $width,
        ];

        if ($format !== null) {
            $parameters[$this->configString('capell-frontend-optimizer.images.format_parameter', 'format')] = $format;
        }

        $quality = config('capell-frontend-optimizer.images.quality');

        if (is_int($quality) && $quality > 0) {
            $parameters[$this->configString('capell-frontend-optimizer.images.quality_parameter', 'q')] = $quality;
        }

        $fragment = '';
        $baseSource = $source;

        if (str_contains($baseSource, '#')) {
            [$baseSource, $fragment] = explode('#', $baseSource, 2);
            $fragment = '#' . $fragment;
        }

        $separator = str_contains($baseSource, '?') ? '&' : '?';

        return $baseSource . $separator . http_build_query($parameters, '', '&', PHP_QUERY_RFC3986) . $fragment;
    }

    private function isSafeSource(string $source): bool
    {
        $scheme = parse_url($source, PHP_URL_SCHEME);

        if (! is_string($scheme)) {
            return true;
        }

        return in_array(strtolower($scheme), ['http', 'https'], true);
    }

    private function canRenderFormatSources(string $source): bool
    {
        $path = parse_url($source, PHP_URL_PATH);

        if (! is_string($path)) {
            return false;
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function attributes(array $attributes): string
    {
        $rendered = [];

        foreach ($attributes as $name => $value) {
            if (! $this->isSafeAttributeName($name) || $value === null || $value === false) {
                continue;
            }

            if ($value === true) {
                $rendered[] = $name;

                continue;
            }

            if (! is_scalar($value) && ! $value instanceof Stringable) {
                continue;
            }

            $rendered[] = sprintf('%s="%s"', $name, e((string) $value));
        }

        return implode(' ', $rendered);
    }

    private function isSafeAttributeName(string $name): bool
    {
        return preg_match('/^[A-Za-z_:][A-Za-z0-9_:.\\-]*$/', $name) === 1;
    }

    private function configString(string $key, string $default): string
    {
        $value = config($key, $default);

        return is_string($value) && $value !== '' ? $value : $default;
    }
}
