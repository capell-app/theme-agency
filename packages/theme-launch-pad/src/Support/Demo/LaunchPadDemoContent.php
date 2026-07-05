<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\LaunchPad\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Launch Pad theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the
 * page adapter emits the theme's signature renderers (category-navigation /
 * website-examples / paid-templates / partner-blocks / gallery-system /
 * newsletter) alongside the standard hero/proof/content-listing/cta — giving
 * every surface a full, individual landing-page gallery rather than the shared
 * skeleton.
 *
 * Anchor discipline: navigation and footer are rendered on every surface, so
 * their links point at real page paths, never section anchors. Hero and CTA
 * anchors are chosen per surface to only reference sections that actually
 * render on that surface.
 */
final class LaunchPadDemoContent implements ProvidesThemeDemoContent
{
    private const string BRAND = 'Galleria';

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
            title: self::BRAND . ' — Landing Page Gallery for SaaS & Startups',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'A landing-page gallery worth saving',
                'Galleria curates the best SaaS, ecommerce, and startup landing pages so design and growth teams can browse, save, and ship faster.',
            ),
            renderData: [
                'summary' => 'Galleria is a curated gallery of high-converting landing pages, paid templates, and partner work for SaaS, ecommerce, and startup teams.',
                'navigation' => $this->navigation($themeKey),
                'footer' => $this->footer($themeKey),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Landing-page inspiration gallery',
                        'heading' => 'A landing-page gallery worth saving',
                        'summary' => 'Browse hundreds of high-converting SaaS, ecommerce, and startup landing pages. Save the ones that inspire you, grab the template, and ship your next launch faster.',
                        'actions' => [
                            ['label' => 'Browse the gallery', 'url' => '#website-examples', 'style' => 'primary'],
                            ['label' => 'Explore templates', 'url' => '#paid-templates', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['hero'][0] ?? null,
                        'mediaAlt' => 'A SaaS landing page captured in the Galleria gallery',
                    ],
                    $this->categoryNavigationSection(
                        heading: 'Browse by what you are building',
                        summary: 'Jump straight to the pages that match your launch — by industry, page type, or conversion goal.',
                        categoryUrl: '#website-examples',
                    ),
                    [
                        'type' => 'utility-hero',
                        'heading' => 'Search the gallery by industry or page type',
                        'summary' => 'Search hundreds of captures, or submit your own landing page for the next weekly drop.',
                        'searchUrl' => '#website-examples',
                        'linkLabel' => 'Submit your landing page',
                        'linkUrl' => '/theme-' . $themeKey . '-contact',
                    ],
                    $this->websiteExamplesSection(
                        heading: 'Latest landing pages, freshly captured',
                        summary: 'New screenshots every week from real products shipping real launches across SaaS, ecommerce, and startups.',
                        media: $media,
                        detailUrl: '/theme-' . $themeKey . '-detail',
                        templateUrl: '#paid-templates',
                    ),
                    $this->paidTemplatesSection(
                        heading: 'Paid templates ready to ship',
                        summary: 'Production-ready landing pages you can buy once, customise, and launch this week.',
                        browseUrl: '#website-examples',
                        purchaseUrl: '/theme-' . $themeKey . '-contact',
                    ),
                    $this->partnerBlocksSection(
                        heading: 'Featured studios & partners',
                        summary: 'The agencies and freelancers behind the work — available to build your next landing page.',
                        contactUrl: '/theme-' . $themeKey . '-contact',
                    ),
                    $this->gallerySystemSection(
                        heading: 'Built to be browsed, saved, and shared',
                        summary: 'Votes, comments, saved collections, and price tags keep the gallery useful for the whole team.',
                    ),
                    $this->proofSection(
                        heading: 'A gallery teams come back to',
                        summary: 'Why design and growth teams make Galleria part of their launch routine.',
                    ),
                    $this->ctaSection(
                        heading: 'Find your next landing page in minutes',
                        summary: 'Start browsing the curated gallery free. Save the pages you love and grab a template when you are ready to ship.',
                        label: 'Browse the gallery',
                        url: '#website-examples',
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
            name: self::BRAND . ' Gallery',
            title: 'Landing page archive — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'The full landing-page archive',
                'Every captured landing page, template, and component in one scannable archive you can filter and save.',
            ),
            renderData: [
                'summary' => 'A structured archive of landing pages, paid templates, and components — filterable by industry, goal, and style.',
                'navigation' => $this->navigation($themeKey),
                'footer' => $this->footer($themeKey),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Landing-page archive',
                        'heading' => 'The full landing-page archive',
                        'summary' => 'Hundreds of curated references kept easy to scan. Filter by industry, page type, or conversion goal, then save what fits your next launch.',
                        'actions' => [
                            ['label' => 'Browse all pages', 'url' => '#content-listing', 'style' => 'primary'],
                            ['label' => 'View categories', 'url' => '#category-navigation', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['listing'][0] ?? null,
                        'mediaAlt' => 'A grid of captured landing pages in the Galleria archive',
                    ],
                    $this->contentListingSection(
                        heading: 'Recently added to the gallery',
                        summary: 'The newest landing pages captured across SaaS, ecommerce, and startup teams.',
                        media: $media,
                        detailUrl: '/theme-' . $themeKey . '-detail',
                    ),
                    $this->categoryNavigationSection(
                        heading: 'Narrow it down by category',
                        summary: 'Pick a lane and the archive filters to the references that match it.',
                        categoryUrl: '#content-listing',
                    ),
                    $this->ctaSection(
                        heading: 'Save the pages worth revisiting',
                        summary: 'Create a free account to build collections, track prices, and get notified when new pages land.',
                        label: 'Browse the archive',
                        url: '#content-listing',
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
            name: self::BRAND . ' Landing Page',
            title: 'Northwind — SaaS landing page — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'Northwind — a SaaS landing page breakdown',
                'A close look at the Northwind launch page: structure, copy, and the template you can buy to ship your own.',
            ),
            renderData: [
                'summary' => 'A detailed breakdown of the Northwind SaaS launch page — what works, why, and the template behind it.',
                'navigation' => $this->navigation($themeKey),
                'footer' => $this->footer($themeKey),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Landing page breakdown',
                        'heading' => 'Northwind — a SaaS launch page that converts',
                        'summary' => 'A renewables platform that pairs a confident hero with a tight feature grid and a single, repeated call to action. Saved 4,200 times and counting.',
                        'actions' => [
                            ['label' => 'See more like this', 'url' => '#website-examples', 'style' => 'primary'],
                            ['label' => 'What the gallery tracks', 'url' => '#gallery-system', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['detail'][0] ?? null,
                        'mediaAlt' => 'The Northwind SaaS landing page hero section',
                    ],
                    $this->websiteExamplesSection(
                        heading: 'More pages in this style',
                        summary: 'Other SaaS launch pages that share Northwind\'s structure and pace.',
                        media: $media,
                        detailUrl: '/theme-' . $themeKey . '-detail',
                        templateUrl: '/theme-' . $themeKey . '-contact',
                    ),
                    $this->gallerySystemSection(
                        heading: 'What the gallery tracks for this page',
                        summary: 'Saves, votes, comments, and the live template price — everything teams use to decide.',
                    ),
                    $this->ctaSection(
                        heading: 'Ship a page like Northwind',
                        summary: 'Buy the template once, swap in your copy and brand, and launch this week.',
                        label: 'See more landing pages',
                        url: '#website-examples',
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
            title: 'Submit a landing page — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Submit your landing page',
                'Built something worth showing? Submit your landing page, pitch a template, or subscribe for the weekly capture digest.',
            ),
            renderData: [
                'summary' => 'Submit a landing page, pitch a paid template, or subscribe to the weekly gallery digest.',
                'navigation' => $this->navigation($themeKey),
                'footer' => $this->footer($themeKey),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Submit & subscribe',
                        'heading' => 'Get your work into the gallery',
                        'summary' => 'Submit a live landing page, pitch a template for the marketplace, or join the weekly digest of fresh captures. We review every submission within two working days.',
                        'actions' => [
                            ['label' => 'Subscribe for updates', 'url' => '#newsletter', 'style' => 'primary'],
                            ['label' => 'What happens next', 'url' => '#proof', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['contact'][0] ?? null,
                        'mediaAlt' => 'A designer reviewing a landing page submission',
                    ],
                    $this->newsletterSection(
                        heading: 'One path to submit, pitch, or subscribe',
                        summary: 'Drop your email to get the weekly capture digest and a link to submit your own landing page.',
                    ),
                    $this->proofSection(
                        heading: 'What happens after you submit',
                        summary: 'How the gallery handles new pages and template pitches.',
                    ),
                    $this->ctaSection(
                        heading: 'Have a page ready to show?',
                        summary: 'Send us the URL and we will capture, tag, and feature it in the next weekly drop.',
                        label: 'Subscribe for updates',
                        url: '#newsletter',
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
            title: 'No matches — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'No pages match that filter yet',
                'A graceful empty state for a filtered gallery view with no matching landing pages.',
            ),
            renderData: [
                'summary' => 'No landing pages match that filter yet — but the gallery can still point you somewhere useful.',
                'navigation' => $this->navigation($themeKey),
                'footer' => $this->footer($themeKey),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Gallery search',
                        'heading' => 'No pages match that filter — yet',
                        'summary' => 'We have not captured a landing page for this combination. Clear the filter to see everything, or browse a category instead.',
                        'actions' => [
                            ['label' => 'Clear filters', 'url' => '#content-listing', 'style' => 'primary'],
                            ['label' => 'Browse categories', 'url' => '#category-navigation', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['proof'][0] ?? null,
                        'mediaAlt' => 'An empty gallery filter view',
                    ],
                    [
                        'type' => 'content-listing',
                        'heading' => 'Nothing to show for this filter',
                        'summary' => 'When pages match this filter they will appear here, newest first.',
                        'items' => [],
                    ],
                    $this->categoryNavigationSection(
                        heading: 'Try a category instead',
                        summary: 'Pick a lane and the gallery filters to references that match it.',
                        categoryUrl: '#content-listing',
                    ),
                    $this->ctaSection(
                        heading: 'Looking for a specific page?',
                        summary: 'Tell us what you are building and we will surface the closest captures in the gallery.',
                        label: 'Browse categories',
                        url: '#category-navigation',
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
        $homeUrl = '/theme-' . $themeKey;

        return new ThemeDemoPageDefinition(
            surface: 'not-found',
            name: self::BRAND . ' 404',
            title: 'Page not found — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-404',
            content: $this->prose(
                'Page not found',
                'A not-found page that routes visitors back into the gallery and template paths.',
            ),
            renderData: [
                'summary' => 'That page has moved or never existed — here is the way back into the gallery.',
                'navigation' => $this->navigation($themeKey),
                'footer' => $this->footer($themeKey),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => '404',
                        'heading' => 'This page wandered off the gallery wall',
                        'summary' => 'The link is broken or the page has moved. Head back to the homepage, or tell us what you were looking for.',
                        'actions' => [
                            ['label' => 'Back to the homepage', 'url' => $homeUrl, 'style' => 'primary'],
                            ['label' => 'Browse the full archive', 'url' => '/theme-' . $themeKey . '-directory', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['cta'][0] ?? null,
                        'mediaAlt' => 'A blank gallery wall where a landing page used to hang',
                    ],
                    $this->ctaSection(
                        heading: 'Still looking for something?',
                        summary: 'Tell us the page you wanted and we will point you to the right capture.',
                        label: 'Back to the homepage',
                        url: $homeUrl,
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
            name: self::BRAND . ' Pro',
            title: 'Go Pro — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Unlock the full gallery with Pro',
                'A focused conversion page inviting teams to upgrade to a Galleria Pro plan.',
            ),
            renderData: [
                'summary' => 'Upgrade to Galleria Pro for unlimited saves, full-resolution captures, and early access to new templates.',
                'navigation' => $this->navigation($themeKey),
                'footer' => $this->footer($themeKey),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Galleria Pro',
                        'heading' => 'Unlock the full gallery with Pro',
                        'summary' => 'Unlimited saved collections, full-resolution screenshots, advanced filters, and early access to every new template the day it lands.',
                        'actions' => [
                            ['label' => 'Start your Pro trial', 'url' => '#cta', 'style' => 'primary'],
                            ['label' => 'Why teams go Pro', 'url' => '#proof', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['hero'][0] ?? null,
                        'mediaAlt' => 'A Galleria Pro saved collection of landing pages',
                    ],
                    $this->proofSection(
                        heading: 'Why teams go Pro',
                        summary: 'The numbers behind a Galleria Pro subscription.',
                    ),
                    $this->ctaSection(
                        heading: 'Start your Pro trial today',
                        summary: 'Fourteen days free, no card required. Cancel any time and keep the collections you built.',
                        label: 'Start your Pro trial',
                        url: '#cta',
                    ),
                ],
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function categoryNavigationSection(string $heading, string $summary, string $categoryUrl): array
    {
        return [
            'type' => 'category-navigation',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'SaaS & software', 'summary' => 'Product launches, pricing pages, and free-trial flows from B2B and B2C software.', 'url' => $categoryUrl],
                ['title' => 'Ecommerce & DTC', 'summary' => 'Storefronts, product pages, and seasonal drops built to convert browsers into buyers.', 'url' => $categoryUrl],
                ['title' => 'Startups & fundraising', 'summary' => 'Pre-launch teasers, waitlists, and investor-ready one-pagers.', 'url' => $categoryUrl],
                ['title' => 'Mobile & app', 'summary' => 'App-store landing pages and onboarding flows tuned for installs.', 'url' => $categoryUrl],
            ],
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function websiteExamplesSection(string $heading, string $summary, array $media, string $detailUrl, string $templateUrl): array
    {
        $pool = array_values(array_unique(array_merge(
            $media['listing'],
            $media['detail'],
            $media['proof'],
            $media['cta'],
        )));

        $examples = [
            ['title' => 'Northwind', 'summary' => 'A renewables SaaS launch page with a confident hero and a single repeated call to action.', 'meta' => 'SaaS · Free template'],
            ['title' => 'Harbour Goods', 'summary' => 'An ecommerce drop page for an independent coffee roaster, built around one hero product.', 'meta' => 'Ecommerce · $49'],
            ['title' => 'Meridian Pay', 'summary' => 'A fintech infrastructure one-pager tuned for enterprise demos and investor reads.', 'meta' => 'Startup · $79'],
            ['title' => 'Atlas Studio', 'summary' => 'A creative-tool waitlist page that turned a teaser into 9,000 signups in two weeks.', 'meta' => 'SaaS · Free template'],
            ['title' => 'Verda Home', 'summary' => 'A DTC homeware storefront with editorial photography and a calm conversion path.', 'meta' => 'Ecommerce · $59'],
            ['title' => 'Lumen Health', 'summary' => 'A health-tech app landing page built to drive installs from a single screen.', 'meta' => 'Mobile · $39'],
        ];

        $items = [];

        foreach ($examples as $index => $example) {
            $image = $pool[$index % max(count($pool), 1)] ?? null;
            $items[] = [
                ...$example,
                'url' => $detailUrl,
                'templateUrl' => $templateUrl,
                'image' => $image,
                'imageUrl' => $image,
                'imageAlt' => $example['title'] . ' landing page',
            ];
        }

        return [
            'type' => 'website-examples',
            'heading' => $heading,
            'summary' => $summary,
            'items' => $items,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function paidTemplatesSection(string $heading, string $summary, string $browseUrl, string $purchaseUrl): array
    {
        return [
            'type' => 'paid-templates',
            'heading' => $heading,
            'summary' => $summary,
            'label' => 'Keep browsing free',
            'url' => $browseUrl,
            'items' => [
                ['title' => 'Launch Kit — SaaS', 'summary' => 'A six-section SaaS launch page with pricing table, FAQ, and trial CTA.', 'price' => '$79', 'framework' => 'Webflow', 'url' => $purchaseUrl],
                ['title' => 'Storefront — Ecommerce', 'summary' => 'A product-led DTC page with gallery, reviews, and sticky buy bar.', 'price' => '$59', 'framework' => 'Shopify', 'url' => $purchaseUrl],
                ['title' => 'Waitlist — Startup', 'summary' => 'A single-screen teaser page with countdown, email capture, and social proof.', 'free' => true, 'framework' => 'HTML', 'url' => $purchaseUrl],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function partnerBlocksSection(string $heading, string $summary, string $contactUrl): array
    {
        return [
            'type' => 'partner-blocks',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['meta' => 'Brand & web studio', 'title' => 'Fieldwork', 'summary' => 'An independent studio shipping brand-led landing pages for funded startups.', 'url' => $contactUrl],
                ['meta' => 'Conversion freelancer', 'title' => 'Priya Nadkarni', 'summary' => 'A landing-page specialist who turns rough copy into pages that convert.', 'url' => $contactUrl],
                ['meta' => 'Ecommerce agency', 'title' => 'Harbour Collective', 'summary' => 'A DTC team building storefronts and drop pages for growing retail brands.', 'url' => $contactUrl],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function gallerySystemSection(string $heading, string $summary): array
    {
        return [
            'type' => 'gallery-system',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Saved collections', 'summary' => 'Group the pages you love into named boards and share them with the whole team.'],
                ['title' => 'Votes & comments', 'summary' => 'See what the community rates highest and read the notes on what makes each page work.'],
                ['title' => 'Live template prices', 'summary' => 'Every paid template shows its current price so you can budget the build before you start.'],
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
                ['value' => '1,200+', 'label' => 'Landing pages captured and tagged'],
                ['value' => 'Weekly', 'label' => 'Fresh captures added every Friday'],
                ['value' => '2 days', 'label' => 'Typical review time for a submission'],
            ],
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function contentListingSection(string $heading, string $summary, array $media, string $detailUrl): array
    {
        $pool = array_values(array_unique(array_merge($media['listing'], $media['detail'], $media['proof'])));

        $rows = [
            ['category' => 'SaaS', 'title' => 'Northwind — renewables platform', 'summary' => 'A confident hero, a tight feature grid, and one repeated call to action.'],
            ['category' => 'Ecommerce', 'title' => 'Harbour Goods — coffee drop', 'summary' => 'A single-product drop page built around bold photography.'],
            ['category' => 'Startup', 'title' => 'Atlas Studio — waitlist', 'summary' => 'A teaser page that converted a launch into 9,000 signups.'],
            ['category' => 'Mobile', 'title' => 'Lumen Health — app landing', 'summary' => 'A one-screen install page for a health-tracking app.'],
        ];

        $items = [];

        foreach ($rows as $index => $row) {
            $image = $pool[$index % max(count($pool), 1)] ?? null;
            $items[] = [
                ...$row,
                'url' => $detailUrl,
                'image' => $image,
                'imageUrl' => $image,
                'imageAlt' => $row['title'],
            ];
        }

        return [
            'type' => 'content-listing',
            'heading' => $heading,
            'summary' => $summary,
            'items' => $items,
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
    private function ctaSection(string $heading, string $summary, string $label, string $url): array
    {
        return [
            'type' => 'cta',
            'heading' => $heading,
            'summary' => $summary,
            'label' => $label,
            'url' => $url,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function navigation(string $themeKey): array
    {
        $homeUrl = '/theme-' . $themeKey;

        return [
            'brandName' => self::BRAND,
            'brand' => self::BRAND,
            'items' => [
                ['label' => 'Websites', 'url' => $homeUrl . '-directory'],
                ['label' => 'Templates', 'url' => $homeUrl . '-directory'],
                ['label' => 'Categories', 'url' => $homeUrl . '-directory'],
                ['label' => 'Partners', 'url' => $homeUrl],
                ['label' => 'Pro', 'url' => $homeUrl . '-cta'],
            ],
            'ctaLabel' => 'Submit a page',
            'ctaUrl' => $homeUrl . '-contact',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(string $themeKey): array
    {
        $homeUrl = '/theme-' . $themeKey;

        return [
            'brandName' => self::BRAND,
            'brand' => self::BRAND,
            'summary' => 'A curated gallery of high-converting landing pages, templates, and partner work.',
            'columns' => [
                [
                    'heading' => 'Gallery',
                    'title' => 'Gallery',
                    'links' => [
                        ['label' => 'Latest pages', 'url' => $homeUrl . '-directory'],
                        ['label' => 'Categories', 'url' => $homeUrl . '-directory'],
                        ['label' => 'Partners', 'url' => $homeUrl],
                    ],
                ],
                [
                    'heading' => 'Marketplace',
                    'title' => 'Marketplace',
                    'links' => [
                        ['label' => 'Paid templates', 'url' => $homeUrl],
                        ['label' => 'Submit a page', 'url' => $homeUrl . '-contact'],
                        ['label' => 'Go Pro', 'url' => $homeUrl . '-cta'],
                    ],
                ],
                [
                    'heading' => 'Galleria',
                    'title' => 'Galleria',
                    'links' => [
                        ['label' => 'Weekly digest', 'url' => $homeUrl . '-contact'],
                        ['label' => 'studio@galleria.example', 'url' => 'mailto:studio@galleria.example'],
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
