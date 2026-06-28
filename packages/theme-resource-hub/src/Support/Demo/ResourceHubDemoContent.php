<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\ResourceHub\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Resource Hub theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the live
 * page emits the theme's signature renderers (category-navigation /
 * website-examples / social-templates / courses-books / learning-resources /
 * newsletter) alongside the shared hero/proof/cta — every surface becomes a full
 * search-led resource library rather than the shared five-section skeleton.
 */
final class ResourceHubDemoContent implements ProvidesThemeDemoContent
{
    private const string BRAND = 'Resource Hub';

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
            title: self::BRAND . ' — Website examples, templates, courses & learning',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'Inspiration and education in one search-led hub',
                'Resource Hub gathers website examples, social templates, courses, books, and learning resources into one fast, search-led library.',
            ),
            renderData: [
                'summary' => 'Resource Hub is a search-led library of website examples, social templates, courses, books, and learning resources for people who build on the web.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Resource Hub',
                        heading: 'Inspiration and education in one search-led hub',
                        summary: 'Browse website examples, social templates, courses, books, and learning resources — all discoverable through one fast search, organised by what you are trying to make.',
                        primaryLabel: 'Browse examples',
                        primaryUrl: '#website-examples',
                        secondaryLabel: 'Explore templates',
                        secondaryUrl: '#social-templates',
                        mediaUrl: $media['hero'][0],
                        mediaAlt: 'Resource Hub library homepage',
                    ),
                    $this->categoryNavigationSection(
                        heading: 'Find your way in by category',
                        summary: 'Jump straight to the kind of resource you need. Every category is search-indexed and curated by the editors.',
                    ),
                    $this->websiteExamplesSection($media),
                    $this->socialTemplatesSection(),
                    $this->coursesBooksSection($media),
                    $this->learningResourcesSection(),
                    $this->proofSection(
                        heading: 'A library people actually finish',
                        summary: 'What the hub looks like in numbers.',
                    ),
                    $this->newsletterSection(
                        heading: 'New resources, every Thursday',
                        summary: 'One short email a week: the best new examples, templates, and courses, hand-picked. No spam, unsubscribe in a click.',
                    ),
                    $this->ctaSection(
                        heading: 'Start searching the hub',
                        summary: 'Type what you are building and let the library bring back examples, templates, and courses that fit.',
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
            name: self::BRAND . ' Library',
            title: 'Browse the library — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'The whole library, built to be scanned',
                'A structured archive of website examples, templates, courses, and books you can filter by category and skill level.',
            ),
            renderData: [
                'summary' => 'Every resource in one scannable archive — website examples, social templates, courses, and books, filterable by category and level.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Browse the library',
                        heading: 'The whole library, built to be scanned',
                        summary: 'Filter by category, skill level, or format. Every card links to a vetted resource with a short note on why it earned a place here.',
                        primaryLabel: 'Jump to categories',
                        primaryUrl: '#category-navigation',
                        secondaryLabel: 'Subscribe for new picks',
                        secondaryUrl: '#newsletter',
                        mediaUrl: $media['listing'][0] ?? $media['hero'][0],
                        mediaAlt: 'Resource Hub archive listing',
                    ),
                    $this->contentListingSection(
                        heading: 'Latest additions to the library',
                        summary: 'Fresh examples, templates, courses, and books, newest first.',
                    ),
                    $this->categoryNavigationSection(
                        heading: 'Filter by category',
                        summary: 'Narrow the archive to exactly the kind of resource you are after.',
                    ),
                    $this->newsletterSection(
                        heading: 'Get new additions in your inbox',
                        summary: 'Subscribe and we will send the best new resources as they land in the library.',
                    ),
                    $this->ctaSection(
                        heading: 'Cannot find it? Search instead',
                        summary: 'Search reaches across every example, template, course, and book in the hub at once.',
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
            name: self::BRAND . ' Resource',
            title: 'The Modern Landing Page Pattern Library — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'The Modern Landing Page Pattern Library',
                'A deep-dive resource collecting forty annotated landing-page examples, the templates behind them, and the courses that teach the craft.',
            ),
            renderData: [
                'summary' => 'A deep-dive resource: forty annotated landing-page examples, the templates behind them, and the courses that teach the craft.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Featured resource',
                        heading: 'The Modern Landing Page Pattern Library',
                        summary: 'Forty annotated landing pages, the templates that recreate them, and a short reading list — everything you need to ship a page that converts.',
                        primaryLabel: 'See the examples',
                        primaryUrl: '#website-examples',
                        secondaryLabel: 'Grab the templates',
                        secondaryUrl: '#social-templates',
                        mediaUrl: $media['detail'][0],
                        mediaAlt: 'Landing page pattern library resource',
                    ),
                    $this->websiteExamplesSection($media),
                    $this->coursesBooksSection($media),
                    $this->learningResourcesSection(),
                    $this->contentListingSection(
                        heading: 'Related resources',
                        summary: 'More from the same category, in case this is not quite the fit.',
                    ),
                    $this->ctaSection(
                        heading: 'Save this resource to your library',
                        summary: 'Subscribe to keep it handy and get the next pattern library the moment it ships.',
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
            name: self::BRAND . ' Submit',
            title: 'Subscribe & submit a resource — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Subscribe, or submit a resource',
                'Join the weekly digest, or send us a website example, template, or course you think belongs in the hub.',
            ),
            renderData: [
                'summary' => 'Join the weekly digest, or submit a website example, template, or course you think deserves a place in the library.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Get involved',
                        heading: 'Subscribe, or submit a resource',
                        summary: 'The hub grows on reader recommendations. Subscribe to the weekly digest below, or tell us about an example, template, or course we should add.',
                        primaryLabel: 'Subscribe to the digest',
                        primaryUrl: '#newsletter',
                        secondaryLabel: 'Browse the library',
                        secondaryUrl: '#website-examples',
                        mediaUrl: $media['contact'][0],
                        mediaAlt: 'Subscribe and submit a resource',
                    ),
                    $this->newsletterSection(
                        heading: 'One confident path to subscribe or submit',
                        summary: 'Drop your email for the Thursday digest, or use it to start a submission — both land with the editors who run the library.',
                    ),
                    $this->proofSection(
                        heading: 'What happens after you subscribe',
                        summary: 'How the digest and submissions work.',
                    ),
                    $this->ctaSection(
                        heading: 'Prefer to just browse?',
                        summary: 'The full library is open and search-led — dive in any time, no account needed.',
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
            title: 'No matching resources — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'No resources match that search yet',
                'A graceful empty state for a filtered library search that returned nothing.',
            ),
            renderData: [
                'summary' => 'Nothing matches that search yet — here are the categories and new picks to fall back on.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Search results',
                        heading: 'No resources match that search yet',
                        summary: 'Nothing in the library fits that query right now. Clear the filters to see everything, or browse a category below to get going.',
                        primaryLabel: 'Browse all categories',
                        primaryUrl: '#category-navigation',
                        secondaryLabel: 'Subscribe for new picks',
                        secondaryUrl: '#newsletter',
                        mediaUrl: $media['hero'][0],
                        mediaAlt: 'Empty library search results',
                    ),
                    $this->categoryNavigationSection(
                        heading: 'Try a category instead',
                        summary: 'Start from a category and let the library narrow itself down.',
                    ),
                    $this->learningResourcesSection(),
                    $this->ctaSection(
                        heading: 'Want us to add it?',
                        summary: 'Tell us what you searched for and we will hunt down a resource worth adding to the hub.',
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
            title: 'Page not found — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-404',
            content: $this->prose(
                'That resource has moved',
                'A not-found page that routes visitors back into the library search and categories.',
            ),
            renderData: [
                'summary' => 'That link is broken or the resource has moved — here is the way back into the library.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: '404',
                        heading: 'That resource has moved or retired',
                        summary: 'The link is broken or the resource has been retired from the library. Head back to search, or pick a category to keep exploring.',
                        primaryLabel: 'Back to the hub',
                        primaryUrl: '/',
                        secondaryLabel: 'Browse categories',
                        secondaryUrl: '#category-navigation',
                        mediaUrl: $media['hero'][0],
                        mediaAlt: 'Resource not found',
                    ),
                    $this->categoryNavigationSection(
                        heading: 'Pick a category to continue',
                        summary: 'These are the busiest corners of the library right now.',
                    ),
                    $this->ctaSection(
                        heading: 'Looking for something specific?',
                        summary: 'Search the whole hub from one box — examples, templates, courses, and books at once.',
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
            title: 'Join the weekly resource digest — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'One email a week, all signal',
                'A focused conversion page inviting visitors to subscribe to the weekly resource digest.',
            ),
            renderData: [
                'summary' => 'Join thousands of builders who get the best new website examples, templates, and courses every Thursday.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Join the digest',
                        heading: 'One email a week, all signal',
                        summary: 'Get the best new website examples, social templates, courses, and books delivered every Thursday — curated, never auto-generated.',
                        primaryLabel: 'Subscribe free',
                        primaryUrl: '#newsletter',
                        secondaryLabel: 'See a recent issue',
                        secondaryUrl: '#website-examples',
                        mediaUrl: $media['cta'][0],
                        mediaAlt: 'Subscribe to the resource digest',
                    ),
                    $this->proofSection(
                        heading: 'Why builders subscribe',
                        summary: 'The numbers behind the digest.',
                    ),
                    $this->newsletterSection(
                        heading: 'Add your email and you are in',
                        summary: 'No account, no spam. One issue a week, and you can leave whenever you like.',
                    ),
                    $this->ctaSection(
                        heading: 'Ready when you are',
                        summary: 'Subscribe now and the next issue lands in your inbox this Thursday.',
                    ),
                ],
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function heroSection(
        string $eyebrow,
        string $heading,
        string $summary,
        string $primaryLabel,
        string $primaryUrl,
        string $secondaryLabel,
        string $secondaryUrl,
        string $mediaUrl,
        string $mediaAlt,
    ): array {
        return [
            'type' => 'hero',
            'eyebrow' => $eyebrow,
            'heading' => $heading,
            'summary' => $summary,
            'primary_label' => $primaryLabel,
            'primary_url' => $primaryUrl,
            'secondary_label' => $secondaryLabel,
            'secondary_url' => $secondaryUrl,
            'actions' => [
                ['label' => $primaryLabel, 'url' => $primaryUrl, 'style' => 'primary'],
                ['label' => $secondaryLabel, 'url' => $secondaryUrl, 'style' => 'secondary'],
            ],
            'notes' => [
                'Search-led discovery across every resource type',
                'Curated by editors, never auto-generated',
            ],
            'mediaUrl' => $mediaUrl,
            'mediaAlt' => $mediaAlt,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function categoryNavigationSection(string $heading, string $summary): array
    {
        return [
            'type' => 'category-navigation',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Website examples', 'summary' => 'Annotated screenshots of real sites worth studying.', 'url' => '#website-examples'],
                ['title' => 'Social templates', 'summary' => 'Ready-to-edit graphics for launches and updates.', 'url' => '#social-templates'],
                ['title' => 'Courses & books', 'summary' => 'Structured learning, from quick reads to full programmes.', 'url' => '#courses-books'],
                ['title' => 'Learning resources', 'summary' => 'Guides, references, and tools the editors keep open.', 'url' => '#learning-resources'],
                ['title' => 'Free starters', 'summary' => 'No-cost templates and kits to get moving fast.', 'url' => '#social-templates'],
                ['title' => 'Newest first', 'summary' => 'Everything added to the library this month.', 'url' => '#content-listing'],
            ],
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function websiteExamplesSection(array $media): array
    {
        $images = array_values(array_unique(array_merge($media['listing'], $media['detail'], $media['proof'])));

        $examples = [
            ['title' => 'Linear — product marketing site', 'meta' => 'SaaS landing', 'summary' => 'A masterclass in restraint: one idea per screen, type doing the heavy lifting.', 'care_note' => 'Studied for pacing and whitespace.'],
            ['title' => 'Stripe Docs — developer home', 'meta' => 'Documentation', 'summary' => 'How to make dense reference material feel navigable and calm.', 'care_note' => 'Studied for information density.'],
            ['title' => 'Ghost — publishing platform', 'meta' => 'Editorial', 'summary' => 'Warm, content-first design that puts the writing front and centre.', 'care_note' => 'Studied for editorial hierarchy.'],
            ['title' => 'Arc — browser launch page', 'meta' => 'Product launch', 'summary' => 'Motion and storytelling carrying a brand-new category.', 'care_note' => 'Studied for launch narrative.'],
        ];

        $items = [];

        foreach ($examples as $index => $example) {
            $items[] = [
                ...$example,
                'url' => '#example-' . ($index + 1),
                'image' => $images[$index % max(count($images), 1)] ?? null,
                'imageUrl' => $images[$index % max(count($images), 1)] ?? null,
            ];
        }

        return [
            'type' => 'website-examples',
            'heading' => 'Website examples worth studying',
            'summary' => 'Real sites, annotated by the editors with a note on exactly what makes each one work.',
            'items' => $items,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function socialTemplatesSection(): array
    {
        return [
            'type' => 'social-templates',
            'heading' => 'Social templates ready to edit',
            'summary' => 'Drop-in graphics for launches, announcements, and milestones — open them, swap the copy, ship.',
            'label' => 'Open the template pack',
            'url' => '#newsletter',
            'items' => [
                ['title' => 'Launch announcement set', 'summary' => 'A coordinated pack for the day you go live, sized for every platform.'],
                ['title' => 'Milestone & metrics cards', 'summary' => 'Clean templates for sharing numbers without making a spreadsheet.'],
                ['title' => 'Quote & testimonial frames', 'summary' => 'Turn a kind word from a customer into a shareable graphic in seconds.'],
                ['title' => 'Event & webinar covers', 'summary' => 'Promote the next session with templates that match your brand.'],
            ],
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function coursesBooksSection(array $media): array
    {
        $images = array_values(array_unique(array_merge($media['detail'], $media['listing'])));

        $stories = [
            ['title' => 'Refactoring UI', 'meta' => 'Book · Design', 'summary' => 'Practical visual design tactics for developers who ship interfaces.'],
            ['title' => 'CSS for JavaScript Developers', 'meta' => 'Course · Front-end', 'summary' => 'A deep, interactive course that finally makes layout click.'],
            ['title' => 'The Reading List for Founders', 'meta' => 'Book · Strategy', 'summary' => 'Twelve titles the editors return to when the brief gets fuzzy.'],
        ];

        $items = [];

        foreach ($stories as $index => $story) {
            $items[] = [
                ...$story,
                'url' => '#course-' . ($index + 1),
                'image' => $images[$index % max(count($images), 1)] ?? null,
                'imageUrl' => $images[$index % max(count($images), 1)] ?? null,
            ];
        }

        return [
            'type' => 'courses-books',
            'heading' => 'Courses & books, vetted by the editors',
            'summary' => 'Structured learning we have worked through ourselves — no affiliate filler, just the ones that earned their place.',
            'items' => $items,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function learningResourcesSection(): array
    {
        return [
            'type' => 'learning-resources',
            'heading' => 'Learning resources to keep open',
            'summary' => 'References, guides, and tools the editors keep in a browser tab all week.',
            'items' => [
                ['title' => 'The accessibility checklist', 'summary' => 'A practical, plain-language pass to run before any page ships.'],
                ['title' => 'Type scale & spacing guide', 'summary' => 'Get rhythm right with ratios you can actually remember.'],
                ['title' => 'Copy patterns for empty states', 'summary' => 'What to say when there is nothing to show yet.'],
                ['title' => 'Performance budgets, explained', 'summary' => 'Set limits that keep pages fast as they grow.'],
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
                ['title' => 'Twelve dashboard layouts worth copying', 'category' => 'Website examples', 'summary' => 'Annotated screenshots of analytics and admin UIs that stay legible at scale.'],
                ['title' => 'The pricing-page swipe file', 'category' => 'Templates', 'summary' => 'Layouts and copy patterns from pricing pages that convert.'],
                ['title' => 'Learn TypeScript in a weekend', 'category' => 'Courses & books', 'summary' => 'A focused path from curious to comfortable, with no filler.'],
                ['title' => 'Writing microcopy that helps', 'category' => 'Learning resources', 'summary' => 'A short guide to the words around your buttons and forms.'],
                ['title' => 'Onboarding flows, taken apart', 'category' => 'Website examples', 'summary' => 'Step-by-step teardowns of first-run experiences done right.'],
                ['title' => 'The launch-week social kit', 'category' => 'Templates', 'summary' => 'Everything you need to announce, all sized and ready.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function proofSection(string $heading, string $summary): array
    {
        return [
            'type' => 'proof',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['value' => '1,200+', 'label' => 'Resources curated and annotated by the editors.'],
                ['value' => '24k', 'label' => 'Builders reading the weekly digest.'],
                ['value' => 'Thu', 'label' => 'One issue a week, the same day, every week.'],
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
            'label' => 'Subscribe free',
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
            'label' => 'Browse the library',
            'url' => '#website-examples',
            'actions' => [
                ['label' => 'Browse the library', 'url' => '#website-examples', 'style' => 'primary'],
                ['label' => 'Subscribe to the digest', 'url' => '#newsletter', 'style' => 'secondary'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function navigation(): array
    {
        return [
            'brand' => self::BRAND,
            'brandName' => self::BRAND,
            'items' => [
                ['label' => 'Examples', 'url' => '#website-examples'],
                ['label' => 'Templates', 'url' => '#social-templates'],
                ['label' => 'Courses', 'url' => '#courses-books'],
                ['label' => 'Learning', 'url' => '#learning-resources'],
                ['label' => 'Subscribe', 'url' => '#newsletter'],
            ],
            'ctaLabel' => 'Subscribe free',
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
            'brand' => self::BRAND,
            'brandName' => self::BRAND,
            'summary' => 'A search-led library of website examples, templates, courses, and learning resources for people who build on the web.',
            'columns' => [
                [
                    'title' => 'Browse',
                    'heading' => 'Browse',
                    'links' => [
                        ['label' => 'Website examples', 'url' => '#website-examples'],
                        ['label' => 'Social templates', 'url' => '#social-templates'],
                        ['label' => 'Courses & books', 'url' => '#courses-books'],
                        ['label' => 'Learning resources', 'url' => '#learning-resources'],
                    ],
                ],
                [
                    'title' => 'The hub',
                    'heading' => 'The hub',
                    'links' => [
                        ['label' => 'Categories', 'url' => '#category-navigation'],
                        ['label' => 'Latest additions', 'url' => '#content-listing'],
                        ['label' => 'Submit a resource', 'url' => '#newsletter'],
                    ],
                ],
                [
                    'title' => 'Stay in touch',
                    'heading' => 'Stay in touch',
                    'links' => [
                        ['label' => 'Weekly digest', 'url' => '#newsletter'],
                        ['label' => 'hello@resourcehub.example', 'url' => 'mailto:hello@resourcehub.example'],
                    ],
                ],
            ],
            'items' => [
                [
                    'title' => 'Browse',
                    'links' => [
                        ['label' => 'Website examples', 'url' => '#website-examples'],
                        ['label' => 'Social templates', 'url' => '#social-templates'],
                        ['label' => 'Courses & books', 'url' => '#courses-books'],
                        ['label' => 'Learning resources', 'url' => '#learning-resources'],
                    ],
                ],
                [
                    'title' => 'The hub',
                    'links' => [
                        ['label' => 'Categories', 'url' => '#category-navigation'],
                        ['label' => 'Latest additions', 'url' => '#content-listing'],
                        ['label' => 'Submit a resource', 'url' => '#newsletter'],
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
