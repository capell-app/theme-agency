<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\PortfolioDirectory\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Portfolio Directory theme.
 *
 * Every surface is seeded as an ordered `render_data['sections']` list so the
 * live /theme-portfolio-directory render emits the theme's signature surfaces
 * (directory-hero / role-filters / portfolio-grid / resume-resources /
 * curated-lists / profile-detail) alongside the shared
 * hero/proof/content-listing/cta — giving each surface a full curated directory
 * rather than the generic skeleton. Copy is mined from the screenshot renderer.
 */
final class PortfolioDirectoryDemoContent implements ProvidesThemeDemoContent
{
    private const string BRAND = 'Folio Index';

    /**
     * @return array<int, ThemeDemoPageDefinition>
     */
    public function definitions(string $themeKey, string $themeName, string $baseUrl): array
    {
        $media = ThemeDemoMedia::groupedForTheme($themeKey);

        return [
            $this->homepage($themeKey, $media),
            $this->directory($themeKey, $media),
            $this->detail($themeKey, $media),
            $this->contact($themeKey, $media),
            $this->empty($themeKey, $media),
            $this->notFound($themeKey, $media),
            $this->cta($themeKey, $media),
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     */
    private function homepage(string $themeKey, array $media): ThemeDemoPageDefinition
    {
        return new ThemeDemoPageDefinition(
            surface: 'homepage',
            name: self::BRAND . ' Home',
            title: self::BRAND . ' — A directory the best work can stand behind',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'A directory the best work can stand behind',
                'A curated frontend theme for designers, developers, and studios with large preview tiles, role and medium filters, resume resources, and curated lists.',
            ),
            renderData: [
                'summary' => 'A curated directory for designers, developers, and studios with large preview tiles, role and medium filters, resume resources, and curated lists.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Portfolio Directory',
                        heading: 'A directory the best work can stand behind',
                        summary: 'A curated theme for designers, developers, and studios with large preview tiles, role and medium filters, resume resources, and curated lists.',
                        media: $media['hero'][0] ?? null,
                    ),
                    $this->directoryHeroSection($media),
                    $this->roleFiltersSection(),
                    $this->portfolioGridSection($media),
                    $this->resumeResourcesSection(),
                    $this->curatedListsSection(),
                    $this->profileDetailSection($media),
                    $this->proofSection(),
                    $this->newsletterSection(
                        heading: 'New portfolios, curated for you',
                        summary: 'A non-submitting newsletter prompt proves the follow path feels like part of the directory.',
                    ),
                    $this->ctaSection(
                        heading: 'List your portfolio in the index',
                        summary: 'A focused invitation to join the directory without the theme owning creator records.',
                    ),
                ],
            ],
            type: PageTypeEnum::Home,
            layout: LayoutEnum::Home,
        );
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     */
    private function directory(string $themeKey, array $media): ThemeDemoPageDefinition
    {
        return new ThemeDemoPageDefinition(
            surface: 'directory',
            name: self::BRAND . ' Creators',
            title: 'A directory of creators built to be scanned — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'A directory of creators built to be scanned',
                'Role and medium filters keep the portfolio archive legible without the theme owning creator records.',
            ),
            renderData: [
                'summary' => 'Role and medium filters keep the portfolio archive legible without the theme owning creator records.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Creators',
                        heading: 'A directory of creators built to be scanned',
                        summary: 'Role and medium filters keep the portfolio archive legible without the theme owning creator records.',
                        media: $media['listing'][0] ?? $media['hero'][0] ?? null,
                    ),
                    $this->roleFiltersSection(),
                    $this->portfolioGridSection($media),
                    $this->curatedListsSection(),
                    $this->ctaSection(
                        heading: 'Add your work to the directory',
                        summary: 'A clear path for creators to join the curated index.',
                    ),
                ],
            ],
            layout: LayoutEnum::Results,
        );
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     */
    private function detail(string $themeKey, array $media): ThemeDemoPageDefinition
    {
        return new ThemeDemoPageDefinition(
            surface: 'detail',
            name: self::BRAND . ' Profile',
            title: 'A portfolio landing built to be admired — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'A portfolio landing built to be admired',
                'A single portfolio view pairs gallery stacks and resume resources so visitors can explore the work with confidence.',
            ),
            renderData: [
                'summary' => 'A single portfolio view pairs gallery stacks and resume resources so visitors can explore the work with confidence.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Profile',
                        heading: 'A portfolio landing built to be admired',
                        summary: 'A single portfolio view pairs gallery stacks and resume resources so visitors can explore the work with confidence.',
                        media: $media['detail'][0] ?? null,
                    ),
                    $this->profileDetailSection($media),
                    $this->resumeResourcesSection(),
                    $this->curatedListsSection(),
                    $this->proofSection(),
                    $this->ctaSection(
                        heading: 'Work with this creator',
                        summary: 'A direct, on-brand path to start a conversation from a profile.',
                    ),
                ],
            ],
        );
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     */
    private function contact(string $themeKey, array $media): ThemeDemoPageDefinition
    {
        return new ThemeDemoPageDefinition(
            surface: 'contact',
            name: self::BRAND . ' Contact',
            title: 'Stay close to the directory — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Stay close to the directory through one path',
                'A non-submitting newsletter and conversion CTA proves the contact journey feels like part of the directory.',
            ),
            renderData: [
                'summary' => 'A non-submitting newsletter and conversion CTA proves the contact journey feels like part of the directory.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Contact',
                        heading: 'Stay close to the directory through one path',
                        summary: 'A non-submitting newsletter and conversion CTA proves the contact journey feels like part of the directory.',
                        media: $media['contact'][0] ?? null,
                    ),
                    $this->newsletterSection(
                        heading: 'Get new portfolios in your inbox',
                        summary: 'A single subscribe field keeps visitors close to the curated index.',
                    ),
                    $this->proofSection(),
                    $this->ctaSection(
                        heading: 'List your portfolio',
                        summary: 'A focused invitation for creators to join the directory.',
                    ),
                ],
            ],
            layout: LayoutEnum::System,
        );
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     */
    private function empty(string $themeKey, array $media): ThemeDemoPageDefinition
    {
        return new ThemeDemoPageDefinition(
            surface: 'empty',
            name: self::BRAND . ' No Results',
            title: 'No portfolios match that filter yet — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'No portfolios match that filter yet',
                'A graceful empty state for a filtered directory with no matching creators.',
            ),
            renderData: [
                'summary' => 'No portfolios match that filter yet — the directory stays calm and points somewhere useful.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Creators',
                        heading: 'No portfolios match that filter yet',
                        summary: 'Nothing matches the current role or medium filter. Clear it to see every creator, or browse the curated lists.',
                        media: null,
                    ),
                    [
                        'type' => 'content-listing',
                        'heading' => 'When creators join, they appear here',
                        'summary' => 'New portfolios show up in this directory as large preview tiles, newest first.',
                        'items' => [],
                    ],
                    $this->roleFiltersSection(),
                    $this->ctaSection(
                        heading: 'Be the first to list here',
                        summary: 'Add your portfolio and help seed the directory for this filter.',
                    ),
                ],
            ],
        );
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     */
    private function notFound(string $themeKey, array $media): ThemeDemoPageDefinition
    {
        return new ThemeDemoPageDefinition(
            surface: 'not-found',
            name: self::BRAND . ' 404',
            title: 'That portfolio moved — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-404',
            content: $this->prose(
                'That portfolio moved',
                'A not-found page that routes visitors back into the directory and curated lists.',
            ),
            renderData: [
                'summary' => 'That portfolio moved or never existed — here is the way back into the directory.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: '404',
                        heading: 'That portfolio moved',
                        summary: 'The link is broken or the profile has moved. Head back to the directory, or browse the curated lists.',
                        media: null,
                    ),
                    $this->ctaSection(
                        heading: 'Back to the directory',
                        summary: 'A clear path home keeps a missing profile from ending the visit.',
                    ),
                ],
            ],
            type: PageTypeEnum::NotFound,
            layout: LayoutEnum::System,
        );
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     */
    private function cta(string $themeKey, array $media): ThemeDemoPageDefinition
    {
        return new ThemeDemoPageDefinition(
            surface: 'cta',
            name: self::BRAND . ' List Your Work',
            title: 'List your portfolio in the index — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'List your portfolio in the index',
                'A focused conversion page inviting creators to add their work to the curated directory.',
            ),
            renderData: [
                'summary' => 'A focused conversion page inviting creators to add their work to the curated directory.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'List your work',
                        heading: 'List your portfolio in the index',
                        summary: 'Add your work to a curated directory that designers, developers, and studios trust.',
                        media: $media['cta'][0] ?? null,
                    ),
                    $this->newsletterSection(
                        heading: 'Get notified when listings open',
                        summary: 'A single subscribe field keeps creators in the loop on new directory rounds.',
                    ),
                    $this->proofSection(),
                    $this->ctaSection(
                        heading: 'Add your portfolio now',
                        summary: 'A confident close that asks creators to join the directory.',
                    ),
                ],
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function heroSection(string $eyebrow, string $heading, string $summary, ?string $media): array
    {
        $section = [
            'type' => 'hero',
            'eyebrow' => $eyebrow,
            'heading' => $heading,
            'summary' => $summary,
            'actions' => [
                ['label' => 'Browse portfolios', 'url' => '#portfolio-grid', 'style' => 'primary'],
                ['label' => 'Filter by role', 'url' => '#role-filters', 'style' => 'secondary'],
            ],
        ];

        if ($media !== null) {
            $section['mediaUrl'] = $media;
            $section['mediaAlt'] = 'Large portfolio preview tiles in a curated directory';
        }

        return $section;
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function directoryHeroSection(array $media): array
    {
        return [
            'type' => 'directory-hero',
            'heading' => 'Featured portfolios, front and centre',
            'summary' => 'Large preview tiles put the strongest work first, so visitors feel the quality before they filter.',
            'items' => [
                ['title' => 'Studio Marlow', 'summary' => 'Brand and product design for ambitious early-stage teams.', 'image' => $media['detail'][0] ?? null],
                ['title' => 'Tideline Interactive', 'summary' => 'Motion-led web experiences for culture and music clients.', 'image' => $media['proof'][0] ?? null],
                ['title' => 'Northglass Labs', 'summary' => 'Frontend engineering and design systems for scale-ups.', 'image' => $media['cta'][0] ?? null],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function roleFiltersSection(): array
    {
        return [
            'type' => 'role-filters',
            'heading' => 'Filter by role, medium, and availability',
            'summary' => 'Discovery filters surface designers, developers, and studios so visitors find the right work fast.',
            'items' => [
                ['title' => 'Designers', 'summary' => 'Brand, product, and editorial designers, filterable by medium.'],
                ['title' => 'Developers', 'summary' => 'Frontend and full-stack engineers with shipped, public work.'],
                ['title' => 'Studios', 'summary' => 'Small teams taking on end-to-end product and brand work.'],
            ],
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function portfolioGridSection(array $media): array
    {
        $images = array_values(array_unique(array_merge($media['listing'], $media['proof'], $media['detail'])));

        $entries = [
            ['title' => 'Aria Wells', 'meta' => 'Product Designer', 'summary' => 'Interface and systems work for fintech and health products.'],
            ['title' => 'Devon Park Studio', 'meta' => 'Design Studio', 'summary' => 'Brand identities and websites for culture-led organisations.'],
            ['title' => 'Mira Sol', 'meta' => 'Frontend Engineer', 'summary' => 'Accessible, fast interfaces built with care and craft.'],
        ];

        $items = [];

        foreach ($entries as $index => $entry) {
            $items[] = [
                ...$entry,
                'image' => $images[$index % max(count($images), 1)] ?? null,
            ];
        }

        return [
            'type' => 'portfolio-grid',
            'heading' => 'Browse portfolios as large preview tiles',
            'summary' => 'A generous grid keeps the work legible and confident, so each portfolio gets room to breathe.',
            'items' => $items,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function resumeResourcesSection(): array
    {
        return [
            'type' => 'resume-resources',
            'heading' => 'Resume resources, ready to download',
            'summary' => 'Each creator can attach a resume and case-study pack, so hiring teams get everything in one place.',
            'label' => 'View resources',
            'url' => '#resume-resources',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function curatedListsSection(): array
    {
        return [
            'type' => 'curated-lists',
            'heading' => 'Curated lists, refreshed every month',
            'summary' => 'Editor-picked collections group the directory by theme, so visitors always have a strong starting point.',
            'items' => [
                ['title' => 'Best of brand design', 'summary' => 'Ten studios setting the pace for brand and identity work.'],
                ['title' => 'Frontend craftspeople', 'summary' => 'Engineers whose public work is a masterclass in detail.'],
                ['title' => 'New studios to watch', 'summary' => 'Young teams doing standout end-to-end product work.'],
            ],
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function profileDetailSection(array $media): array
    {
        return [
            'type' => 'profile-detail',
            'heading' => 'A profile that pairs gallery stacks with context',
            'summary' => 'A single portfolio view pairs gallery stacks and resume resources so visitors can explore the work with confidence.',
            'items' => [
                ['title' => 'Selected work', 'summary' => 'A curated gallery of the projects that matter most.', 'image' => $media['detail'][0] ?? null],
                ['title' => 'About', 'summary' => 'A short, human introduction to the creator and their approach.'],
                ['title' => 'Resources', 'summary' => 'Resume, case studies, and ways to get in touch, in one place.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function proofSection(): array
    {
        return [
            'type' => 'proof',
            'heading' => 'Creators and hirers who trust the index',
            'summary' => 'A curated directory only works when both sides trust it — here is what that looks like in practice.',
            'items' => [
                ['title' => '1,200 portfolios', 'quote' => 'Listing here put my work in front of the right teams.'],
                ['title' => '4.8/5 hirer rating', 'quote' => 'The filters meant we found a designer in an afternoon.'],
                ['title' => '30 curated lists', 'quote' => 'Being featured in a list sent real, qualified leads.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function newsletterSection(string $heading, string $summary): array
    {
        return [
            'type' => 'newsletter',
            'heading' => $heading,
            'summary' => $summary,
            'action' => '#newsletter',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function ctaSection(string $heading, string $summary): array
    {
        return [
            'type' => 'cta',
            'heading' => $heading,
            'summary' => $summary,
            'actions' => [
                ['label' => 'List your portfolio', 'url' => '#newsletter', 'style' => 'primary'],
                ['label' => 'Browse portfolios', 'url' => '#portfolio-grid', 'style' => 'secondary'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function navigation(): array
    {
        return [
            'brandName' => self::BRAND,
            'brand' => self::BRAND,
            'items' => [
                ['label' => 'Portfolios', 'url' => '#portfolio-grid'],
                ['label' => 'Creators', 'url' => '#role-filters'],
                ['label' => 'Resources', 'url' => '#resume-resources'],
                ['label' => 'Curated lists', 'url' => '#curated-lists'],
            ],
            'ctaLabel' => 'List your work',
            'ctaUrl' => '#newsletter',
            'consultationUrl' => '#newsletter',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        return [
            'brandName' => self::BRAND,
            'brand' => self::BRAND,
            'summary' => 'A curated portfolio directory for designers, developers, and studios.',
            'columns' => [
                [
                    'heading' => 'Browse',
                    'links' => [
                        ['label' => 'Portfolios', 'url' => '#portfolio-grid'],
                        ['label' => 'Creators', 'url' => '#role-filters'],
                    ],
                ],
                [
                    'heading' => 'Discover',
                    'links' => [
                        ['label' => 'Curated lists', 'url' => '#curated-lists'],
                        ['label' => 'Resources', 'url' => '#resume-resources'],
                    ],
                ],
                [
                    'heading' => 'Join',
                    'links' => [
                        ['label' => 'List your work', 'url' => '#newsletter'],
                        ['label' => 'Newsletter', 'url' => '#newsletter'],
                    ],
                ],
            ],
        ];
    }

    private function prose(string $heading, string $summary): string
    {
        return sprintf('<h2>%s</h2><p>%s</p>', e($heading), e($summary));
    }
}
