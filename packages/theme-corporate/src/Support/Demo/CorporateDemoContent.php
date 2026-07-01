<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\Corporate\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Corporate theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the
 * page adapter emits the theme's signature renderers (features / investor-relations
 * / careers / locations) alongside the standard hero/proof/content-listing/cta —
 * giving every surface a full, individual corporate site rather than the shared
 * five-section skeleton. The voice is a professional-services firm: structured,
 * restrained, trust-led, accountable.
 */
final class CorporateDemoContent implements ProvidesThemeDemoContent
{
    private const string BRAND = 'Harwell & Crane';

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
            title: self::BRAND . ' — Corporate advisory, assurance & delivery',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'A corporate practice stakeholders can stand behind',
                'Harwell & Crane is an independent advisory, assurance, and delivery firm for boards and leadership teams in regulated sectors.',
            ),
            renderData: [
                'summary' => 'Harwell & Crane is an independent advisory, assurance, and delivery firm. We help boards and leadership teams govern with confidence.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Corporate practice',
                        'heading' => 'Govern with confidence, deliver with discipline',
                        'summary' => 'We partner with boards and executive teams on strategy, oversight, and complex delivery — work judged by the decisions it makes possible, not the slides it produces.',
                        'actions' => [
                            ['label' => 'Contact the team', 'url' => '#contact', 'style' => 'primary'],
                            ['label' => 'View services', 'url' => '#services', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['hero'][0],
                        'mediaAlt' => 'Harwell & Crane advisory practice',
                        'stats' => [
                            ['label' => 'Mandates since 2009', 'value' => '320+'],
                            ['label' => 'Board engagements a year', 'value' => '40'],
                            ['label' => 'Reply to every enquiry', 'value' => '1 day'],
                        ],
                    ],
                    $this->featuresSection(
                        heading: 'An operating model built for accountability',
                        summary: 'Three practices that connect policy, risk, and delivery so leadership can see how a decision becomes an outcome.',
                    ),
                    $this->investorRelationsSection(
                        heading: 'Investor material published with restraint',
                        summary: 'Results, calendar, and disclosures kept structured and current for the people who hold us to account.',
                    ),
                    $this->contentListingSection(
                        heading: 'Resources written for considered readers',
                        summary: 'Briefings and reports that keep governance, risk, and delivery thinking legible.',
                        media: $media,
                    ),
                    $this->careersSection(
                        heading: 'Careers within a disciplined practice',
                        summary: 'Roles for people who value rigour, judgement, and accountability over noise.',
                    ),
                    $this->proofSection(
                        heading: 'Outcomes stakeholders can stand behind',
                        summary: 'A sample of measured results from recent advisory, assurance, and delivery mandates.',
                    ),
                    $this->ctaSection(
                        heading: 'Begin a considered conversation',
                        summary: 'Tell us what your board is weighing. We route every enquiry to the partner best placed to help and reply within one working day.',
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
            name: self::BRAND . ' Services',
            title: 'Services — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'Services that read as boardroom-ready',
                'Advisory, assurance, and delivery mandates structured around the questions boards actually have to answer.',
            ),
            renderData: [
                'summary' => 'Advisory, assurance, and delivery mandates structured around the questions boards actually have to answer.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Services',
                        'heading' => 'Mandates structured around real board questions',
                        'summary' => 'Browse the practice. Each engagement is scoped to a decision, with clear governance and a named partner accountable for the outcome.',
                        'actions' => [
                            ['label' => 'Contact the team', 'url' => '#contact', 'style' => 'primary'],
                        ],
                        'mediaUrl' => $media['listing'][0] ?? $media['hero'][0],
                        'mediaAlt' => 'Corporate advisory services',
                    ],
                    $this->featuresSection(
                        heading: 'Three practices, one chain of accountability',
                        summary: 'Most engagements move across advisory, assurance, and delivery as a programme matures.',
                    ),
                    $this->contentListingSection(
                        heading: 'Recent mandates and briefings',
                        summary: 'How the practice shows up across regulated sectors.',
                        media: $media,
                    ),
                    $this->proofSection(
                        heading: 'Outcomes that earned the next mandate',
                        summary: 'Results clients were willing to put their name to.',
                    ),
                    $this->ctaSection(
                        heading: 'See a service that fits your remit?',
                        summary: 'Tell us the decision in front of your board and we will send the most relevant work, with scope and timing.',
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
            name: self::BRAND . ' Governance',
            title: 'Meridian Holdings — Governance review — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'Meridian Holdings — restoring board oversight',
                'How a structured governance review rebuilt oversight across three divisions and lifted return on capital.',
            ),
            renderData: [
                'summary' => 'How a structured governance review rebuilt oversight across three divisions and lifted return on capital.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Governance review',
                        'heading' => 'Meridian Holdings — oversight, rebuilt',
                        'summary' => 'A diversified group whose board had lost its rhythm after two acquisitions. We restructured oversight so decisions had an owner and a paper trail again.',
                        'actions' => [
                            ['label' => 'View all services', 'url' => '#services', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['detail'][0],
                        'mediaAlt' => 'Meridian Holdings governance review',
                    ],
                    $this->investorRelationsSection(
                        heading: 'What the board signed off',
                        summary: 'The governance calendar and disclosure framework the review put in place.',
                    ),
                    $this->proofSection(
                        heading: 'Results the board could defend',
                        summary: 'Measured against the mandate the directors set at the outset.',
                    ),
                    $this->contentListingSection(
                        heading: 'Related governance work',
                        summary: 'Other mandates in the same neighbourhood.',
                        media: $media,
                    ),
                    $this->ctaSection(
                        heading: 'Facing a similar oversight gap?',
                        summary: 'Most reviews open with a short, paid scoping. Tell us where the board feels exposed and we will map a path.',
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
            title: 'Contact the practice — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Route your enquiry to the right partner',
                'Reach the advisory, assurance, or delivery team directly. We reply to every enquiry within one working day.',
            ),
            renderData: [
                'summary' => 'Reach the advisory, assurance, or delivery team directly. Every enquiry is answered by a partner within one working day.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Contact',
                        'heading' => 'Route your enquiry to the right partner',
                        'summary' => 'Offices in London and Manchester, working with boards across the UK and Europe. Email enquiries@harwellcrane.example or use the routes below.',
                        'actions' => [
                            ['label' => 'Email the practice', 'url' => 'mailto:enquiries@harwellcrane.example', 'style' => 'primary'],
                            ['label' => 'View services', 'url' => '#services', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['contact'][0],
                        'mediaAlt' => 'Harwell & Crane offices',
                    ],
                    $this->locationsSection(
                        heading: 'Offices and contact routes',
                        summary: 'Reach the right team at the right location.',
                    ),
                    $this->featuresSection(
                        heading: 'How we can help',
                        summary: 'Pick the practice that fits the question. Most engagements open with a short, paid scoping.',
                    ),
                    $this->ctaSection(
                        heading: 'Prefer to talk it through first?',
                        summary: 'Request a 30-minute introductory call and we will tell you honestly whether this is a mandate we are the right firm for.',
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
            title: 'No matching material — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'No material matches that filter yet',
                'A composed empty state for a filtered resource library with no matching briefings or reports.',
            ),
            renderData: [
                'summary' => 'No briefings or reports match that filter yet — but the practice can still point you somewhere useful.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Resource library',
                        'heading' => 'No material matches that filter — yet',
                        'summary' => 'We have not published material in this category. Clear the filter to see everything, or tell us what your board needs and we will point you to it.',
                        'actions' => [
                            ['label' => 'View all resources', 'url' => '#resources', 'style' => 'primary'],
                            ['label' => 'Contact the team', 'url' => '#contact', 'style' => 'secondary'],
                        ],
                    ],
                    [
                        'type' => 'content-listing',
                        'heading' => 'Nothing published in this category',
                        'summary' => 'When briefings and reports land here they will appear newest first, with the practice and publication date.',
                        'variant' => 'editorial',
                        'items' => [],
                    ],
                    $this->featuresSection(
                        heading: 'While you are here',
                        summary: 'The three practices the firm is best known for.',
                    ),
                    $this->ctaSection(
                        heading: 'Looking for something specific?',
                        summary: 'Tell us the question and we will send the most relevant material from the library.',
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
                'A not-found page that routes visitors back into the firm\'s services and contact paths.',
            ),
            renderData: [
                'summary' => 'That page has moved or never existed — here is the way back into the practice.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => '404',
                        'heading' => 'This page is off the record',
                        'summary' => 'The link is broken or the material has moved. Head back to the services overview, or open a conversation with the practice.',
                        'actions' => [
                            ['label' => 'Back to home', 'url' => '/', 'style' => 'primary'],
                            ['label' => 'View services', 'url' => '#services', 'style' => 'secondary'],
                        ],
                    ],
                    $this->ctaSection(
                        heading: 'Still looking for something?',
                        summary: 'Tell us what you needed and we will route you to the right partner.',
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
            name: self::BRAND . ' Enquire',
            title: 'Work with the practice — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Ready to put a decision on firmer ground?',
                'A focused page inviting boards and leadership teams to open a mandate.',
            ),
            renderData: [
                'summary' => 'Ready to put a board decision on firmer ground? Open a mandate with the practice.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Work with us',
                        'heading' => 'Put your next decision on firmer ground',
                        'summary' => 'Whether it is a full governance review or a single assurance opinion, you get the same partner-led rigour and the same standard of evidence.',
                        'actions' => [
                            ['label' => 'Contact the team', 'url' => '#contact', 'style' => 'primary'],
                            ['label' => 'Email the practice', 'url' => 'mailto:enquiries@harwellcrane.example', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['cta'][0],
                        'mediaAlt' => 'Harwell & Crane practice',
                    ],
                    $this->proofSection(
                        heading: 'Why boards appoint Harwell & Crane',
                        summary: 'The evidence behind the mandate.',
                    ),
                    $this->ctaSection(
                        heading: 'One conversation away',
                        summary: 'Send the brief and we will come back within one working day with a named partner and a path to scope.',
                    ),
                ],
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function featuresSection(string $heading, string $summary): array
    {
        return [
            'type' => 'features',
            'heading' => $heading,
            'summary' => $summary,
            'features' => [
                [
                    'type' => 'Advisory',
                    'title' => 'Corporate strategy and governance',
                    'description' => 'Board-level advisory that keeps strategy, oversight, and reporting aligned as the organisation grows.',
                ],
                [
                    'type' => 'Assurance',
                    'title' => 'Risk and controls assurance',
                    'description' => 'Independent review of controls and risk so leadership can act, and report, with confidence.',
                ],
                [
                    'type' => 'Delivery',
                    'title' => 'Programme delivery oversight',
                    'description' => 'Structured delivery governance for complex, multi-year mandates that have to land on time and on mandate.',
                ],
                [
                    'type' => 'Disclosure',
                    'title' => 'Reporting and disclosure',
                    'description' => 'Annual report, governance, and regulatory disclosure prepared to a standard that holds up under scrutiny.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function investorRelationsSection(string $heading, string $summary): array
    {
        return [
            'type' => 'investor-relations',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                [
                    'period' => 'Q4',
                    'title' => 'Full-year results statement',
                    'summary' => 'Audited results with commentary on capital allocation and the outlook for the year ahead.',
                    'type' => 'Results',
                    'url' => '#governance',
                ],
                [
                    'period' => 'Q3',
                    'title' => 'Interim trading update',
                    'summary' => 'A measured update on performance against the guidance set at the half year.',
                    'type' => 'Update',
                    'url' => '#governance',
                ],
                [
                    'period' => 'AGM',
                    'title' => 'Notice of Annual General Meeting',
                    'summary' => 'Resolutions, voting arrangements, and the board\'s recommendations for shareholders.',
                    'type' => 'Notice',
                    'url' => '#governance',
                ],
            ],
            'events' => [
                ['title' => 'Annual General Meeting', 'date' => '14 May 2026'],
                ['title' => 'Half-year results briefing', 'date' => '3 September 2026'],
                ['title' => 'Capital markets day', 'date' => '19 November 2026'],
            ],
            'documents' => [
                ['title' => 'Annual report 2025', 'url' => '#governance'],
                ['title' => 'Governance framework', 'url' => '#governance'],
                ['title' => 'Remuneration policy', 'url' => '#governance'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function careersSection(string $heading, string $summary): array
    {
        return [
            'type' => 'careers',
            'heading' => $heading,
            'summary' => $summary,
            'benefits' => [
                ['label' => 'Hybrid working'],
                ['label' => 'Chartered support'],
                ['label' => 'Mentored progression'],
                ['label' => 'Profit share'],
            ],
            'items' => [
                [
                    'team' => 'Advisory',
                    'title' => 'Senior governance consultant',
                    'summary' => 'Lead board-level advisory mandates across regulated sectors, owning the relationship with the chair.',
                    'location' => 'London',
                    'type' => 'Full-time',
                    'url' => '#careers',
                ],
                [
                    'team' => 'Assurance',
                    'title' => 'Risk and controls manager',
                    'summary' => 'Own controls-review programmes for major clients and sign the work that goes to their audit committee.',
                    'location' => 'Manchester',
                    'type' => 'Full-time',
                    'url' => '#careers',
                ],
                [
                    'team' => 'Delivery',
                    'title' => 'Programme oversight lead',
                    'summary' => 'Keep multi-year delivery mandates honest against scope, budget, and the decisions the board signed off.',
                    'location' => 'London',
                    'type' => 'Full-time',
                    'url' => '#careers',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function locationsSection(string $heading, string $summary): array
    {
        return [
            'type' => 'locations',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                [
                    'type' => 'Head office',
                    'title' => 'London',
                    'summary' => 'Our registered office and the home of the advisory practice.',
                    'addressLines' => ['18 Cornhill', 'London', 'EC3V 3ND'],
                    'phone' => '+44 20 7946 0100',
                    'email' => 'london@harwellcrane.example',
                    'hours' => ['Monday to Friday', '08:30 to 18:00'],
                ],
                [
                    'type' => 'Regional office',
                    'title' => 'Manchester',
                    'summary' => 'Assurance and delivery teams serving clients across the North.',
                    'addressLines' => ['2 St Peter\'s Square', 'Manchester', 'M2 3AA'],
                    'phone' => '+44 161 200 0100',
                    'email' => 'manchester@harwellcrane.example',
                    'hours' => ['Monday to Friday', '09:00 to 17:30'],
                ],
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
                [
                    'metric' => '+18%',
                    'name' => 'Meridian Holdings',
                    'summary' => 'A governance review that restructured oversight across three divisions and lifted return on capital.',
                    'type' => 'Advisory mandate',
                ],
                [
                    'metric' => '£240m',
                    'name' => 'Ashford Capital',
                    'summary' => 'Delivery oversight that kept a multi-year infrastructure programme on mandate and on budget.',
                    'type' => 'Delivery mandate',
                ],
                [
                    'name' => 'Caldwell Infrastructure',
                    'quote' => 'Harwell & Crane brought clarity to a board that had lost its rhythm.',
                    'summary' => 'A controls programme that rebuilt assurance confidence ahead of a refinancing.',
                    'type' => 'Assurance mandate',
                ],
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
            ['type' => 'Briefing', 'title' => 'Board oversight in volatile markets', 'summary' => 'How effective boards keep oversight disciplined when the ground keeps moving under them.'],
            ['type' => 'Report', 'title' => 'Annual governance outlook 2026', 'summary' => 'The structural shifts reshaping corporate accountability, and what boards should do about them.'],
            ['type' => 'Note', 'title' => 'Assurance that leadership actually relies on', 'summary' => 'Designing a controls review that earns trust instead of just satisfying a checklist.'],
        ];

        $items = [];

        foreach ($entries as $index => $entry) {
            $image = $images[$index % max(count($images), 1)] ?? null;
            $items[] = [
                ...$entry,
                'url' => '#resources',
                'image' => $image,
            ];
        }

        return [
            'type' => 'content-listing',
            'heading' => $heading,
            'summary' => $summary,
            'variant' => 'editorial',
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
                ['label' => 'Contact the team', 'url' => '#contact', 'style' => 'primary'],
                ['label' => 'View services', 'url' => '#services', 'style' => 'secondary'],
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
                ['label' => 'Services', 'url' => '#services'],
                ['label' => 'Governance', 'url' => '#governance'],
                ['label' => 'Resources', 'url' => '#resources'],
                ['label' => 'Careers', 'url' => '#careers'],
                ['label' => 'Contact', 'url' => '#contact'],
            ],
            'ctaLabel' => 'Contact the team',
            'ctaUrl' => '#contact',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        return [
            'brandName' => self::BRAND,
            'summary' => 'An independent advisory, assurance, and delivery firm for boards in regulated sectors. London and Manchester.',
            'columns' => [
                [
                    'heading' => 'Practice',
                    'links' => [
                        ['label' => 'Advisory', 'url' => '#services'],
                        ['label' => 'Assurance', 'url' => '#services'],
                        ['label' => 'Delivery', 'url' => '#services'],
                        ['label' => 'Governance', 'url' => '#governance'],
                    ],
                ],
                [
                    'heading' => 'Company',
                    'links' => [
                        ['label' => 'Resources', 'url' => '#resources'],
                        ['label' => 'Careers', 'url' => '#careers'],
                        ['label' => 'Locations', 'url' => '#contact'],
                    ],
                ],
                [
                    'heading' => 'Contact',
                    'links' => [
                        ['label' => 'Enquiries', 'url' => '#contact'],
                        ['label' => 'enquiries@harwellcrane.example', 'url' => 'mailto:enquiries@harwellcrane.example'],
                        ['label' => 'Investor relations', 'url' => '#governance'],
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
