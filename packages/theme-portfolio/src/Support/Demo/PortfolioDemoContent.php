<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\Portfolio\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Portfolio theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the
 * page adapter emits the theme's signature renderers (work-grid / case-studies
 * / case-study-detail / services / process / speaking-media-kit / testimonials)
 * alongside the standard hero/proof/cta — giving every surface a full creator
 * portfolio site rather than the shared five-section skeleton.
 */
final class PortfolioDemoContent implements ProvidesThemeDemoContent
{
    private const BRAND = 'Marlow Studio';

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
            title: self::BRAND . ' — Selected Work, Proven by Outcomes',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'Selected work, proven by outcomes',
                'Marlow Studio is an independent creator and consultant practice turning selected work into outcome-driven case studies.',
            ),
            renderData: [
                'summary' => 'A work-led portfolio for creators and consultants — case studies, services, media kit, and audience growth from one polished site.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Portfolio',
                        'heading' => 'Selected work, proven by outcomes',
                        'summary' => 'A work-led portfolio for creators and consultants — case studies, services, media kit, and audience growth from one polished site. Lead with proof, not just pretty pictures.',
                        'actions' => [
                            ['label' => 'View selected work', 'url' => '#work', 'style' => 'primary'],
                            ['label' => 'Start an enquiry', 'url' => '#enquire', 'style' => 'secondary'],
                        ],
                        'imageUrl' => $media['hero'][0],
                        'imageAlt' => 'Marlow Studio selected work',
                    ],
                    $this->workGridSection(
                        heading: 'Selected work built to be scanned',
                        summary: 'Dense work cards keep role, scope, and outcome legible at a glance.',
                    ),
                    $this->caseStudiesSection(
                        heading: 'Case studies that prove measurable value',
                        summary: 'Each engagement pairs challenge, approach, and result so the outcome is impossible to miss.',
                    ),
                    $this->servicesSection(
                        heading: 'Studio capabilities you can actually buy',
                        summary: 'Engagement-shaped service cards make the offer legible — pick the shape that fits the brief.',
                    ),
                    $this->proofSection(
                        heading: 'Proof, in numbers',
                        summary: 'Outcomes from recent creator and consultant engagements.',
                    ),
                    $this->testimonialsSection(
                        summary: 'What founders and editors say after working with the studio.',
                    ),
                    $this->speakingMediaKitSection(
                        label: 'Media kit',
                        summary: 'Speaking, press, and audience reach for partners and event teams.',
                    ),
                    $this->ctaSection(
                        heading: 'Have a project in mind?',
                        summary: 'Tell us what you are building. We reply to every enquiry within one working day.',
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
            name: self::BRAND . ' Work',
            title: 'Selected Work — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'Selected work & case studies',
                'A directory of creator and consultant projects across brand, editorial, and product work.',
            ),
            renderData: [
                'summary' => 'Browse the studio archive of selected work and outcome-driven case studies.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Selected work',
                        'heading' => 'Selected work built to be scanned',
                        'summary' => 'Dense work cards keep role, scope, and outcome legible without the theme becoming a blog, gallery, or services grid.',
                        'actions' => [
                            ['label' => 'Start an enquiry', 'url' => '#enquire', 'style' => 'primary'],
                        ],
                        'imageUrl' => $media['listing'][0] ?? $media['hero'][0],
                        'imageAlt' => 'Studio project work',
                    ],
                    $this->workGridSection(
                        heading: 'Featured projects',
                        summary: 'The work the studio keeps coming back to.',
                    ),
                    $this->contentListingSection(
                        heading: 'More from the archive',
                        summary: 'Smaller engagements, collaborations, and experiments.',
                        media: $media,
                    ),
                    $this->ctaSection(
                        heading: 'See a project that fits your brief?',
                        summary: 'Tell us what you are planning and we will send the most relevant work, with context on scope and timing.',
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
            name: self::BRAND . ' Case Study',
            title: 'Northbeam — Case Study — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'Northbeam — a launch that paid for itself',
                'How Marlow Studio shaped the Northbeam brand story and launch campaign ahead of a sold-out first cohort.',
            ),
            renderData: [
                'summary' => 'An outcome ledger pairing challenge, approach, and result for the Northbeam launch engagement.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Case study',
                        'heading' => 'Northbeam — a launch that paid for itself',
                        'summary' => 'A solo founder with a strong idea and no narrative. We built the story, the proof, and the launch that filled the first cohort in nine days.',
                        'actions' => [
                            ['label' => 'View all work', 'url' => '#work', 'style' => 'secondary'],
                        ],
                        'imageUrl' => $media['detail'][0],
                        'imageAlt' => 'Northbeam launch case study',
                    ],
                    $this->caseStudyDetailSection(
                        heading: 'A case study that proves measurable value',
                        summary: 'An outcome ledger pairs challenge, approach, and result so the studio proves premium worth through work rather than broad claims.',
                    ),
                    $this->caseStudiesSection(
                        heading: 'Related engagements',
                        summary: 'Other projects in the same neighbourhood.',
                    ),
                    $this->proofSection(
                        heading: 'The numbers behind it',
                        summary: 'What the Northbeam launch returned in its first quarter.',
                    ),
                    $this->ctaSection(
                        heading: 'Want results like these?',
                        summary: 'Most engagements start with a short paid discovery. Tell us about the work and we will map a path.',
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
            name: self::BRAND . ' Enquire',
            title: 'Start an Enquiry — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Start an enquiry',
                'Tell us what you are planning. We scope creator and consultant work directly with the people who do it.',
            ),
            renderData: [
                'summary' => 'Tell us what you are planning. Every project is scoped directly with the studio — no account managers in between.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Enquire',
                        'heading' => 'Start an enquiry',
                        'summary' => 'Working with creators, founders, and editorial teams worldwide. Email hello@marlow.example or use the form below — we reply within one working day.',
                        'actions' => [
                            ['label' => 'Email the studio', 'url' => 'mailto:hello@marlow.example', 'style' => 'primary'],
                            ['label' => 'View selected work', 'url' => '#work', 'style' => 'secondary'],
                        ],
                        'imageUrl' => $media['contact'][0],
                        'imageAlt' => 'The Marlow Studio desk',
                    ],
                    $this->servicesSection(
                        heading: 'How we can help',
                        summary: 'Pick the shape that fits. Most projects start with a short paid discovery.',
                    ),
                    $this->processSection(
                        heading: 'How an engagement runs',
                        summary: 'What to expect once a project starts.',
                    ),
                    $this->ctaSection(
                        heading: 'Prefer to talk it through?',
                        summary: 'Book a 30-minute intro call and we will tell you honestly whether the studio is the right fit.',
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
            title: 'No matching work — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'No work matches that filter yet',
                'A graceful empty state for a filtered work archive with no matching projects.',
            ),
            renderData: [
                'summary' => 'No projects match that filter yet — but the studio can still point you somewhere useful.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Work archive',
                        'heading' => 'No work matches that filter — yet',
                        'summary' => 'We have not published work in this category. Clear the filter to see everything, or tell us what you are looking for.',
                        'actions' => [
                            ['label' => 'View all work', 'url' => '#work', 'style' => 'primary'],
                            ['label' => 'Start an enquiry', 'url' => '#enquire', 'style' => 'secondary'],
                        ],
                    ],
                    [
                        'type' => 'work-grid',
                        'heading' => 'Nothing to show here',
                        'summary' => 'When projects land in this category they will appear here, newest first.',
                        'items' => [],
                    ],
                    $this->servicesSection(
                        heading: 'While you are here',
                        summary: 'The three engagements the studio is best known for.',
                    ),
                    $this->ctaSection(
                        heading: 'Looking for something specific?',
                        summary: 'Tell us the brief and we will send relevant work from the archive.',
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
                'A not-found page that routes visitors back into the studio work and enquiry paths.',
            ),
            renderData: [
                'summary' => 'That page has moved or never existed — here is the way back.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => '404',
                        'heading' => 'This page took a different brief',
                        'summary' => 'The link is broken or the page has moved. Head back to the work, or start a conversation with the studio.',
                        'actions' => [
                            ['label' => 'Back to home', 'url' => '/', 'style' => 'primary'],
                            ['label' => 'View selected work', 'url' => '#work', 'style' => 'secondary'],
                        ],
                    ],
                    $this->workGridSection(
                        heading: 'Try the selected work instead',
                        summary: 'Recent creator and consultant projects, newest first.',
                    ),
                    $this->ctaSection(
                        heading: 'Still looking for something?',
                        summary: 'Tell us what you needed and we will point you to the right place.',
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
            name: self::BRAND . ' Work With Us',
            title: 'Work with the studio — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Ready to turn your work into proof?',
                'A focused conversion page inviting new creator and consultant enquiries.',
            ),
            renderData: [
                'summary' => 'Ready to turn your work into proof? Start an enquiry with the studio.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => "Let's work together",
                        'heading' => 'Ready to turn your work into proof?',
                        'summary' => 'Whether it is a single case study or a full portfolio rebuild, you get the same senior attention and the same standard of proof.',
                        'actions' => [
                            ['label' => 'Start an enquiry', 'url' => '#enquire', 'style' => 'primary'],
                            ['label' => 'Email the studio', 'url' => 'mailto:hello@marlow.example', 'style' => 'secondary'],
                        ],
                        'imageUrl' => $media['cta'][0],
                        'imageAlt' => 'Marlow Studio',
                    ],
                    $this->proofSection(
                        heading: 'Why creators choose the studio',
                        summary: 'The numbers behind the work.',
                    ),
                    $this->testimonialsSection(
                        summary: 'Recent clients on what changed after we worked together.',
                    ),
                    $this->ctaSection(
                        heading: 'One brief away',
                        summary: 'Send the project over and we will come back within one working day with next steps.',
                    ),
                ],
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function workGridSection(string $heading, string $summary): array
    {
        return [
            'type' => 'work-grid',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['type' => 'Brand + Web', 'title' => 'Northbeam', 'description' => 'Brand story and launch campaign for a solo-founder cohort course that sold out in nine days.'],
                ['type' => 'Editorial', 'title' => 'Field Notes', 'description' => 'A serialised long-form essay series that grew a consultant newsletter from 800 to 9,000 readers.'],
                ['type' => 'Product', 'title' => 'Lantern OS', 'description' => 'Positioning, messaging, and a marketing site for an indie productivity app heading into launch.'],
                ['type' => 'Identity', 'title' => 'Harlow & Reed', 'description' => 'Naming and visual identity for a two-person legal consultancy spinning out on their own.'],
                ['type' => 'Campaign', 'title' => 'Tideline', 'description' => 'A launch film and social toolkit that took a coastal photography book to a second print run.'],
                ['type' => 'Web', 'title' => 'Studio Caldera', 'description' => 'An editorial portfolio site for a ceramicist that lets the work carry the page.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function caseStudiesSection(string $heading, string $summary): array
    {
        return [
            'type' => 'case-studies',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                [
                    'type' => 'Brand + Web',
                    'title' => 'Northbeam',
                    'metric' => 'Cohort sold out in 9 days',
                    'description' => 'A launch story and campaign that turned a quiet idea into a waitlist, then a full first cohort.',
                    'scope' => 'Brand story, launch page, email sequence',
                    'role' => 'Strategy & copy lead',
                    'timeline' => '6 weeks',
                ],
                [
                    'type' => 'Editorial',
                    'title' => 'Field Notes',
                    'metric' => 'Newsletter 800 → 9,000',
                    'description' => 'A serialised essay series that gave a consultant a voice and an audience that compounds.',
                    'scope' => 'Editorial strategy, ghostwriting, growth',
                    'role' => 'Editorial partner',
                    'timeline' => '4 months',
                ],
                [
                    'type' => 'Product',
                    'title' => 'Lantern OS',
                    'metric' => '+38% trial-to-paid',
                    'description' => 'Sharper positioning and a marketing site that finally explained the product in one scroll.',
                    'scope' => 'Positioning, messaging, site copy',
                    'role' => 'Messaging lead',
                    'timeline' => '5 weeks',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function caseStudyDetailSection(string $heading, string $summary): array
    {
        return [
            'type' => 'case-study-detail',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['type' => 'Challenge', 'title' => 'A strong idea with no story', 'summary' => 'Northbeam had a sharp curriculum and a founder who knew the space cold — but the landing page read like a syllabus and converted like one.'],
                ['type' => 'Approach', 'title' => 'Build the narrative, then the proof', 'summary' => 'Six weeks: a week of founder interviews to find the real promise, a story-led launch page, and a five-email sequence that earned the sale instead of demanding it.'],
                ['type' => 'Result', 'title' => 'A waitlist that became a cohort', 'summary' => 'The first cohort sold out in nine days, the launch covered a year of the studio retainer, and the founder kept the page as the evergreen front door.'],
                ['type' => 'Role', 'title' => 'Strategy and copy lead', 'summary' => 'Direct work with the founder throughout — no hand-offs, no account layer between the brief and the words on the page.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function servicesSection(string $heading, string $summary): array
    {
        return [
            'type' => 'services',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['type' => 'Engagement', 'title' => 'Case study sprint', 'description' => 'One outcome-driven case study, fully written and designed, from interview to publish in two weeks.'],
                ['type' => 'Engagement', 'title' => 'Portfolio rebuild', 'description' => 'A full work-led portfolio — selected work, case studies, services, and media kit — shipped on this theme.'],
                ['type' => 'Retainer', 'title' => 'Editorial partner', 'description' => 'Ongoing ghostwriting and audience growth for consultants who want to publish without it eating their week.'],
                ['type' => 'Engagement', 'title' => 'Launch campaign', 'description' => 'Story, landing page, and email sequence for a product, course, or book launch with a real date attached.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function processSection(string $heading, string $summary): array
    {
        return [
            'type' => 'process',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['type' => 'Step 01', 'title' => 'Discovery', 'summary' => 'A short paid discovery: interviews, audit, and a written plan you keep whether or not we go further.'],
                ['type' => 'Step 02', 'title' => 'Draft', 'summary' => 'A first full draft of the work — words, structure, and proof — for an honest round of feedback.'],
                ['type' => 'Step 03', 'title' => 'Build', 'summary' => 'We shape the draft into the live page or campaign, designed and built on the portfolio theme.'],
                ['type' => 'Step 04', 'title' => 'Launch', 'summary' => 'We ship, measure, and hand you a page you can own and update without coming back to us.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function speakingMediaKitSection(string $label, string $summary): array
    {
        return [
            'type' => 'speaking-media-kit',
            'label' => $label,
            'summary' => $summary,
            'items' => [
                ['title' => 'Keynotes & talks', 'description' => 'Talks on building an audience as a consultant — delivered at SaaS, design, and founder events across Europe.'],
                ['title' => 'Press & features', 'description' => 'Work and commentary featured in industry newsletters and two trade publications over the last year.'],
                ['title' => 'Audience', 'description' => '9,000 newsletter readers and a 22,000-strong following, concentrated in product and consulting circles.'],
                ['title' => 'Booking', 'description' => 'Available for one speaking engagement a month. Media kit, headshots, and bio supplied on request.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function testimonialsSection(string $summary): array
    {
        return [
            'type' => 'testimonials',
            'summary' => $summary,
            'items' => [
                ['quote' => 'The case study did more for our pipeline than six months of cold outreach. People quote it back to us on sales calls.', 'name' => 'Dana Whitfield', 'attribution' => 'Founder, Northbeam'],
                ['quote' => 'I finally sound like myself in print. The newsletter went from a chore to the best lead source I have.', 'name' => 'Marcus Ardent', 'attribution' => 'Independent consultant'],
                ['quote' => 'They rewrote our homepage and trial-to-paid jumped within a fortnight. No new features — just clarity.', 'name' => 'Priya Sood', 'attribution' => 'Co-founder, Lantern OS'],
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
                ['metric' => '40+', 'name' => 'Case studies shipped', 'quote' => 'For creators, consultants, and indie product teams since 2019.'],
                ['metric' => '2 wks', 'name' => 'Typical case study sprint', 'quote' => 'From founder interview to a published, designed outcome story.'],
                ['metric' => '1 day', 'name' => 'Reply to every enquiry', 'quote' => 'You hear back from the person who would do the work, fast.'],
            ],
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function contentListingSection(string $heading, string $summary, array $media): array
    {
        $images = array_values(array_unique(array_merge($media['listing'], $media['proof'])));

        $entries = [
            ['title' => 'Harlow & Reed — identity', 'type' => 'Identity', 'summary' => 'Naming and a visual identity for a two-person legal consultancy going independent.'],
            ['title' => 'Tideline — launch film', 'type' => 'Campaign', 'summary' => 'A launch film and social toolkit that pushed a photography book to a second print run.'],
            ['title' => 'Studio Caldera — portfolio', 'type' => 'Web', 'summary' => 'An editorial portfolio site for a ceramicist, built to let the work carry the page.'],
        ];

        $items = [];

        foreach ($entries as $index => $entry) {
            $image = $images[$index % max(count($images), 1)] ?? null;
            $items[] = [
                ...$entry,
                'url' => '#archive-' . ($index + 1),
                'image' => $image,
                'imageUrl' => $image,
                'imageAlt' => $entry['title'],
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
    private function ctaSection(string $heading, string $summary): array
    {
        return [
            'type' => 'cta',
            'heading' => $heading,
            'summary' => $summary,
            'actions' => [
                ['label' => 'Start an enquiry', 'url' => '#enquire', 'style' => 'primary'],
                ['label' => 'View selected work', 'url' => '#work', 'style' => 'secondary'],
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
                ['label' => 'Work', 'url' => '#work'],
                ['label' => 'Case studies', 'url' => '#case-studies'],
                ['label' => 'Services', 'url' => '#services'],
                ['label' => 'Media kit', 'url' => '#media-kit'],
                ['label' => 'Enquire', 'url' => '#enquire'],
            ],
            'ctaLabel' => 'Start an enquiry',
            'ctaUrl' => '#enquire',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        return [
            'brandName' => self::BRAND,
            'summary' => 'An independent creator and consultant studio. Selected work, proven by outcomes.',
            'columns' => [
                [
                    'heading' => 'Studio',
                    'links' => [
                        ['label' => 'About', 'url' => '#about'],
                        ['label' => 'Services', 'url' => '#services'],
                        ['label' => 'Process', 'url' => '#process'],
                        ['label' => 'Media kit', 'url' => '#media-kit'],
                    ],
                ],
                [
                    'heading' => 'Work',
                    'links' => [
                        ['label' => 'Selected work', 'url' => '#work'],
                        ['label' => 'Case studies', 'url' => '#case-studies'],
                        ['label' => 'Newsletter', 'url' => '#newsletter'],
                    ],
                ],
                [
                    'heading' => 'Connect',
                    'links' => [
                        ['label' => 'Enquire', 'url' => '#enquire'],
                        ['label' => 'hello@marlow.example', 'url' => 'mailto:hello@marlow.example'],
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
