<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\PersonalDev\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Personal Dev theme.
 *
 * Every surface is seeded as an ordered `render_data['sections']` list so the
 * live /theme-personal-dev render emits the theme's signature surfaces
 * (about-intro / now / writing-index / projects / newsletter-inline) alongside
 * the shared hero/features/proof/content-listing/cta — giving each surface a
 * full, typographic personal site rather than the generic skeleton. Copy is
 * mined from the screenshot renderer so the demo matches the marketing previews.
 */
final class PersonalDevDemoContent implements ProvidesThemeDemoContent
{
    private const string BRAND = 'Alex Rivers';

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
            title: self::BRAND . ' — Writing, projects, and a public now page',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'A personal site for writing, projects, and a public now page',
                'A minimal, typography-led homepage for developers and writers who want portable content and quiet, premium output.',
            ),
            renderData: [
                'summary' => 'A minimal, typography-led personal site for developers and writers who want portable content and quiet, premium output.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Personal Dev',
                        heading: 'A personal site for writing, projects, and a public now page',
                        summary: 'A minimal, typography-led homepage for developers and writers who want portable content and quiet, premium output.',
                        media: $media['hero'][0] ?? null,
                    ),
                    $this->aboutIntroSection(),
                    $this->nowSection(),
                    $this->writingIndexSection(
                        heading: 'Recent writing, set like a well-typeset page',
                        summary: 'Essays and notes stay premium and scannable while the theme leaves content ownership to Capell.',
                    ),
                    $this->projectsSection(),
                    $this->featuresSection(),
                    $this->proofSection(),
                    $this->newsletterSection(
                        heading: 'A quiet newsletter for new writing',
                        summary: 'A non-submitting subscribe prompt proves the conversation path feels native to the site.',
                    ),
                    $this->ctaSection(
                        heading: 'Read the writing, then subscribe',
                        summary: 'One confident invitation to follow the work without a loud, salesy call to action.',
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
            name: self::BRAND . ' Writing',
            title: 'Writing index — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'Everything I have written, in one scannable index',
                'An editorial writing directory keeps essays and notes legible without the theme owning content records.',
            ),
            renderData: [
                'summary' => 'An editorial writing directory keeps essays and notes legible without the theme owning content records.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Writing',
                        heading: 'Everything I have written, in one scannable index',
                        summary: 'An editorial writing directory keeps essays and notes legible without the theme owning content records.',
                        media: $media['listing'][0] ?? $media['hero'][0] ?? null,
                    ),
                    $this->writingIndexSection(
                        heading: 'Essays and notes, newest first',
                        summary: 'A premium reading index that stays calm and typographic across the whole archive.',
                    ),
                    $this->contentListingSection(
                        heading: 'Browse writing without leaving the page',
                        summary: 'Result cards keep the archive legible while the theme stays free of content records.',
                    ),
                    $this->ctaSection(
                        heading: 'Follow the writing',
                        summary: 'Subscribe once and new essays arrive without the theme owning a mailing list.',
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
            name: self::BRAND . ' Essay',
            title: 'A focused detail surface — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'A focused detail surface for a single piece',
                'Detail pages stay quiet and typographic so the reader keeps their attention on the writing itself.',
            ),
            renderData: [
                'summary' => 'Detail pages stay quiet and typographic so the reader keeps their attention on the writing itself.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Essay',
                        heading: 'A focused detail surface for a single piece',
                        summary: 'Detail pages stay quiet and typographic so the reader keeps their attention on the writing itself.',
                        media: $media['detail'][0] ?? null,
                    ),
                    $this->aboutIntroSection(
                        heading: 'A short note on who is writing',
                        summary: 'A brief author intro grounds the piece without pulling focus from the reading.',
                    ),
                    $this->proofSection(),
                    $this->newsletterSection(
                        heading: 'Liked this piece? Get the next one',
                        summary: 'A quiet subscribe prompt that stays native to the reading experience.',
                    ),
                    $this->ctaSection(
                        heading: 'Keep reading',
                        summary: 'A clear path back into the writing index from the end of a piece.',
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
            title: 'Stay in touch — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Stay in touch without a heavy contact form',
                'A non-submitting newsletter and contact prompt proves the conversation path feels native to the site.',
            ),
            renderData: [
                'summary' => 'A non-submitting newsletter and contact prompt proves the conversation path feels native to the site.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Contact',
                        heading: 'Stay in touch without a heavy contact form',
                        summary: 'A non-submitting newsletter and contact prompt proves the conversation path feels native to the site.',
                        media: $media['contact'][0] ?? null,
                    ),
                    $this->newsletterSection(
                        heading: 'Subscribe, or just say hello',
                        summary: 'A calm subscribe prompt alongside a direct way to reach out by email.',
                    ),
                    $this->proofSection(),
                    $this->ctaSection(
                        heading: 'Start a conversation',
                        summary: 'A focused invitation to get in touch that stays quiet and on-brand.',
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
            title: 'Nothing here yet — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'Nothing here yet, and that is fine',
                'The empty state stays calm and on-brand so a fresh site never looks broken.',
            ),
            renderData: [
                'summary' => 'The empty state stays calm and on-brand so a fresh site never looks broken.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Writing',
                        heading: 'Nothing here yet, and that is fine',
                        summary: 'No essays match this filter yet. Clear it to see the full archive, or jump to the latest writing.',
                        media: null,
                    ),
                    [
                        'type' => 'content-listing',
                        'heading' => 'When writing lands, it shows up here',
                        'summary' => 'New essays and notes appear in this index, newest first, the moment they publish.',
                        'items' => [],
                    ],
                    $this->aboutIntroSection(
                        heading: 'While you are here',
                        summary: 'A short author intro keeps a fresh, empty site feeling intentional rather than broken.',
                    ),
                    $this->ctaSection(
                        heading: 'Follow along from the start',
                        summary: 'Subscribe now and the first essays arrive as soon as they are written.',
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
            title: 'That page wandered off — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-404',
            content: $this->prose(
                'That page wandered off',
                'A typographic 404 keeps the reader oriented and offers a clear way back into the writing.',
            ),
            renderData: [
                'summary' => 'A typographic 404 keeps the reader oriented and offers a clear way back into the writing.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: '404',
                        heading: 'That page wandered off',
                        summary: 'The link is broken or the page has moved. Head back to the writing index, or start a conversation.',
                        media: null,
                    ),
                    $this->ctaSection(
                        heading: 'Back to the writing',
                        summary: 'A clear path home keeps a missing page from ending the visit.',
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
            name: self::BRAND . ' Subscribe',
            title: 'One confident invitation to subscribe — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'One confident invitation to subscribe',
                'A focused conversion CTA proves the theme can sell a newsletter or product without feeling loud.',
            ),
            renderData: [
                'summary' => 'A focused conversion CTA proves the theme can sell a newsletter or product without feeling loud.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Subscribe',
                        heading: 'One confident invitation to subscribe',
                        summary: 'A focused conversion CTA proves the theme can sell a newsletter or product without feeling loud.',
                        media: $media['cta'][0] ?? null,
                    ),
                    $this->newsletterSection(
                        heading: 'Get new writing in your inbox',
                        summary: 'A single, calm subscribe field is all it takes to follow the work.',
                    ),
                    $this->proofSection(),
                    $this->ctaSection(
                        heading: 'Subscribe now',
                        summary: 'A confident close that asks for the subscribe without raising its voice.',
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
                ['label' => 'Read the writing', 'url' => '#writing', 'style' => 'primary'],
                ['label' => 'See projects', 'url' => '#projects', 'style' => 'secondary'],
            ],
        ];

        if ($media !== null) {
            $section['mediaUrl'] = $media;
            $section['mediaAlt'] = 'A quiet, typography-led personal site surface';
        }

        return $section;
    }

    /**
     * @return array<string, mixed>
     */
    private function aboutIntroSection(string $heading = 'A developer who writes in public', string $summary = 'A short, human intro that frames the writing and projects without a heavy about page.'): array
    {
        return [
            'type' => 'about-intro',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Writing', 'summary' => 'Essays and notes on building software, set like a well-typeset page.'],
                ['title' => 'Projects', 'summary' => 'A working portfolio that pairs context with the outcomes that mattered.'],
                ['title' => 'Now', 'summary' => 'A public now page that keeps current focus visible and honest.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function nowSection(): array
    {
        return [
            'type' => 'now',
            'heading' => 'What I am focused on right now',
            'summary' => 'The now surface keeps present priorities visible and human without owning any structured records.',
            'items' => [
                ['title' => 'Shipping', 'summary' => 'Polishing a small writing tool and publishing one essay a week.'],
                ['title' => 'Reading', 'summary' => 'Working through a stack of books on typography and systems design.'],
                ['title' => 'Learning', 'summary' => 'Going deeper on accessible, content-first web patterns.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function writingIndexSection(string $heading, string $summary): array
    {
        return [
            'type' => 'writing-index',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Designing for quiet reading', 'summary' => 'Why a calm, typographic page beats a loud one for long-form writing.'],
                ['title' => 'Portable content, lasting sites', 'summary' => 'Keeping your words yours, even as themes and tools come and go.'],
                ['title' => 'A working now page', 'summary' => 'How a public now page keeps a personal site honest and current.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function projectsSection(): array
    {
        return [
            'type' => 'projects',
            'heading' => 'Projects framed as a working portfolio',
            'summary' => 'A project index pairs context and outcomes so visitors understand the work without leaving the page.',
            'items' => [
                ['title' => 'Marlow — a writing tool', 'summary' => 'A focused editor for long-form essays, built for calm, distraction-free drafting.'],
                ['title' => 'Tideline — a reading app', 'summary' => 'A typographic reader that keeps long articles legible on any screen.'],
                ['title' => 'Northpage — a now-page kit', 'summary' => 'A tiny kit for publishing and updating a public now page.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function featuresSection(): array
    {
        return [
            'type' => 'features',
            'heading' => 'A site that stays calm and legible',
            'summary' => 'Typography-led surfaces keep structure, rhythm, and contrast intact across every public page.',
            'features' => [
                ['title' => 'Typographic by default', 'summary' => 'A measured type scale keeps headings and copy legible from first view to footer.'],
                ['title' => 'Portable content', 'summary' => 'Writing and projects stay in Capell, so the theme never owns your words.'],
                ['title' => 'Quiet, premium output', 'summary' => 'Restrained colour and spacing make every page feel considered, not templated.'],
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
            'heading' => 'Readers who stayed for the writing',
            'summary' => 'Quiet, typographic pages keep readers reading — here is what that looks like in practice.',
            'items' => [
                ['title' => '12k subscribers', 'quote' => 'The calm layout is why people actually finish my essays.'],
                ['title' => '4.9/5 readability', 'quote' => 'It reads like a well-set book, not a busy blog.'],
                ['title' => '2x return visits', 'quote' => 'The now page keeps people coming back to see what changed.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function contentListingSection(string $heading, string $summary): array
    {
        return [
            'type' => 'content-listing',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Designing for quiet reading', 'summary' => 'Why a calm, typographic page beats a loud one for long-form writing.'],
                ['title' => 'Portable content, lasting sites', 'summary' => 'Keeping your words yours, even as themes and tools come and go.'],
                ['title' => 'A working now page', 'summary' => 'How a public now page keeps a personal site honest and current.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function newsletterSection(string $heading, string $summary): array
    {
        return [
            'type' => 'newsletter-inline',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'One email a week', 'summary' => 'New writing and the occasional project note, never more than once a week.'],
                ['title' => 'No noise', 'summary' => 'No tracking-heavy funnels — just the writing, sent when it is ready.'],
            ],
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
                ['label' => 'Subscribe', 'url' => '#subscribe', 'style' => 'primary'],
                ['label' => 'Read the writing', 'url' => '#writing', 'style' => 'secondary'],
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
                ['label' => 'Writing', 'url' => '#writing'],
                ['label' => 'Projects', 'url' => '#projects'],
                ['label' => 'Now', 'url' => '#now'],
                ['label' => 'Contact', 'url' => '#contact'],
            ],
            'ctaLabel' => 'Subscribe',
            'ctaUrl' => '#subscribe',
            'subscribeUrl' => '#subscribe',
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
            'summary' => 'A minimal, typography-led personal site for writing, projects, and a public now page.',
            'columns' => [
                [
                    'heading' => 'Site',
                    'links' => [
                        ['label' => 'Writing', 'url' => '#writing'],
                        ['label' => 'Projects', 'url' => '#projects'],
                    ],
                ],
                [
                    'heading' => 'More',
                    'links' => [
                        ['label' => 'Now', 'url' => '#now'],
                        ['label' => 'Contact', 'url' => '#contact'],
                    ],
                ],
                [
                    'heading' => 'Follow',
                    'links' => [
                        ['label' => 'Subscribe', 'url' => '#subscribe'],
                        ['label' => 'Email', 'url' => 'mailto:hello@alexrivers.example'],
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
