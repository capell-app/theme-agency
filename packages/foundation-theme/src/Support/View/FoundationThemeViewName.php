<?php

declare(strict_types=1);

namespace Capell\FoundationTheme\Support\View;

final class FoundationThemeViewName
{
    public static function canonical(string $view): string
    {
        foreach (self::legacyViewPrefixes() as $legacyPrefix => $canonicalPrefix) {
            if (str_starts_with($view, $legacyPrefix)) {
                return $canonicalPrefix . substr($view, strlen($legacyPrefix));
            }
        }

        return $view;
    }

    /**
     * @return array<string, string>
     */
    private static function legacyViewPrefixes(): array
    {
        return [
            'capell-layout-builder::components.widget.' => 'capell-foundation-theme::components.widget.',
            'capell-layout-builder::components.layout.' => 'capell-foundation-theme::components.layout.',
            'capell-layout-builder::components.actions.' => 'capell-foundation-theme::components.actions.',
            'capell-layout-builder::layout.' => 'capell-foundation-theme::components.layout.',
            'capell-layout-builder::widget.' => 'capell-foundation-theme::components.widget.',
            'components.widget.' => 'capell-foundation-theme::components.widget.',
            'components.layout.' => 'capell-foundation-theme::components.layout.',
            'components.actions.' => 'capell-foundation-theme::components.actions.',
        ];
    }
}
