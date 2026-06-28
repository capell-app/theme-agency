<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\LawFirm\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Law Firm theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the
 * page adapter emits the theme's signature legal renderers (practice-areas /
 * attorneys / case-results / credentials / consultation-cta) alongside the
 * standard hero/proof/cta — giving every surface a full, individual practice
 * site rather than the shared five-section skeleton.
 */
final class LawFirmDemoContent implements ProvidesThemeDemoContent
{
    private const BRAND = 'Whitlock & Hart';

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
            title: self::BRAND . ' — Trial & Advisory Counsel',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'A trial and advisory practice',
                'Whitlock & Hart is a litigation and advisory firm representing businesses and individuals in high-stakes disputes across commercial, employment, and personal injury law.',
            ),
            renderData: [
                'summary' => 'Whitlock & Hart is a trial and advisory firm. We represent businesses and individuals in commercial, employment, and personal injury matters that demand a steady hand and a sharp argument.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Trial & advisory counsel',
                        'heading' => 'Counsel a practice can stand behind',
                        'summary' => 'For more than thirty years, Whitlock & Hart has guided clients through litigation, regulatory pressure, and the moments where the right argument changes everything. Senior partners lead every matter we accept.',
                        'actions' => [
                            ['label' => 'Book a consultation', 'url' => '#consultation', 'style' => 'primary'],
                            ['label' => 'View practice areas', 'url' => '#practice-areas', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['hero'][0],
                        'mediaAlt' => 'The Whitlock & Hart offices',
                    ],
                    $this->practiceAreasSection(
                        heading: 'Practice areas',
                        summary: 'Focused groups of attorneys who do this work every day. Most engagements draw on more than one.',
                    ),
                    $this->attorneysSection(
                        heading: 'Counsel who try cases',
                        summary: 'A senior bench of trial lawyers and advisors. You work directly with the partner on your matter.',
                    ),
                    $this->caseResultsSection(
                        heading: 'Representative results',
                        summary: 'Outcomes from recent matters. Past results do not guarantee a similar outcome in your case.',
                    ),
                    $this->credentialsSection(
                        heading: 'Recognition & admissions',
                        summary: 'Where the firm is admitted, ranked, and recognised for the work.',
                    ),
                    $this->consultationSection(
                        heading: 'Book a confidential consultation',
                        summary: 'Tell us about your matter in confidence. A partner reviews every enquiry and responds within one business day.',
                    ),
                    $this->proofSection(
                        heading: 'Why clients instruct the firm',
                        summary: 'The record behind three decades of practice.',
                    ),
                    $this->ctaSection(
                        heading: 'Facing a dispute or a deadline?',
                        summary: 'Speak with a partner before the next move. Every consultation is privileged and confidential.',
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
            name: self::BRAND . ' Attorneys',
            title: 'Our attorneys — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'Meet the counsel behind the practice',
                'A directory of the partners and associates who lead matters at Whitlock & Hart across litigation and advisory work.',
            ),
            renderData: [
                'summary' => 'A directory of the partners and associates who lead matters across litigation, employment, and advisory practice.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Our attorneys',
                        'heading' => 'Meet the counsel behind the practice',
                        'summary' => 'Browse the firm by name or practice group. Every attorney listed here leads matters personally — there is no layer of intermediaries between you and your counsel.',
                        'actions' => [
                            ['label' => 'Book a consultation', 'url' => '#consultation', 'style' => 'primary'],
                        ],
                        'mediaUrl' => $media['listing'][0] ?? $media['hero'][0],
                        'mediaAlt' => 'Attorneys of Whitlock & Hart',
                    ],
                    $this->attorneysSection(
                        heading: 'Partners & senior counsel',
                        summary: 'The attorneys who set strategy and stand up in court.',
                    ),
                    $this->contentListingSection(
                        heading: 'Associates & of counsel',
                        summary: 'The wider bench that supports every matter the firm accepts.',
                    ),
                    $this->ctaSection(
                        heading: 'Not sure who you need?',
                        summary: 'Describe your matter and we will route you to the right partner for a confidential first conversation.',
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
            name: self::BRAND . ' Attorney Profile',
            title: 'Eleanor Whitlock — Partner — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'Eleanor Whitlock — Founding Partner',
                'A profile of founding partner Eleanor Whitlock, a trial lawyer with three decades of commercial litigation and appellate experience.',
            ),
            renderData: [
                'summary' => 'Founding partner Eleanor Whitlock has tried commercial and appellate matters for three decades, with a record built on preparation and clear argument.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Founding partner',
                        'heading' => 'Eleanor Whitlock',
                        'summary' => 'Lead trial counsel in commercial and appellate litigation. Thirty years before judges and juries, with a practice built on relentless preparation and arguments that hold.',
                        'actions' => [
                            ['label' => 'Request a consultation', 'url' => '#consultation', 'style' => 'primary'],
                            ['label' => 'View all attorneys', 'url' => '#attorneys', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['detail'][0],
                        'mediaAlt' => 'Portrait of Eleanor Whitlock',
                    ],
                    $this->attorneyProfileSection(
                        heading: 'About Eleanor',
                        summary: 'Practice focus, courtroom record, and the matters she leads personally.',
                    ),
                    $this->credentialsSection(
                        heading: 'Admissions & recognition',
                        summary: 'Bar admissions, appellate clerkships, and the rankings that follow the work.',
                    ),
                    $this->caseResultsSection(
                        heading: 'Selected matters',
                        summary: 'Representative results from Eleanor\'s docket. Past results do not guarantee a similar outcome.',
                    ),
                    $this->ctaSection(
                        heading: 'Want Eleanor on your matter?',
                        summary: 'Partner availability is limited and matters are accepted selectively. Request a confidential consultation to begin.',
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
            name: self::BRAND . ' Consultation',
            title: 'Book a consultation — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Book a confidential consultation',
                'Reach Whitlock & Hart to discuss your matter in confidence. A partner reviews every enquiry personally.',
            ),
            renderData: [
                'summary' => 'Reach the firm to discuss your matter in confidence. Offices in the financial district; consultations by appointment, in person or by secure video.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Consultation',
                        'heading' => 'Reach the firm through one confident path',
                        'summary' => 'Call the practice on (212) 555-0184, email counsel@whitlockhart.example, or request an appointment below. Every first conversation is privileged and without obligation.',
                        'actions' => [
                            ['label' => 'Email the firm', 'url' => 'mailto:counsel@whitlockhart.example', 'style' => 'primary'],
                            ['label' => 'View practice areas', 'url' => '#practice-areas', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['contact'][0],
                        'mediaAlt' => 'The Whitlock & Hart reception',
                    ],
                    $this->consultationSection(
                        heading: 'Book a consultation in one confident path',
                        summary: 'Tell us the nature of your matter and your preferred time. A partner confirms within one business day.',
                    ),
                    $this->officeDetailsSection(
                        heading: 'Office & hours',
                        summary: 'Where to find us and how to reach the right team quickly.',
                    ),
                    $this->ctaSection(
                        heading: 'Prefer to speak first?',
                        summary: 'Request a fifteen-minute intake call and we will tell you plainly whether we are the right firm for your matter.',
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
            title: 'No matters found — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'Nothing published here yet',
                'A composed empty state for a filtered insights archive with no matching articles.',
            ),
            renderData: [
                'summary' => 'No insights match that filter yet — but the firm can still point you toward the right counsel.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Insights archive',
                        'heading' => 'No articles match that filter — yet',
                        'summary' => 'We have not published commentary in this area. Clear the filter to read everything, or speak with a partner directly about your question.',
                        'actions' => [
                            ['label' => 'View all insights', 'url' => '#insights', 'style' => 'primary'],
                            ['label' => 'Book a consultation', 'url' => '#consultation', 'style' => 'secondary'],
                        ],
                    ],
                    [
                        'type' => 'content-listing',
                        'heading' => 'Nothing to show here',
                        'summary' => 'When the firm publishes in this practice area, articles will appear here, newest first.',
                        'items' => [],
                    ],
                    $this->practiceAreasSection(
                        heading: 'While you are here',
                        summary: 'The matters the firm is most often instructed on.',
                    ),
                    $this->ctaSection(
                        heading: 'Looking for guidance on something specific?',
                        summary: 'Describe your question and we will point you to the partner best placed to answer it.',
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
                'That page could not be found',
                'A not-found page that routes visitors back into the firm\'s practice and consultation paths.',
            ),
            renderData: [
                'summary' => 'That page has moved or never existed — here is the way back into the practice.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => '404',
                        'heading' => 'That page could not be found',
                        'summary' => 'The link is broken or the page has moved. Return to the practice, review our attorneys, or reach a partner directly.',
                        'actions' => [
                            ['label' => 'Back to home', 'url' => '/', 'style' => 'primary'],
                            ['label' => 'View practice areas', 'url' => '#practice-areas', 'style' => 'secondary'],
                        ],
                    ],
                    $this->ctaSection(
                        heading: 'Still need to reach the firm?',
                        summary: 'Tell us what you were looking for and we will direct you to the right counsel.',
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
            name: self::BRAND . ' Engage',
            title: 'Instruct the firm — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Turn intent into a booked consultation',
                'A focused conversion page inviting prospective clients to instruct the firm.',
            ),
            renderData: [
                'summary' => 'Ready to instruct counsel? Book a confidential consultation with a partner at Whitlock & Hart.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Instruct the firm',
                        'heading' => 'Turn intent into a booked consultation',
                        'summary' => 'Whether you are facing litigation, a regulator, or a deal that cannot slip, the firm brings the same senior bench and the same standard to every matter.',
                        'actions' => [
                            ['label' => 'Book a consultation', 'url' => '#consultation', 'style' => 'primary'],
                            ['label' => 'Email the firm', 'url' => 'mailto:counsel@whitlockhart.example', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['cta'][0],
                        'mediaAlt' => 'The Whitlock & Hart practice',
                    ],
                    $this->consultationSection(
                        heading: 'A direct path to counsel',
                        summary: 'No intake mazes. Tell us about your matter and a partner reviews it personally.',
                    ),
                    $this->proofSection(
                        heading: 'Why clients instruct the firm',
                        summary: 'The record behind three decades of practice.',
                    ),
                    $this->ctaSection(
                        heading: 'Begin a confidential consultation',
                        summary: 'Send the details over and a partner will respond within one business day with next steps.',
                    ),
                ],
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function practiceAreasSection(string $heading, string $summary): array
    {
        return [
            'type' => 'practice-areas',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Commercial litigation', 'summary' => 'Contract disputes, partnership breakups, and bet-the-company cases tried before judges and juries.'],
                ['title' => 'Employment law', 'summary' => 'Discrimination, wrongful termination, and executive separation matters for employers and senior individuals.'],
                ['title' => 'Personal injury', 'summary' => 'Serious-injury and wrongful-death claims pursued against insurers and corporate defendants.'],
                ['title' => 'Appellate practice', 'summary' => 'Briefing and argument in state and federal appellate courts, including matters tried by other counsel.'],
                ['title' => 'Regulatory & investigations', 'summary' => 'Responding to agency inquiries, internal investigations, and enforcement actions with discretion.'],
                ['title' => 'Estate & fiduciary disputes', 'summary' => 'Will contests, trust litigation, and fiduciary accountings handled with sensitivity and rigour.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function attorneysSection(string $heading, string $summary): array
    {
        return [
            'type' => 'attorneys',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Eleanor Whitlock', 'name' => 'Eleanor Whitlock', 'summary' => 'Founding partner. Trial and appellate counsel in commercial litigation. Thirty years before the bench.'],
                ['title' => 'Marcus Hart', 'name' => 'Marcus Hart', 'summary' => 'Founding partner. Leads the firm\'s employment and executive-separation practice for employers and individuals.'],
                ['title' => 'Priya Anand', 'name' => 'Priya Anand', 'summary' => 'Partner. Personal-injury and wrongful-death trial lawyer with a record of seven-figure recoveries.'],
                ['title' => 'David Okonkwo', 'name' => 'David Okonkwo', 'summary' => 'Partner. Regulatory defence and internal investigations, formerly with the enforcement division of a state agency.'],
                ['title' => 'Sarah Lindqvist', 'name' => 'Sarah Lindqvist', 'summary' => 'Of counsel. Appellate specialist and former federal appellate clerk leading the firm\'s briefing.'],
                ['title' => 'James Reyes', 'name' => 'James Reyes', 'summary' => 'Senior associate. Commercial and estate-dispute litigation, with a focus on complex fiduciary accountings.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function attorneyProfileSection(string $heading, string $summary): array
    {
        return [
            'type' => 'attorneys',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Practice focus', 'summary' => 'Commercial and appellate litigation, with an emphasis on contract and partnership disputes that reach trial.'],
                ['title' => 'Courtroom record', 'summary' => 'First-chair trial counsel in more than forty matters across state and federal courts over three decades.'],
                ['title' => 'How Eleanor works', 'summary' => 'She accepts a limited docket so every matter gets a partner\'s full attention from intake through judgment.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function caseResultsSection(string $heading, string $summary): array
    {
        return [
            'type' => 'case-results',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => '$24M jury verdict', 'summary' => 'Breach-of-contract verdict for a manufacturer after a six-week commercial trial.'],
                ['title' => 'Defence verdict', 'summary' => 'Complete defence verdict for an employer in a high-profile discrimination suit.'],
                ['title' => '$7.5M settlement', 'summary' => 'Pre-trial settlement in a catastrophic-injury claim against a national carrier.'],
                ['title' => 'Appeal reversed', 'summary' => 'Reversal on appeal of an adverse judgment, securing a retrial for the client.'],
                ['title' => 'Investigation closed', 'summary' => 'Regulatory inquiry resolved with no charges following an internal investigation.'],
                ['title' => 'Injunction granted', 'summary' => 'Emergency injunction protecting a client\'s trade secrets within seventy-two hours of filing.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function credentialsSection(string $heading, string $summary): array
    {
        return [
            'type' => 'credentials',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Bar admissions', 'summary' => 'Admitted before state and federal trial and appellate courts, including the Court of Appeals.'],
                ['title' => 'Peer recognition', 'summary' => 'Ranked in leading legal directories for commercial litigation and employment law.'],
                ['title' => 'AV-rated', 'summary' => 'The firm holds the highest peer rating for legal ability and ethical standards.'],
                ['title' => 'Bar leadership', 'summary' => 'Partners serve on litigation and professional-responsibility committees of the state bar.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function consultationSection(string $heading, string $summary): array
    {
        return [
            'type' => 'consultation-cta',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Confidential & privileged', 'summary' => 'Every first conversation is protected and carries no obligation to instruct the firm.'],
                ['title' => 'Reviewed by a partner', 'summary' => 'A partner reads each enquiry personally — your matter is never triaged by software.'],
                ['title' => 'One business day', 'summary' => 'We respond to every consultation request within one business day, often sooner.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function officeDetailsSection(string $heading, string $summary): array
    {
        return [
            'type' => 'features',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Office', 'summary' => 'One Harborview Plaza, 28th Floor, in the financial district. Visitor parking adjoins the lobby.'],
                ['title' => 'Hours', 'summary' => 'Monday to Friday, 8:30am to 6:00pm. Urgent matters are covered outside hours by arrangement.'],
                ['title' => 'Reach us', 'summary' => 'Call (212) 555-0184 or email counsel@whitlockhart.example. Secure video consultations available.'],
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
                ['metric' => '30+ yrs', 'name' => 'In practice', 'quote' => 'Three decades representing clients through their most consequential disputes.'],
                ['metric' => '95%', 'name' => 'Matters resolved favourably', 'quote' => 'Most matters end in a verdict or settlement our clients can live with.'],
                ['metric' => '1 day', 'name' => 'Reply to every enquiry', 'quote' => 'A partner responds to every consultation request within one business day.'],
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
                ['title' => 'Anika Patel — Associate', 'summary' => 'Commercial litigation and discovery management for complex multi-party matters.'],
                ['title' => 'Thomas Greer — Associate', 'summary' => 'Employment and regulatory work, with a focus on agency response and compliance.'],
                ['title' => 'Renee Beaumont — Of counsel', 'summary' => 'Mediation and alternative dispute resolution across the firm\'s practice areas.'],
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
                ['label' => 'Book a consultation', 'url' => '#consultation', 'style' => 'primary'],
                ['label' => 'View practice areas', 'url' => '#practice-areas', 'style' => 'secondary'],
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
                ['label' => 'Practice areas', 'url' => '#practice-areas'],
                ['label' => 'Attorneys', 'url' => '#attorneys'],
                ['label' => 'Case results', 'url' => '#case-results'],
                ['label' => 'Insights', 'url' => '#insights'],
                ['label' => 'Consultation', 'url' => '#consultation'],
            ],
            'ctaLabel' => 'Book a consultation',
            'ctaUrl' => '#consultation',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        return [
            'brandName' => self::BRAND,
            'summary' => 'A trial and advisory firm. One Harborview Plaza, financial district. Consultations by appointment.',
            'columns' => [
                [
                    'heading' => 'Practice',
                    'links' => [
                        ['label' => 'Commercial litigation', 'url' => '#practice-areas'],
                        ['label' => 'Employment law', 'url' => '#practice-areas'],
                        ['label' => 'Personal injury', 'url' => '#practice-areas'],
                        ['label' => 'Appellate practice', 'url' => '#practice-areas'],
                    ],
                ],
                [
                    'heading' => 'Firm',
                    'links' => [
                        ['label' => 'Attorneys', 'url' => '#attorneys'],
                        ['label' => 'Case results', 'url' => '#case-results'],
                        ['label' => 'Credentials', 'url' => '#credentials'],
                        ['label' => 'Insights', 'url' => '#insights'],
                    ],
                ],
                [
                    'heading' => 'Contact',
                    'links' => [
                        ['label' => 'Book a consultation', 'url' => '#consultation'],
                        ['label' => 'counsel@whitlockhart.example', 'url' => 'mailto:counsel@whitlockhart.example'],
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
