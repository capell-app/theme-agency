<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\FinancialAdvisory\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Financial Advisory theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the
 * page adapter emits the theme's signature renderers (services / advisors /
 * calculators / credentials / client-segments / features / proof) alongside the
 * standard hero/cta — giving every surface a full advisory site rather than the
 * shared five-section skeleton. Copy is mined from the screenshot renderer and
 * extended with item-level cards every section blade reads via its
 * `items`/`title`/`summary` keys.
 */
final class FinancialAdvisoryDemoContent implements ProvidesThemeDemoContent
{
    private const string BRAND = 'Meridian Wealth Advisory';

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
            title: self::BRAND . ' — Wealth, Tax & Financial Planning',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'Trusted advice for every financial decision',
                'Meridian Wealth Advisory pairs independent advisers with planning calculators, transparent credentials, and client-led journeys for every stage of life.',
            ),
            renderData: [
                'summary' => 'A professional advisory homepage for services, advisers, planning calculators, credentials, and client-led journeys.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Financial Advisory',
                        'heading' => 'Trusted advice for every financial decision',
                        'summary' => 'A professional advisory homepage for services, advisers, planning calculators, credentials, and client-led journeys.',
                        'actions' => [
                            ['label' => 'Book a consultation', 'url' => '#consultation', 'style' => 'primary'],
                            ['label' => 'Explore services', 'url' => '#services', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['hero'][0] ?? null,
                        'mediaAlt' => 'Meridian Wealth Advisory consultation',
                    ],
                    $this->servicesSection(
                        heading: 'Advisory services built to be compared',
                        summary: 'Editorial service groupings keep wealth, tax, and planning offerings legible and premium.',
                    ),
                    $this->advisorsSection(
                        heading: 'Meet the advisory team',
                        summary: 'Adviser cards pair specialisms with credentials so prospects can self-select the right expert.',
                    ),
                    $this->calculatorsSection(
                        heading: 'Planning calculators that build engagement',
                        summary: 'Non-submitting calculator surfaces let prospects explore the numbers before they ever book a call.',
                    ),
                    $this->credentialsSection(),
                    $this->clientSegmentsSection(),
                    $this->featuresSection(),
                    $this->proofSection(
                        heading: 'Proof from two decades of advice',
                        summary: 'The outcomes clients return for, year after year.',
                    ),
                    $this->ctaSection(
                        heading: 'Book an advisory consultation',
                        summary: 'Start with a no-obligation conversation. We reply to every enquiry within one working day.',
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
            name: self::BRAND . ' Advisers',
            title: 'Advisory team directory — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'Browse the advisory team directory',
                'A scannable directory of advisers keeps every specialist discoverable without the theme owning people records.',
            ),
            renderData: [
                'summary' => 'A scannable directory of advisers keeps every specialist discoverable across wealth, tax, and planning.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Advisory team',
                        'heading' => 'Browse the advisory team directory',
                        'summary' => 'A scannable directory of advisers keeps every specialist discoverable without the theme owning people records.',
                        'actions' => [
                            ['label' => 'Book a consultation', 'url' => '#consultation', 'style' => 'primary'],
                        ],
                        'mediaUrl' => $media['listing'][0] ?? $media['hero'][0] ?? null,
                        'mediaAlt' => 'Meridian advisory team',
                    ],
                    $this->advisorsSection(
                        heading: 'Browse the advisory team directory',
                        summary: 'A scannable directory of advisers keeps every specialist discoverable without the theme owning people records.',
                    ),
                    $this->clientSegmentsSection(),
                    $this->credentialsSection(),
                    $this->ctaSection(
                        heading: 'Not sure which adviser fits?',
                        summary: 'Tell us your goal and we will match you to the right specialist within one working day.',
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
            name: self::BRAND . ' Adviser Profile',
            title: 'Adviser profile — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'A single adviser profile that builds trust',
                'Credentials, specialisms, and proof sit together so a prospect can evaluate one adviser in depth.',
            ),
            renderData: [
                'summary' => 'Credentials, specialisms, and proof sit together so a prospect can evaluate one adviser in depth.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Adviser profile',
                        'heading' => 'A single adviser profile that builds trust',
                        'summary' => 'Credentials, specialisms, and proof sit together so a prospect can evaluate one adviser in depth.',
                        'actions' => [
                            ['label' => 'Book with this adviser', 'url' => '#consultation', 'style' => 'primary'],
                            ['label' => 'Back to the team', 'url' => '#advisors', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['detail'][0] ?? null,
                        'mediaAlt' => 'Meridian adviser portrait',
                    ],
                    $this->advisorsSection(
                        heading: 'A single adviser profile that builds trust',
                        summary: 'Credentials, specialisms, and proof sit together so a prospect can evaluate one adviser in depth.',
                    ),
                    $this->credentialsSection(),
                    $this->proofSection(
                        heading: 'What clients of this adviser say',
                        summary: 'Outcomes from recent engagements across retirement, tax, and estate planning.',
                    ),
                    $this->ctaSection(
                        heading: 'Book an advisory consultation',
                        summary: 'Speak directly with the adviser whose specialism matches your goal.',
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
            title: 'Start a confident conversation — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Start a confident conversation',
                'A non-submitting contact path proves the enquiry journey feels like part of the advisory experience.',
            ),
            renderData: [
                'summary' => 'A non-submitting contact path proves the enquiry journey feels like part of the advisory experience.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Contact',
                        'heading' => 'Start a confident conversation',
                        'summary' => 'A non-submitting contact path proves the enquiry journey feels like part of the advisory experience. Email hello@meridianwealth.example or use the details below — we reply within one working day.',
                        'actions' => [
                            ['label' => 'Email the practice', 'url' => 'mailto:hello@meridianwealth.example', 'style' => 'primary'],
                            ['label' => 'Explore services', 'url' => '#services', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['contact'][0] ?? null,
                        'mediaAlt' => 'Meridian Wealth Advisory office',
                    ],
                    $this->clientSegmentsSection(),
                    $this->servicesSection(
                        heading: 'How we can help',
                        summary: 'Pick the conversation that fits where you are today.',
                    ),
                    $this->ctaSection(
                        heading: 'Book an advisory consultation',
                        summary: 'Choose a time that works and meet the adviser best suited to your goal.',
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
            name: self::BRAND . ' No Insights',
            title: 'No insights published yet — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'No insights published yet',
                'The empty state stays premium and on-brand while the firm builds out its insights library.',
            ),
            renderData: [
                'summary' => 'The empty state stays premium and on-brand while the firm builds out its insights library.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Insights',
                        'heading' => 'No insights published yet',
                        'summary' => 'The empty state stays premium and on-brand while the firm builds out its insights library.',
                        'actions' => [
                            ['label' => 'Explore services', 'url' => '#services', 'style' => 'primary'],
                            ['label' => 'Book a consultation', 'url' => '#consultation', 'style' => 'secondary'],
                        ],
                    ],
                    [
                        'type' => 'content-listing',
                        'heading' => 'No insights published yet',
                        'summary' => 'When the practice publishes guides and market notes they will appear here, newest first.',
                        'items' => [],
                    ],
                    $this->servicesSection(
                        heading: 'While you are here',
                        summary: 'The advisory services the practice is best known for.',
                    ),
                    $this->ctaSection(
                        heading: 'Book an advisory consultation',
                        summary: 'Prefer to talk it through now? Start a no-obligation conversation today.',
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
            title: 'We could not find that page — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-404',
            content: $this->prose(
                'We could not find that page',
                'A branded 404 keeps lost visitors oriented and routes them back to advisory journeys.',
            ),
            renderData: [
                'summary' => 'A branded 404 keeps lost visitors oriented and routes them back to advisory journeys.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => '404',
                        'heading' => 'We could not find that page',
                        'summary' => 'A branded 404 keeps lost visitors oriented and routes them back to advisory journeys.',
                        'actions' => [
                            ['label' => 'Back to home', 'url' => '/', 'style' => 'primary'],
                            ['label' => 'Explore services', 'url' => '#services', 'style' => 'secondary'],
                        ],
                    ],
                    $this->ctaSection(
                        heading: 'Book an advisory consultation',
                        summary: 'Tell us what you were looking for and we will point you to the right adviser.',
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
            name: self::BRAND . ' Book',
            title: 'Book an advisory consultation — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Book an advisory consultation',
                'A focused conversion CTA proves the booking path feels premium without owning scheduling records.',
            ),
            renderData: [
                'summary' => 'A focused conversion CTA proves the booking path feels premium without owning scheduling records.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Book a consultation',
                        'heading' => 'Book an advisory consultation',
                        'summary' => 'A focused conversion CTA proves the booking path feels premium without owning scheduling records.',
                        'actions' => [
                            ['label' => 'Book a consultation', 'url' => '#consultation', 'style' => 'primary'],
                            ['label' => 'Email the practice', 'url' => 'mailto:hello@meridianwealth.example', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['cta'][0] ?? null,
                        'mediaAlt' => 'Meridian Wealth Advisory consultation',
                    ],
                    $this->proofSection(
                        heading: 'Why clients choose Meridian',
                        summary: 'The numbers behind two decades of independent advice.',
                    ),
                    $this->ctaSection(
                        heading: 'One conversation away',
                        summary: 'Send your enquiry and we will come back within one working day with next steps.',
                    ),
                ],
            ],
        );
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
                [
                    'title' => 'Wealth management',
                    'summary' => 'Discretionary and advisory portfolios built around your risk profile, rebalanced as markets and goals move.',
                ],
                [
                    'title' => 'Retirement planning',
                    'summary' => 'Pension consolidation, drawdown modelling, and income strategies that make your savings last.',
                ],
                [
                    'title' => 'Tax & estate planning',
                    'summary' => 'Allowance-efficient structures, trusts, and inheritance planning coordinated with your accountant and solicitor.',
                ],
                [
                    'title' => 'Business & exit advice',
                    'summary' => 'Succession, share schemes, and exit planning for founders preparing to sell or step back.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function advisorsSection(string $heading, string $summary): array
    {
        return [
            'type' => 'advisors',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                [
                    'title' => 'Eleanor Hartley, CFP',
                    'summary' => 'Senior wealth adviser. Twenty years building retirement and investment plans for professionals and families.',
                ],
                [
                    'title' => 'Marcus Bellamy, CTA',
                    'summary' => 'Chartered tax adviser. Specialises in estate planning, trusts, and inheritance-efficient structures.',
                ],
                [
                    'title' => 'Priya Anand, FPFS',
                    'summary' => 'Chartered financial planner focused on pensions, drawdown, and long-term income strategy.',
                ],
                [
                    'title' => 'James Okonkwo, ACA',
                    'summary' => 'Business adviser covering succession, share schemes, and exit planning for founders.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function calculatorsSection(string $heading, string $summary): array
    {
        return [
            'type' => 'calculators',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                [
                    'title' => 'Retirement income planner',
                    'summary' => 'Estimate the sustainable income your pension pot could support across different drawdown rates.',
                ],
                [
                    'title' => 'Pension contribution modeller',
                    'summary' => 'See how regular contributions and tax relief compound toward your target retirement fund.',
                ],
                [
                    'title' => 'Inheritance tax estimator',
                    'summary' => 'Gauge a potential estate liability and the headroom your allowances leave before planning.',
                ],
                [
                    'title' => 'Investment growth projector',
                    'summary' => 'Project a portfolio across cautious, balanced, and adventurous return assumptions.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function credentialsSection(): array
    {
        return [
            'type' => 'credentials',
            'heading' => 'Credentials you can verify',
            'summary' => 'Regulated, chartered, and independent — the trust signals advisory clients look for first.',
            'items' => [
                [
                    'title' => 'FCA regulated',
                    'summary' => 'Authorised and regulated by the Financial Conduct Authority, with permissions you can check on the register.',
                ],
                [
                    'title' => 'Chartered firm status',
                    'summary' => 'Corporate Chartered Financial Planners — the profession\'s gold standard for ethics and competence.',
                ],
                [
                    'title' => 'Independent, whole-of-market',
                    'summary' => 'No product ties and no in-house funds. Recommendations come from the whole market, on your side.',
                ],
                [
                    'title' => 'Transparent, fixed fees',
                    'summary' => 'Clear fees agreed before any work begins. No commission, no surprises on the invoice.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function clientSegmentsSection(): array
    {
        return [
            'type' => 'client-segments',
            'heading' => 'Advice shaped to where you are',
            'summary' => 'Distinct journeys for the clients the practice serves most.',
            'items' => [
                [
                    'title' => 'Approaching retirement',
                    'summary' => 'Turn a working life of saving into a dependable, tax-efficient income you can plan around.',
                ],
                [
                    'title' => 'Established professionals',
                    'summary' => 'Coordinate pensions, investments, and protection while your earnings and responsibilities peak.',
                ],
                [
                    'title' => 'Business owners',
                    'summary' => 'Extract value efficiently, plan succession, and prepare for a confident exit on your terms.',
                ],
                [
                    'title' => 'Families & estates',
                    'summary' => 'Protect what you have built and pass it on with inheritance and estate planning that holds up.',
                ],
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
            'heading' => 'How working with Meridian feels',
            'summary' => 'The standards behind every engagement, from first call to annual review.',
            'items' => [
                [
                    'title' => 'A named adviser',
                    'summary' => 'You work with one specialist who knows your plan — never a rotating call-centre queue.',
                ],
                [
                    'title' => 'Annual review, on the calendar',
                    'summary' => 'Plans drift. We meet every year to rebalance, revisit goals, and adjust for life changes.',
                ],
                [
                    'title' => 'Plain-English reporting',
                    'summary' => 'Clear statements and projections you can actually read, with jargon translated up front.',
                ],
                [
                    'title' => 'Secure client portal',
                    'summary' => 'Documents, valuations, and your plan in one place, available whenever you need them.',
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
                    'title' => '£420m advised',
                    'summary' => 'Client assets under advice across pensions, investments, and protection portfolios.',
                ],
                [
                    'title' => '20+ years',
                    'summary' => 'Independent, chartered advice through every market cycle since the practice was founded.',
                ],
                [
                    'title' => '98% retention',
                    'summary' => 'The share of advised clients who stay with Meridian year after year.',
                ],
                [
                    'title' => '1 working day',
                    'summary' => 'Every enquiry hears back from a real adviser, fast — not an automated queue.',
                ],
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
                ['label' => 'Explore services', 'url' => '#services', 'style' => 'secondary'],
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
                ['label' => 'Advisors', 'url' => '#advisors'],
                ['label' => 'Calculators', 'url' => '#calculators'],
                ['label' => 'Insights', 'url' => '#insights'],
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
            'summary' => 'Independent, chartered financial advice for wealth, retirement, tax, and estate planning.',
            'columns' => [
                [
                    'heading' => 'Advice',
                    'links' => [
                        ['label' => 'Wealth management', 'url' => '#services'],
                        ['label' => 'Retirement planning', 'url' => '#services'],
                        ['label' => 'Tax & estate planning', 'url' => '#services'],
                        ['label' => 'Business & exit advice', 'url' => '#services'],
                    ],
                ],
                [
                    'heading' => 'Practice',
                    'links' => [
                        ['label' => 'Our advisers', 'url' => '#advisors'],
                        ['label' => 'Credentials', 'url' => '#credentials'],
                        ['label' => 'Calculators', 'url' => '#calculators'],
                        ['label' => 'Insights', 'url' => '#insights'],
                    ],
                ],
                [
                    'heading' => 'Connect',
                    'links' => [
                        ['label' => 'Book a consultation', 'url' => '#consultation'],
                        ['label' => 'hello@meridianwealth.example', 'url' => 'mailto:hello@meridianwealth.example'],
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
