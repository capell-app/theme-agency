<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\ProductCompanyEditorial\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Product Company Editorial theme.
 *
 * Every surface is seeded as an ordered `render_data['sections']` list so the
 * live /theme-product-company-editorial render emits the theme's signature
 * surfaces (featured-posts / topic-areas / story-cards / product-updates /
 * template-stories / author-callouts) alongside the shared
 * hero/proof/content-listing/newsletter/cta — giving each surface a full,
 * dark-masthead editorial publication. Copy is mined from the screenshot renderer.
 */
final class ProductCompanyEditorialDemoContent implements ProvidesThemeDemoContent
{
    private const string BRAND = 'Foundry Journal';

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
            title: self::BRAND . ' — Editorial a product company can stand behind',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'Editorial a product company can stand behind',
                'A dark-masthead editorial homepage for featured posts, product updates, design essays, engineering stories, templates, and newsletter conversion.',
            ),
            renderData: [
                'summary' => 'A dark-masthead editorial publication for featured posts, product updates, design essays, engineering stories, templates, and newsletter conversion.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Product Company Editorial',
                        heading: 'Editorial a product company can stand behind',
                        summary: 'A dark-masthead editorial homepage for featured posts, product updates, design essays, engineering stories, templates, and newsletter conversion.',
                        media: $media['hero'][0] ?? null,
                    ),
                    $this->featuredPostsSection($media),
                    $this->topicAreasSection(),
                    $this->storyCardsSection($media),
                    $this->productUpdatesSection(),
                    $this->templateStoriesSection(),
                    $this->authorCalloutsSection($media),
                    $this->proofSection(),
                    $this->newsletterSection(
                        heading: 'Subscribe to the publication',
                        summary: 'A non-submitting newsletter conversion proves the signup journey feels like part of the editorial experience.',
                    ),
                    $this->ctaSection(
                        heading: 'Read featured stories, then subscribe',
                        summary: 'A confident invitation to follow the publication without a loud sales pitch.',
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
            name: self::BRAND . ' Archive',
            title: 'An archive built to be scanned and trusted — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'An archive built to be scanned and trusted',
                'Structured story cards keep product updates, essays, and templates legible without the theme owning content records.',
            ),
            renderData: [
                'summary' => 'Structured story cards keep product updates, essays, and templates legible without the theme owning content records.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Archive',
                        heading: 'An archive built to be scanned and trusted',
                        summary: 'Structured story cards keep product updates, essays, and templates legible without the theme owning content records.',
                        media: $media['listing'][0] ?? $media['hero'][0] ?? null,
                    ),
                    $this->contentListingSection(
                        heading: 'Every story, newest first',
                        summary: 'A structured archive of product updates, design essays, and engineering stories.',
                    ),
                    $this->topicAreasSection(),
                    $this->ctaSection(
                        heading: 'Follow the publication',
                        summary: 'Subscribe once and new stories arrive without the theme owning a mailing list.',
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
            name: self::BRAND . ' Story',
            title: 'A featured story that leads the publication — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'A featured story that leads the whole publication',
                'A single editorial landing pairs a product update, design essay, or engineering post with supporting context so readers commit to the read.',
            ),
            renderData: [
                'summary' => 'A single editorial landing pairs a product update, design essay, or engineering post with supporting context so readers commit to the read.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Featured story',
                        heading: 'A featured story that leads the whole publication',
                        summary: 'A single editorial landing pairs a product update, design essay, or engineering post with supporting context so readers commit to the read.',
                        media: $media['detail'][0] ?? null,
                    ),
                    $this->featuredPostsSection($media),
                    $this->storyCardsSection($media),
                    $this->authorCalloutsSection($media),
                    $this->proofSection(),
                    $this->ctaSection(
                        heading: 'Keep reading',
                        summary: 'A clear path back into the publication from the end of a story.',
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
            name: self::BRAND . ' Subscribe',
            title: 'Subscribe to the publication — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Subscribe to the publication in one confident path',
                'A non-submitting newsletter conversion proves the signup journey feels like part of the editorial experience.',
            ),
            renderData: [
                'summary' => 'A non-submitting newsletter conversion proves the signup journey feels like part of the editorial experience.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Subscribe',
                        heading: 'Subscribe to the publication in one confident path',
                        summary: 'A non-submitting newsletter conversion proves the signup journey feels like part of the editorial experience.',
                        media: $media['contact'][0] ?? null,
                    ),
                    $this->newsletterSection(
                        heading: 'Get every story in your inbox',
                        summary: 'A single subscribe field keeps readers close to the publication.',
                    ),
                    $this->proofSection(),
                    $this->ctaSection(
                        heading: 'Start reading',
                        summary: 'A focused invitation into the featured stories.',
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
            title: 'No stories match that filter yet — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'No stories match that filter yet',
                'A graceful empty state for a filtered archive with no matching stories.',
            ),
            renderData: [
                'summary' => 'No stories match that filter yet — the archive stays calm and points somewhere useful.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Archive',
                        heading: 'No stories match that filter yet',
                        summary: 'Nothing matches the current topic or format. Clear the filter to see every story, or browse the topic areas.',
                        media: null,
                    ),
                    [
                        'type' => 'content-listing',
                        'heading' => 'When stories publish, they appear here',
                        'summary' => 'New product updates, essays, and engineering posts show up in this archive, newest first.',
                        'items' => [],
                    ],
                    $this->topicAreasSection(),
                    $this->ctaSection(
                        heading: 'Subscribe for what is next',
                        summary: 'Follow the publication and the next stories arrive as soon as they publish.',
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
            title: 'That story moved — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-404',
            content: $this->prose(
                'That story moved',
                'A not-found page that routes readers back into the featured stories and topic areas.',
            ),
            renderData: [
                'summary' => 'That story moved or never existed — here is the way back into the publication.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: '404',
                        heading: 'That story moved',
                        summary: 'The link is broken or the story has moved. Head back to the featured stories, or browse the topic areas.',
                        media: null,
                    ),
                    $this->ctaSection(
                        heading: 'Back to the publication',
                        summary: 'A clear path home keeps a missing story from ending the visit.',
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
            name: self::BRAND . ' Get Started',
            title: 'Subscribe to the publication — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'One confident invitation to subscribe',
                'A focused conversion page inviting readers to follow the editorial publication.',
            ),
            renderData: [
                'summary' => 'A focused conversion page inviting readers to follow the editorial publication.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Subscribe',
                        heading: 'Follow the publication from here',
                        summary: 'Get featured stories, product updates, and design essays delivered as they publish.',
                        media: $media['cta'][0] ?? null,
                    ),
                    $this->newsletterSection(
                        heading: 'Get new stories in your inbox',
                        summary: 'A single, calm subscribe field is all it takes to follow the publication.',
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
                ['label' => 'Read featured stories', 'url' => '#featured-posts', 'style' => 'primary'],
                ['label' => 'Subscribe', 'url' => '#newsletter', 'style' => 'secondary'],
            ],
        ];

        if ($media !== null) {
            $section['mediaUrl'] = $media;
            $section['mediaAlt'] = 'A dark-masthead editorial publication surface';
        }

        return $section;
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function featuredPostsSection(array $media): array
    {
        return [
            'type' => 'featured-posts',
            'heading' => 'A featured story that leads the publication',
            'summary' => 'A leading post pairs a product update, design essay, or engineering story with supporting context so readers commit to the read.',
            'items' => [
                ['title' => 'Shipping the new editor', 'summary' => 'How the team rebuilt the writing experience from the ground up.', 'meta' => 'Product update', 'image' => $media['detail'][0] ?? null],
                ['title' => 'Designing for trust', 'summary' => 'The principles behind a calmer, more legible product surface.', 'meta' => 'Design essay', 'image' => $media['proof'][0] ?? null],
                ['title' => 'Inside our render pipeline', 'summary' => 'An engineering deep-dive into how pages stay fast at scale.', 'meta' => 'Engineering', 'image' => $media['cta'][0] ?? null],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function topicAreasSection(): array
    {
        return [
            'type' => 'topic-areas',
            'heading' => 'Read by topic',
            'summary' => 'Product updates, design essays, engineering stories, and templates — grouped so readers find their lane fast.',
            'items' => [
                ['title' => 'Product', 'summary' => 'Release notes and the thinking behind each change.'],
                ['title' => 'Design', 'summary' => 'Essays on craft, systems, and the details that matter.'],
                ['title' => 'Engineering', 'summary' => 'How the product is built, scaled, and kept fast.'],
            ],
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function storyCardsSection(array $media): array
    {
        $images = array_values(array_unique(array_merge($media['listing'], $media['proof'], $media['detail'])));

        $entries = [
            ['title' => 'A faster path to publish', 'meta' => 'Product', 'summary' => 'The workflow changes that cut publish time in half.'],
            ['title' => 'Type at scale', 'meta' => 'Design', 'summary' => 'Building a type system that holds up across the whole product.'],
            ['title' => 'Caching, end to end', 'meta' => 'Engineering', 'summary' => 'How we kept editorial pages instant without sacrificing freshness.'],
        ];

        $items = [];

        foreach ($entries as $index => $entry) {
            $items[] = [
                ...$entry,
                'image' => $images[$index % max(count($images), 1)] ?? null,
            ];
        }

        return [
            'type' => 'story-cards',
            'heading' => 'Latest stories, structured to scan',
            'summary' => 'Structured story cards keep updates, essays, and engineering posts legible without the theme owning content records.',
            'items' => $items,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function productUpdatesSection(): array
    {
        return [
            'type' => 'product-updates',
            'heading' => 'Product updates, in plain language',
            'summary' => 'Every release explained with the reasoning behind it, so readers trust where the product is going.',
            'label' => 'View all updates',
            'url' => '#product-updates',
            'items' => [
                ['title' => 'New editor, now default', 'summary' => 'The rebuilt writing experience is now live for everyone.'],
                ['title' => 'Faster page rendering', 'summary' => 'Editorial pages now load noticeably faster across devices.'],
                ['title' => 'Template gallery', 'summary' => 'Start from a proven layout with the new template gallery.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function templateStoriesSection(): array
    {
        return [
            'type' => 'template-stories',
            'heading' => 'Templates, told as stories',
            'summary' => 'Each template comes with the story of where it works best, so readers know exactly when to reach for it.',
            'items' => [
                ['title' => 'Launch announcement', 'summary' => 'A template tuned for clear, confident product launches.'],
                ['title' => 'Design essay', 'summary' => 'A reading-first layout built for long-form craft writing.'],
                ['title' => 'Engineering deep-dive', 'summary' => 'A structured template for technical, diagram-heavy posts.'],
            ],
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function authorCalloutsSection(array $media): array
    {
        return [
            'type' => 'author-callouts',
            'heading' => 'Written by the people building the product',
            'summary' => 'Author callouts put a face and a perspective behind every story, so readers know who is speaking.',
            'items' => [
                ['title' => 'Aria Wells', 'summary' => 'Head of Design, writing on craft and systems.', 'image' => $media['proof'][0] ?? null],
                ['title' => 'Devon Park', 'summary' => 'Staff Engineer, writing on performance and architecture.'],
                ['title' => 'Mira Sol', 'summary' => 'Product Lead, writing on releases and roadmap.'],
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
            'heading' => 'Readers who trust the publication',
            'summary' => 'A product publication earns trust one honest story at a time — here is what that looks like.',
            'items' => [
                ['title' => '40k subscribers', 'quote' => 'The product updates are the clearest in our industry.'],
                ['title' => '4.9/5 readability', 'quote' => 'It reads like a real publication, not a marketing blog.'],
                ['title' => '3x return visits', 'quote' => 'The topic areas keep me coming back for the engineering posts.'],
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
                ['title' => 'Shipping the new editor', 'summary' => 'How the team rebuilt the writing experience from the ground up.'],
                ['title' => 'Designing for trust', 'summary' => 'The principles behind a calmer, more legible product surface.'],
                ['title' => 'Inside our render pipeline', 'summary' => 'An engineering deep-dive into how pages stay fast at scale.'],
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
                ['label' => 'Subscribe', 'url' => '#newsletter', 'style' => 'primary'],
                ['label' => 'Read featured stories', 'url' => '#featured-posts', 'style' => 'secondary'],
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
                ['label' => 'Featured', 'url' => '#featured-posts'],
                ['label' => 'Topics', 'url' => '#topic-areas'],
                ['label' => 'Product updates', 'url' => '#product-updates'],
                ['label' => 'Templates', 'url' => '#template-stories'],
            ],
            'ctaLabel' => 'Subscribe',
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
            'summary' => 'A dark-masthead editorial publication for product updates, design essays, and engineering stories.',
            'columns' => [
                [
                    'heading' => 'Read',
                    'links' => [
                        ['label' => 'Featured', 'url' => '#featured-posts'],
                        ['label' => 'Topics', 'url' => '#topic-areas'],
                    ],
                ],
                [
                    'heading' => 'Explore',
                    'links' => [
                        ['label' => 'Product updates', 'url' => '#product-updates'],
                        ['label' => 'Templates', 'url' => '#template-stories'],
                    ],
                ],
                [
                    'heading' => 'Follow',
                    'links' => [
                        ['label' => 'Subscribe', 'url' => '#newsletter'],
                        ['label' => 'Authors', 'url' => '#author-callouts'],
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
