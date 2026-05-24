<?php

declare(strict_types=1);

namespace Capell\FoundationTheme\Support\Demo;

final class ThemeDemoMedia
{
    /**
     * @return array<int, string>
     */
    public static function forTheme(string $themeKey): array
    {
        return array_values(array_merge(...array_values(self::groupedForTheme($themeKey))));
    }

    /**
     * @return array{hero: array<int, string>, listing: array<int, string>, detail: array<int, string>, proof: array<int, string>, contact: array<int, string>, cta: array<int, string>}
     */
    public static function groupedForTheme(string $themeKey): array
    {
        $normalizedThemeKey = strtolower(trim($themeKey));
        $catalogue = self::catalogue();

        if (array_key_exists($normalizedThemeKey, $catalogue)) {
            return $catalogue[$normalizedThemeKey];
        }

        return $catalogue['default'];
    }

    /**
     * @return array<string, array{hero: array<int, string>, listing: array<int, string>, detail: array<int, string>, proof: array<int, string>, contact: array<int, string>, cta: array<int, string>}>
     */
    private static function catalogue(): array
    {
        return [
            'default' => [
                'hero' => [
                    'https://images.unsplash.com/photo-1497366754035-f200968a6e72?auto=format&fit=crop&w=1800&q=80',
                ],
                'listing' => [
                    'https://images.unsplash.com/photo-1497215842964-222b430dc094?auto=format&fit=crop&w=1200&q=80',
                ],
                'detail' => [
                    'https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&w=1400&q=80',
                ],
                'proof' => [
                    'https://images.unsplash.com/photo-1556761175-b413da4baf72?auto=format&fit=crop&w=1200&q=80',
                ],
                'contact' => [
                    'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=1200&q=80',
                ],
                'cta' => [
                    'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?auto=format&fit=crop&w=1400&q=80',
                ],
            ],
            'agency' => [
                'hero' => [
                    'https://images.unsplash.com/photo-1556761175-4b46a572b786?auto=format&fit=crop&w=1800&q=80',
                ],
                'listing' => [
                    'https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=1200&q=80',
                ],
                'detail' => [
                    'https://images.unsplash.com/photo-1542744173-8e7e53415bb0?auto=format&fit=crop&w=1400&q=80',
                ],
                'proof' => [
                    'https://images.unsplash.com/photo-1559136555-9303baea8ebd?auto=format&fit=crop&w=1200&q=80',
                ],
                'contact' => [
                    'https://images.unsplash.com/photo-1519389950473-47ba0277781c?auto=format&fit=crop&w=1200&q=80',
                ],
                'cta' => [
                    'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=1400&q=80',
                ],
            ],
            'corporate' => [
                'hero' => [
                    'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=1800&q=80',
                ],
                'listing' => [
                    'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1200&q=80',
                ],
                'detail' => [
                    'https://images.unsplash.com/photo-1504384308090-c894fdcc538d?auto=format&fit=crop&w=1400&q=80',
                ],
                'proof' => [
                    'https://images.unsplash.com/photo-1551836022-d5d88e9218df?auto=format&fit=crop&w=1200&q=80',
                ],
                'contact' => [
                    'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?auto=format&fit=crop&w=1200&q=80',
                ],
                'cta' => [
                    'https://images.unsplash.com/photo-1556761175-5973dc0f32e7?auto=format&fit=crop&w=1400&q=80',
                ],
            ],
            'saas' => [
                'hero' => [
                    'https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=1800&q=80',
                ],
                'listing' => [
                    'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=1200&q=80',
                ],
                'detail' => [
                    'https://images.unsplash.com/photo-1519389950473-47ba0277781c?auto=format&fit=crop&w=1400&q=80',
                ],
                'proof' => [
                    'https://images.unsplash.com/photo-1551434678-e076c223a692?auto=format&fit=crop&w=1200&q=80',
                ],
                'contact' => [
                    'https://images.unsplash.com/photo-1553877522-43269d4ea984?auto=format&fit=crop&w=1200&q=80',
                ],
                'cta' => [
                    'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=1400&q=80',
                ],
            ],
            'commerce' => [
                'hero' => [
                    'https://images.unsplash.com/photo-1441986300917-64674bd600d8?auto=format&fit=crop&w=1800&q=80',
                ],
                'listing' => [
                    'https://images.unsplash.com/photo-1472851294608-062f824d29cc?auto=format&fit=crop&w=1200&q=80',
                ],
                'detail' => [
                    'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=1400&q=80',
                ],
                'proof' => [
                    'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=1200&q=80',
                ],
                'contact' => [
                    'https://images.unsplash.com/photo-1528698827591-e19ccd7bc23d?auto=format&fit=crop&w=1200&q=80',
                ],
                'cta' => [
                    'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?auto=format&fit=crop&w=1400&q=80',
                ],
            ],
            'healthcare' => [
                'hero' => [
                    'https://images.unsplash.com/photo-1505751172876-fa1923c5c528?auto=format&fit=crop&w=1800&q=80',
                ],
                'listing' => [
                    'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&fit=crop&w=1200&q=80',
                ],
                'detail' => [
                    'https://images.unsplash.com/photo-1584515933487-779824d29309?auto=format&fit=crop&w=1400&q=80',
                ],
                'proof' => [
                    'https://images.unsplash.com/photo-1550831107-1553da8c8464?auto=format&fit=crop&w=1200&q=80',
                ],
                'contact' => [
                    'https://images.unsplash.com/photo-1581056771107-24ca5f033842?auto=format&fit=crop&w=1200&q=80',
                ],
                'cta' => [
                    'https://images.unsplash.com/photo-1526256262350-7da7584cf5eb?auto=format&fit=crop&w=1400&q=80',
                ],
            ],
        ];
    }
}
