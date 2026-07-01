<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\Commerce\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Commerce theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the
 * page adapter emits the theme's retail renderers (collections / product-finder
 * / product-grid / product-detail / comparison / lookbook / buying-guide /
 * campaign / store-event) alongside the standard hero/proof/cta — giving every
 * surface a full, individual storefront rather than the shared skeleton.
 */
final class CommerceDemoContent implements ProvidesThemeDemoContent
{
    private const string BRAND = 'Marlowe & Field';

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
            title: self::BRAND . ' — Considered Goods for Everyday Use',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'Considered goods for everyday use',
                'Marlowe & Field is a direct-to-consumer homeware label. We design hard-wearing kitchen, table, and home goods and sell them direct, so the value stays in the product.',
            ),
            renderData: [
                'summary' => 'Marlowe & Field designs hard-wearing kitchen, table, and home goods and sells them direct — built to last, priced without the middle markup.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'New season',
                        'heading' => 'Everyday goods built to outlast the trend cycle',
                        'summary' => 'Kitchen, table, and home essentials designed in our Leeds studio and made to be used hard for years. Free returns, lifetime repairs, and stock you can actually trust.',
                        'actions' => [
                            ['label' => 'Shop collections', 'url' => '#collections', 'style' => 'primary'],
                            ['label' => 'Browse the catalogue', 'url' => '#shop', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['hero'][0],
                        'mediaAlt' => 'A Marlowe & Field kitchen still life',
                        'badges' => [
                            ['label' => 'Free 60-day returns'],
                            ['label' => 'Lifetime repairs'],
                            ['label' => 'Made to order'],
                        ],
                    ],
                    $this->collectionsSection(
                        heading: 'Shop by collection',
                        summary: 'Four ranges, each built around how a room is actually used.',
                        media: $media,
                    ),
                    $this->productFinderSection(),
                    $this->productGridSection(
                        heading: 'This week’s best sellers',
                        summary: 'The pieces flying out of the studio right now — back in stock and ready to ship.',
                        media: $media,
                    ),
                    $this->proofSection(
                        heading: 'Why shoppers come back',
                        summary: 'The numbers behind a catalogue people keep returning to.',
                    ),
                    $this->blogTeaserSection(
                        heading: 'From the journal',
                        summary: 'Care guides, material stories, and the thinking behind each range.',
                    ),
                    $this->ctaSection(
                        heading: 'Find your everyday pieces',
                        summary: 'Start with a collection or filter the full catalogue. Free returns mean there is no risk in trying.',
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
            name: self::BRAND . ' Shop',
            title: 'Shop the kitchen range — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'Shop the kitchen range',
                'Browse the full kitchen catalogue — cookware, knives, and prep tools, filtered by how you cook.',
            ),
            renderData: [
                'summary' => 'The full kitchen catalogue — cookware, knives, and prep tools you can filter by material, use, and price.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Kitchen',
                        'heading' => 'Shop the kitchen range',
                        'summary' => 'Cookware, knives, and prep tools designed to earn their place on the counter. Filter by material, use, or price and add straight to basket.',
                        'actions' => [
                            ['label' => 'View the lookbook', 'url' => '#lookbook', 'style' => 'primary'],
                        ],
                        'mediaUrl' => $media['listing'][0] ?? $media['hero'][0],
                        'mediaAlt' => 'Kitchen range flat lay',
                    ],
                    $this->productFinderSection(),
                    $this->productGridSection(
                        heading: 'In the kitchen range',
                        summary: 'Every piece in stock today, ready to ship within two working days.',
                        media: $media,
                    ),
                    $this->lookbookSection($media),
                    $this->ctaSection(
                        heading: 'Not sure where to start?',
                        summary: 'Tell us how you cook and our team will build a basket that fits — no upsell, just the right kit.',
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
            name: self::BRAND . ' Product',
            title: 'The Field Chef’s Knife — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'The Field Chef’s Knife',
                'Our flagship 20cm chef’s knife — forged carbon steel, full tang, and balanced for daily prep.',
            ),
            renderData: [
                'summary' => 'The Field Chef’s Knife — forged carbon steel, full tang, and balanced for daily prep. Sharpened and shipped in two days.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Best seller',
                        'heading' => 'The Field Chef’s Knife',
                        'summary' => 'A 20cm forged carbon-steel blade with a full tang and a hand-finished oak handle. The one knife that handles ninety percent of the prep in a working kitchen.',
                        'actions' => [
                            ['label' => 'Back to the kitchen range', 'url' => '#shop', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['detail'][0],
                        'mediaAlt' => 'The Field Chef’s Knife on a board',
                    ],
                    $this->productDetailSection($media),
                    $this->comparisonSection(),
                    $this->proofSection(
                        heading: 'What owners say',
                        summary: 'Verified reviews from people who use this knife every day.',
                    ),
                    $this->productGridSection(
                        heading: 'Complete the set',
                        summary: 'The pieces our kitchen team reaches for alongside the chef’s knife.',
                        media: $media,
                    ),
                    $this->ctaSection(
                        heading: 'Add the chef’s knife to your kitchen',
                        summary: 'Free 60-day returns and a lifetime sharpening service. If it does not earn its place, send it back.',
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
            name: self::BRAND . ' Help',
            title: 'Help & support — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Help and support',
                'Questions about an order, a return, or a repair? Our Leeds team answers every message within one working day.',
            ),
            renderData: [
                'summary' => 'Questions about an order, a return, or a repair? Our Leeds team answers every message within one working day.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Help & support',
                        'heading' => 'We’re here to help with any order',
                        'summary' => 'Track a delivery, start a return, or book a repair. Email help@marloweandfield.example or use the live chat — a real person on the Leeds team replies within one working day.',
                        'actions' => [
                            ['label' => 'Email support', 'url' => 'mailto:help@marloweandfield.example', 'style' => 'primary'],
                            ['label' => 'Read the buying guides', 'url' => '#guides', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['contact'][0],
                        'mediaAlt' => 'The Marlowe & Field studio team',
                    ],
                    $this->buyingGuideSection(),
                    $this->storeEventSection(),
                    $this->proofSection(
                        heading: 'Support you can count on',
                        summary: 'How we look after every order, before and after it ships.',
                    ),
                    $this->ctaSection(
                        heading: 'Still have a question?',
                        summary: 'Send us the order number and what you need — we’ll have an answer back to you within a day.',
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
            title: 'No matching products — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'No products match that filter',
                'A graceful empty state for a filtered catalogue with no matching products — with routes back into the range.',
            ),
            renderData: [
                'summary' => 'Nothing matches that combination of filters yet — but the catalogue still has plenty to show you.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Catalogue',
                        'heading' => 'No products match those filters — yet',
                        'summary' => 'Nothing in stock fits that exact combination. Clear a filter to widen the search, or browse the best sellers below.',
                        'actions' => [
                            ['label' => 'Clear filters', 'url' => '#shop', 'style' => 'primary'],
                            ['label' => 'Shop best sellers', 'url' => '#shop', 'style' => 'secondary'],
                        ],
                    ],
                    [
                        'type' => 'product-grid',
                        'heading' => 'Nothing here right now',
                        'summary' => 'When products match this filter they’ll appear here, newest in stock first.',
                        'items' => [],
                    ],
                    $this->collectionsSection(
                        heading: 'Try another collection',
                        summary: 'Four ranges to explore while we restock that filter.',
                        media: $media,
                    ),
                    $this->ctaSection(
                        heading: 'Looking for something specific?',
                        summary: 'Tell us what you’re after and we’ll point you to it — or let you know when it’s back in stock.',
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
                'Page not found',
                'A not-found page that routes shoppers back into collections and the catalogue.',
            ),
            renderData: [
                'summary' => 'That product or page has sold out of its URL — here’s the way back to the shop.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => '404',
                        'heading' => 'This page has gone out of stock',
                        'summary' => 'The link is broken or the product has moved. Head back to the collections, or pick up where you left off in the catalogue.',
                        'actions' => [
                            ['label' => 'Back to home', 'url' => '/', 'style' => 'primary'],
                            ['label' => 'Shop collections', 'url' => '#collections', 'style' => 'secondary'],
                        ],
                    ],
                    $this->collectionsSection(
                        heading: 'Pick up where you left off',
                        summary: 'Jump straight back into one of our four ranges.',
                        media: $media,
                    ),
                    $this->ctaSection(
                        heading: 'Can’t find what you came for?',
                        summary: 'Tell us the product and we’ll track it down or recommend the closest match in stock.',
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
            name: self::BRAND . ' Sale',
            title: 'The mid-season sale — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'The mid-season sale is live',
                'A focused promotion page driving shoppers into the seasonal sale.',
            ),
            renderData: [
                'summary' => 'The mid-season sale is live — up to 30% off the kitchen and table ranges while stock lasts.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Mid-season sale',
                        'heading' => 'Up to 30% off the pieces you’ve been eyeing',
                        'summary' => 'A short run on overstocked kitchen and table goods. Same lifetime repairs, same free returns — just a better price for a few days only.',
                        'actions' => [
                            ['label' => 'Shop the sale', 'url' => '#shop', 'style' => 'primary'],
                            ['label' => 'See what’s included', 'url' => '#collections', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['cta'][0],
                        'mediaAlt' => 'Mid-season sale still life',
                    ],
                    $this->campaignSection(),
                    $this->productGridSection(
                        heading: 'In the sale this week',
                        summary: 'Marked down while stock lasts, then back to full price.',
                        media: $media,
                    ),
                    $this->ctaSection(
                        heading: 'The sale ends Sunday',
                        summary: 'Once it’s gone it’s gone — these are end-of-run prices on pieces we won’t discount again this year.',
                    ),
                ],
            ],
        );
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function collectionsSection(string $heading, string $summary, array $media): array
    {
        $pool = array_values(array_unique(array_merge(
            $media['listing'],
            $media['detail'],
            $media['proof'],
            $media['cta'],
        )));

        $collections = [
            ['title' => 'The Kitchen', 'type' => 'Cookware & knives', 'summary' => 'Forged knives, cast pans, and prep tools built for daily use.'],
            ['title' => 'The Table', 'type' => 'Tableware & glass', 'summary' => 'Stoneware, glassware, and linens that look as good worn-in as new.'],
            ['title' => 'The Larder', 'type' => 'Storage & pantry', 'summary' => 'Airtight jars, crocks, and boards that keep a kitchen in order.'],
            ['title' => 'The Home', 'type' => 'Living & care', 'summary' => 'Throws, brushes, and small goods for the rest of the house.'],
        ];

        $items = [];

        foreach ($collections as $index => $collection) {
            $image = $pool[$index % max(count($pool), 1)] ?? null;
            $items[] = [
                ...$collection,
                'url' => '#collection-' . ($index + 1),
                'image' => $image,
                'imageAlt' => $collection['title'] . ' collection',
            ];
        }

        return [
            'type' => 'collections',
            'heading' => $heading,
            'summary' => $summary,
            'items' => $items,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function productFinderSection(): array
    {
        return [
            'type' => 'product-finder',
            'heading' => 'Find the right piece fast',
            'summary' => 'Filter the catalogue the way you’d shop a good hardware store — by what it’s for, not by SKU.',
            'items' => [
                ['group' => 'Room', 'options' => ['Kitchen', 'Table', 'Larder', 'Home']],
                ['group' => 'Material', 'options' => ['Carbon steel', 'Stoneware', 'Oak', 'Linen']],
                ['group' => 'Use', 'options' => ['Everyday', 'Entertaining', 'Gifting', 'Pro kitchen']],
                ['group' => 'Price', 'options' => ['Under £30', '£30–£75', '£75–£150', '£150+']],
            ],
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function productGridSection(string $heading, string $summary, array $media): array
    {
        $pool = array_values(array_unique(array_merge(
            $media['listing'],
            $media['detail'],
            $media['proof'],
            $media['cta'],
        )));

        $products = [
            ['title' => 'Field Chef’s Knife, 20cm', 'price' => '£135', 'summary' => 'Forged carbon steel with a full tang and oak handle.', 'stockStatus' => 'In stock'],
            ['title' => 'Cast Iron Skillet, 26cm', 'price' => '£89', 'summary' => 'Pre-seasoned and built to outlive its owner.', 'stockStatus' => 'In stock'],
            ['title' => 'Stoneware Dinner Set', 'price' => '£120', 'summary' => 'Four-place setting glazed in slate and bone.', 'stockStatus' => 'Low stock'],
            ['title' => 'Oak Prep Board', 'price' => '£48', 'summary' => 'End-grain board that’s kind to your blades.', 'stockStatus' => 'In stock'],
            ['title' => 'Linen Apron', 'price' => '£42', 'summary' => 'Heavyweight linen with adjustable cross-back straps.', 'stockStatus' => 'In stock'],
            ['title' => 'Larder Jar Set of 3', 'price' => '£36', 'summary' => 'Airtight glass with cork seals for the pantry.', 'stockStatus' => 'In stock'],
        ];

        $items = [];

        foreach ($products as $index => $product) {
            $image = $pool[$index % max(count($pool), 1)] ?? null;
            $items[] = [
                ...$product,
                'url' => '#product-' . ($index + 1),
                'image' => $image,
                'imageAlt' => $product['title'],
            ];
        }

        return [
            'type' => 'product-grid',
            'heading' => $heading,
            'summary' => $summary,
            'items' => $items,
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function productDetailSection(array $media): array
    {
        $gallery = array_slice(array_values(array_unique(array_merge($media['detail'], $media['listing']))), 0, 4);

        return [
            'type' => 'product-detail',
            'heading' => 'The Field Chef’s Knife',
            'summary' => 'A 20cm forged carbon-steel blade, full tang, and a hand-finished oak handle. Sharpened to a 15-degree edge and ready to work straight out of the box.',
            'price' => '£135',
            'compareAtPrice' => '£160',
            'stockStatus' => 'In stock — ships in 2 days',
            'ctaLabel' => 'Add to basket',
            'ctaUrl' => '#basket',
            'gallery' => array_map(
                static fn (string $url): array => ['url' => $url, 'alt' => 'The Field Chef’s Knife detail'],
                $gallery,
            ),
            'variants' => [
                ['label' => '20cm chef’s'],
                ['label' => '15cm utility'],
                ['label' => '9cm paring'],
            ],
            'trustItems' => [
                ['label' => 'Free 60-day returns'],
                ['label' => 'Lifetime free sharpening'],
                ['label' => 'Secure checkout'],
            ],
            'recommendations' => [
                ['title' => 'Oak Prep Board', 'price' => '£48', 'url' => '#product-4'],
                ['title' => 'Honing Steel', 'price' => '£32', 'url' => '#product-7'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function comparisonSection(): array
    {
        return [
            'type' => 'comparison',
            'heading' => 'Which blade is right for you?',
            'summary' => 'Three knives from the Field range, side by side, so you can match the tool to the task.',
            'criteria' => [
                ['label' => 'Blade length', 'key' => 'length'],
                ['label' => 'Best for', 'key' => 'use'],
                ['label' => 'Steel', 'key' => 'steel'],
                ['label' => 'Weight', 'key' => 'weight'],
            ],
            'columns' => [
                ['title' => 'Field Chef’s', 'price' => '£135', 'length' => '20cm', 'use' => 'Everyday prep', 'steel' => 'Forged carbon', 'weight' => '210g'],
                ['title' => 'Field Utility', 'price' => '£95', 'length' => '15cm', 'use' => 'Trimming & slicing', 'steel' => 'Forged carbon', 'weight' => '140g'],
                ['title' => 'Field Paring', 'price' => '£65', 'length' => '9cm', 'use' => 'Detail work', 'steel' => 'Forged carbon', 'weight' => '90g'],
            ],
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function lookbookSection(array $media): array
    {
        $images = array_values(array_unique(array_merge($media['listing'], $media['proof'])));

        $looks = [
            ['title' => 'The working kitchen', 'type' => 'Styled at home', 'summary' => 'How our team sets up a counter for daily cooking — knives, boards, and crocks within reach.'],
            ['title' => 'The Sunday table', 'type' => 'Entertaining', 'summary' => 'Stoneware, linen, and glass dressed for a long lunch with friends.'],
            ['title' => 'The ordered larder', 'type' => 'Storage', 'summary' => 'Jars, labels, and crocks that turn a cupboard into a proper pantry.'],
        ];

        $items = [];

        foreach ($looks as $index => $look) {
            $items[] = [
                ...$look,
                'image' => $images[$index % max(count($images), 1)] ?? null,
            ];
        }

        return [
            'type' => 'lookbook',
            'heading' => 'Styled for the way you live',
            'summary' => 'See the ranges in the rooms they’re built for, then shop the look.',
            'items' => $items,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buyingGuideSection(): array
    {
        return [
            'type' => 'buying-guide',
            'heading' => 'Buy once, buy well',
            'summary' => 'Short guides from our makers to help you choose the right piece the first time.',
            'items' => [
                ['type' => 'Care guide', 'title' => 'Looking after carbon steel', 'summary' => 'How to season, dry, and store a carbon-steel knife so it lasts a lifetime.'],
                ['type' => 'Buying guide', 'title' => 'Choosing your first chef’s knife', 'summary' => 'Length, weight, and balance — what actually matters when you pick a blade.'],
                ['type' => 'Material story', 'title' => 'Why we use stoneware', 'summary' => 'The case for stoneware over porcelain for plates you use every day.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function storeEventSection(): array
    {
        return [
            'type' => 'store-event',
            'heading' => 'Meet us in person',
            'summary' => 'Pop-ups and workshops where you can handle the range before you buy.',
            'events' => [
                ['date' => 'Sat 14 Sep', 'title' => 'Knife sharpening clinic', 'location' => 'Leeds studio', 'summary' => 'Bring any blade and our makers will hone it free while you browse.', 'url' => '#event-1', 'ctaLabel' => 'Reserve a slot'],
                ['date' => '21–22 Sep', 'title' => 'Autumn table pop-up', 'location' => 'Spitalfields, London', 'summary' => 'The full table range, styled and stocked for a weekend only.', 'url' => '#event-2', 'ctaLabel' => 'Add to calendar'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function campaignSection(): array
    {
        return [
            'type' => 'campaign',
            'heading' => 'The mid-season sale',
            'summary' => 'Overstocked pieces from the kitchen and table ranges, marked down for a few days only.',
            'items' => [
                ['phase' => 'Kitchen', 'title' => 'Up to 30% off cookware', 'summary' => 'Cast pans and forged knives at end-of-run prices.', 'code' => 'KITCHEN30'],
                ['phase' => 'Table', 'title' => '25% off stoneware sets', 'summary' => 'Complete four-place settings while the last batches last.', 'code' => 'TABLE25'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function blogTeaserSection(string $heading, string $summary): array
    {
        return [
            'type' => 'blog-teaser',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['type' => 'Care guide', 'title' => 'Seasoning a cast iron pan', 'summary' => 'The five-minute ritual that keeps a skillet non-stick for decades.'],
                ['type' => 'Behind the range', 'title' => 'A day in the Leeds studio', 'summary' => 'How a knife goes from raw steel to your kitchen drawer.'],
                ['type' => 'Material story', 'title' => 'Where our oak comes from', 'summary' => 'The Yorkshire timber yard behind every handle and board.'],
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
                ['metric' => '4.9 / 5', 'name' => 'Average product rating', 'summary' => 'Across 6,400 verified reviews from people who use the range daily.', 'rating' => '4.9', 'reviewCount' => '6,400'],
                ['metric' => '60 days', 'name' => 'Free returns', 'summary' => 'Use it, cook with it, send it back if it doesn’t earn its place.'],
                ['metric' => '92%', 'name' => 'Reorder rate', 'summary' => 'Most first-time shoppers come back within the year for a second piece.'],
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
                ['label' => 'Shop collections', 'url' => '#collections', 'style' => 'primary'],
                ['label' => 'Browse the catalogue', 'url' => '#shop', 'style' => 'secondary'],
            ],
            'metrics' => [
                ['value' => '4.9 / 5', 'label' => 'Avg rating'],
                ['value' => '60 days', 'label' => 'Free returns'],
                ['value' => 'Lifetime', 'label' => 'Repairs'],
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
            'items' => [
                ['label' => 'Collections', 'url' => '#collections'],
                ['label' => 'Shop', 'url' => '#shop'],
                ['label' => 'Lookbook', 'url' => '#lookbook'],
                ['label' => 'Guides', 'url' => '#guides'],
                ['label' => 'Help', 'url' => '#help'],
            ],
            'ctaLabel' => 'Shop now',
            'ctaUrl' => '#shop',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        return [
            'brandName' => self::BRAND,
            'summary' => 'A direct-to-consumer homeware label. Designed in Leeds, made to be used hard.',
            'columns' => [
                [
                    'heading' => 'Shop',
                    'links' => [
                        ['label' => 'The Kitchen', 'url' => '#collection-1'],
                        ['label' => 'The Table', 'url' => '#collection-2'],
                        ['label' => 'The Larder', 'url' => '#collection-3'],
                        ['label' => 'The Home', 'url' => '#collection-4'],
                    ],
                ],
                [
                    'heading' => 'Help',
                    'links' => [
                        ['label' => 'Delivery & returns', 'url' => '#help'],
                        ['label' => 'Repairs & care', 'url' => '#guides'],
                        ['label' => 'Track an order', 'url' => '#help'],
                        ['label' => 'Contact us', 'url' => 'mailto:help@marloweandfield.example'],
                    ],
                ],
                [
                    'heading' => 'Studio',
                    'links' => [
                        ['label' => 'Our story', 'url' => '#studio'],
                        ['label' => 'Journal', 'url' => '#guides'],
                        ['label' => 'Pop-ups & events', 'url' => '#events'],
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
