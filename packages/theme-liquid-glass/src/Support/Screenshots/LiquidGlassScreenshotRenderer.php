<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\LiquidGlass\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class LiquidGlassScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-liquid-glass::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (LiquidGlassScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-liquid-glass::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#0f766e',
                accentColor: '#f97316',
                neutralColor: '#24313a',
                headingFont: 'manrope',
                bodyFont: 'inter',
                spacing: 'balanced',
                cardStyle: 'elevated',
                navigationStyle: 'standard',
                layoutPresentation: 'editorial',
                mediaTreatment: 'framed',
                radius: 'xl',
                surfaceColor: '#f3fbfa',
                foregroundColor: '#10202a',
                headingScale: 'balanced',
                cardDensity: 'comfortable',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'liquid-glass',
        ])->render();

        return view('capell-theme-liquid-glass::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, LiquidGlassScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'liquid-glass-homepage' => [
                $this->navigation(),
                $this->hero(),
                $this->section('features'),
                $this->section('proof'),
                $this->section('content-listing'),
                $this->section('cta'),
                $this->footer(),
            ],
            'liquid-glass-sections' => [
                $this->navigation(),
                $this->hero(),
                $this->section('features', [
                    'heading' => 'A section rhythm that stays crisp through the glass',
                    'summary' => 'Hero, feature, proof, and CTA panels keep their translucent structure legible from launch to conversion.',
                ]),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'liquid-glass-directory' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'Browse content cards without leaving the glass',
                    'summary' => 'Translucent result cards keep listings legible and structured while the theme stays free of content records.',
                ]),
                $this->section('cta'),
                $this->footer(),
            ],
            'liquid-glass-detail' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'Scan result groups inside clear translucent panels',
                    'summary' => 'A content-detail review pairs grouped cards and proof so visitors can evaluate without losing the glass system.',
                ]),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'liquid-glass-contact' => [
                $this->navigation(),
                $this->section('cta', [
                    'heading' => 'A lead path that stays polished through the glass',
                    'summary' => 'A non-submitting contact CTA proves the lead journey feels native to the theme while using ordinary public page data.',
                ]),
                $this->section('features'),
                $this->footer(),
            ],
            default => [],
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function section(string $sectionKey, array $data = []): LiquidGlassScreenshotSection
    {
        return new LiquidGlassScreenshotSection($sectionKey, [...$this->defaultDataFor($sectionKey), ...$data]);
    }

    /**
     * Representative sample data so every strict section view renders with
     * real-looking content and never iterates over null. The voice matches the
     * modern, translucent "Liquid Glass" theme.
     *
     * @return array<string, mixed>
     */
    private function defaultDataFor(string $sectionKey): array
    {
        return match ($sectionKey) {
            'features' => [
                'heading' => 'Surfaces that stay legible through the glass',
                'summary' => 'Translucent panels keep structure, rhythm, and contrast intact across every public page.',
                'features' => [
                    [
                        'type' => 'Surface',
                        'title' => 'Frosted panels with depth',
                        'description' => 'Layered translucency adds depth while keeping headings and copy crisp on any backdrop.',
                    ],
                    [
                        'type' => 'Rhythm',
                        'title' => 'A steady section cadence',
                        'description' => 'Hero, feature, proof, and CTA panels share spacing so pages read with calm momentum.',
                    ],
                    [
                        'type' => 'Tokens',
                        'title' => 'Brand-aware glass tints',
                        'description' => 'Accent and surface tokens flow through every panel, so the glass adapts to the brand instantly.',
                    ],
                ],
            ],
            'proof' => [
                'heading' => 'Teams that ship on the glass',
                'summary' => 'Outcome metrics and operator quotes show the theme holding up across real launch and service pages.',
                'items' => [
                    [
                        'metric' => '+34% launches',
                        'quote' => 'The glass panels gave our launch page depth without losing legibility.',
                        'name' => 'Marlow Studio',
                        'role' => 'Head of Web',
                    ],
                    [
                        'metric' => '2x faster builds',
                        'quote' => 'A shared section rhythm meant new pages dropped in without redesign.',
                        'name' => 'Tideline Labs',
                        'role' => 'Design Lead',
                    ],
                    [
                        'metric' => '4.9/5 polish',
                        'quote' => 'Brand tints flowed through every panel, so the theme felt bespoke.',
                        'name' => 'Northglass',
                        'role' => 'Product Marketing',
                    ],
                ],
            ],
            'content-listing' => [
                'heading' => 'Browse content cards without leaving the glass',
                'summary' => 'Translucent result cards keep listings legible and structured while the theme stays free of content records.',
                'variant' => 'editorial',
                'items' => [
                    [
                        'type' => 'Guide',
                        'title' => 'Designing with translucent panels',
                        'summary' => 'How to layer glass surfaces while keeping headings and copy fully legible.',
                        'url' => '#features',
                    ],
                    [
                        'type' => 'Story',
                        'title' => 'Marlow Studio launch page',
                        'summary' => 'A service launch rebuilt on the glass section rhythm in a single sprint.',
                        'url' => '#proof',
                    ],
                    [
                        'type' => 'Reference',
                        'title' => 'Brand-aware glass tokens',
                        'summary' => 'Mapping accent and surface colours through every translucent panel.',
                        'url' => '#content-listing',
                    ],
                ],
            ],
            'cta' => [
                'heading' => 'Bring your pages onto the glass',
                'summary' => 'A warm, focused call to action that stays native to the translucent theme.',
                'actions' => [
                    ['label' => 'View features', 'url' => '#features', 'style' => 'primary'],
                    ['label' => 'Get in touch', 'url' => '#contact', 'style' => 'secondary'],
                ],
            ],
            default => [],
        };
    }

    private function navigation(): LiquidGlassScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Liquid Glass',
            'items' => [
                ['label' => 'Features', 'url' => '#features'],
                ['label' => 'Proof', 'url' => '#proof'],
                ['label' => 'Listing', 'url' => '#content-listing'],
                ['label' => 'Contact', 'url' => '#contact'],
            ],
            'consultationUrl' => '#contact',
        ]);
    }

    private function hero(): LiquidGlassScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'A modern glass surface for launch and service pages',
            'eyebrow' => 'Liquid Glass',
            'summary' => 'A free modern theme with translucent panels, crisp content rhythm, and warm accent actions for launch, listing, and lead journeys.',
            'actions' => [
                ['label' => 'View features', 'url' => '#features'],
                ['label' => 'Get in touch', 'url' => '#contact'],
            ],
        ]);
    }

    private function footer(): LiquidGlassScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Liquid Glass',
            'summary' => 'A free modern theme of translucent panels for launch, listing, and lead pages.',
            'columns' => [
                [
                    'heading' => 'Product',
                    'links' => [
                        ['label' => 'Features', 'url' => '#features'],
                        ['label' => 'Proof', 'url' => '#proof'],
                    ],
                ],
                [
                    'heading' => 'Browse',
                    'links' => [
                        ['label' => 'Listing', 'url' => '#content-listing'],
                        ['label' => 'Detail', 'url' => '#content-listing'],
                    ],
                ],
                [
                    'heading' => 'Company',
                    'links' => [
                        ['label' => 'Contact', 'url' => '#contact'],
                        ['label' => 'Get in touch', 'url' => '#contact'],
                    ],
                ],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'liquid-glass-sections' => 'Theme Liquid Glass sections',
            'liquid-glass-directory' => 'Theme Liquid Glass listing',
            'liquid-glass-detail' => 'Theme Liquid Glass search results',
            'liquid-glass-contact' => 'Theme Liquid Glass contact',
            default => 'Theme Liquid Glass homepage',
        };
    }
}
