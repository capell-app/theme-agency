<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\QuantTrading\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Quant Trading theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the page
 * adapter emits the theme's signature renderers (performance-chart / metric-cards /
 * strategy-cards / track-record-table / risk-disclosure) alongside the standard
 * hero/proof/cta — giving every surface a full systematic-trading firm site rather
 * than the shared five-section skeleton. Performance figures are framed as
 * illustrative and paired with a plain-language risk disclosure throughout.
 */
final class QuantTradingDemoContent implements ProvidesThemeDemoContent
{
    private const BRAND = 'Halevy Capital';

    private const RISK_NOTE = 'Figures shown are illustrative and based on backtested or representative results, net of modelled fees. They are not a promise of future performance. Systematic trading carries the risk of substantial loss; you should only allocate capital you can afford to lose.';

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
            title: self::BRAND . ' — Systematic Trading Firm',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'A systematic trading firm',
                'Halevy Capital runs rules-based, diversified strategies across global futures and equities — built on research, disciplined risk limits, and transparent reporting.',
            ),
            renderData: [
                'summary' => 'Halevy Capital is a systematic trading firm running diversified, rules-based strategies across global markets, with risk limits and reporting allocators can actually read.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Systematic trading firm',
                        'heading' => 'Strategies a book can stand behind',
                        'summary' => 'We trade rules, not hunches. Diversified, fully systematic programs across futures and equities, governed by hard risk limits and reported with the kind of transparency allocators expect.',
                        'actions' => [
                            ['label' => 'View performance', 'url' => '#performance', 'style' => 'primary'],
                            ['label' => 'Explore strategies', 'url' => '#strategies', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['hero'][0],
                        'mediaAlt' => 'Halevy Capital research desk',
                    ],
                    $this->performanceChartSection(),
                    $this->metricCardsSection(),
                    $this->strategyCardsSection(
                        heading: 'Three programs, one risk framework',
                        summary: 'Each program is independently researched and capacity-managed, then combined to keep correlation low across the book.',
                    ),
                    $this->trackRecordTableSection(),
                    $this->featuresSection(
                        heading: 'How the firm operates',
                        summary: 'The infrastructure and discipline behind every position.',
                    ),
                    $this->riskDisclosureSection(),
                    $this->proofSection(
                        heading: 'What allocators value',
                        summary: 'The reasons capital stays with the firm.',
                    ),
                    $this->ctaSection(
                        heading: 'Ready to evaluate the book?',
                        summary: 'Request the full tear sheet and risk documentation. We respond to every allocator enquiry within one business day.',
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
            name: self::BRAND . ' Strategies',
            title: 'Strategies — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'The systematic strategy book',
                'A directory of the firm\'s live and research-stage programs across trend, carry, and statistical-arbitrage mandates.',
            ),
            renderData: [
                'summary' => 'The firm\'s systematic strategy book — live and research-stage programs across trend, carry, and statistical arbitrage, each with its own capacity and risk budget.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Strategy book',
                        'heading' => 'Every program, built to be scanned',
                        'summary' => 'Structured strategy cards keep the book legible: mandate, markets traded, capacity, and the edge each program is designed to harvest.',
                        'actions' => [
                            ['label' => 'Request the tear sheet', 'url' => '#contact', 'style' => 'primary'],
                        ],
                        'mediaUrl' => $media['listing'][0] ?? $media['hero'][0],
                        'mediaAlt' => 'Systematic strategy overview',
                    ],
                    $this->strategyCardsSection(
                        heading: 'Live programs',
                        summary: 'Capital is allocated across these mandates today, sized by capacity and combined to keep correlation low.',
                    ),
                    $this->contentListingSection(
                        heading: 'Research-stage and capacity-limited',
                        summary: 'Programs in incubation or closed to new capital, listed for transparency.',
                    ),
                    $this->riskDisclosureSection(),
                    $this->ctaSection(
                        heading: 'Found a mandate that fits?',
                        summary: 'Tell us the size and objective of your allocation and we will send the matching program documentation.',
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
            name: self::BRAND . ' Strategy Profile',
            title: 'Tessellate Trend — Strategy Profile — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'Tessellate Trend — the firm\'s flagship program',
                'A diversified medium-term trend program trading 80+ liquid futures markets, with full performance, track record, and risk detail.',
            ),
            renderData: [
                'summary' => 'Tessellate Trend is the firm\'s flagship program: a diversified, medium-term trend-following system trading more than 80 liquid global futures markets.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Strategy profile',
                        'heading' => 'Tessellate Trend',
                        'summary' => 'Medium-term, fully systematic trend following across more than 80 liquid futures markets — diversified by asset class, geography, and holding period. Targeting 12% annualised volatility.',
                        'actions' => [
                            ['label' => 'View all strategies', 'url' => '#strategies', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['detail'][0],
                        'mediaAlt' => 'Tessellate Trend performance detail',
                    ],
                    $this->strategyProfileSection(),
                    $this->performanceChartSection(),
                    $this->trackRecordTableSection(),
                    $this->riskDisclosureSection(),
                    $this->ctaSection(
                        heading: 'Evaluate Tessellate Trend',
                        summary: 'Request the full program tear sheet, including monthly returns, drawdown history, and the latest risk report.',
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
            title: 'Contact the desk — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Contact the investor relations desk',
                'One direct path to the firm. We work with institutional allocators, family offices, and qualified investors.',
            ),
            renderData: [
                'summary' => 'One direct path to the desk. We work with institutional allocators, family offices, and qualified investors — and reply to every enquiry within one business day.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Investor relations',
                        'heading' => 'Reach the desk directly',
                        'summary' => 'Offices in London and Singapore, trading global sessions. Email ir@halevycapital.example or use the details below — a partner reads every allocator enquiry.',
                        'actions' => [
                            ['label' => 'Email the desk', 'url' => 'mailto:ir@halevycapital.example', 'style' => 'primary'],
                            ['label' => 'View strategies', 'url' => '#strategies', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['contact'][0],
                        'mediaAlt' => 'Halevy Capital investor relations desk',
                    ],
                    $this->featuresSection(
                        heading: 'How an allocation begins',
                        summary: 'The path from first enquiry to funded mandate.',
                    ),
                    $this->proofSection(
                        heading: 'Who we work with',
                        summary: 'The allocators the firm is built to serve.',
                    ),
                    $this->riskDisclosureSection(),
                    $this->ctaSection(
                        heading: 'Prefer a call?',
                        summary: 'Book a 30-minute introduction with a partner and we will walk through the programs, the risk framework, and current capacity.',
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
            title: 'Nothing published yet — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'Nothing published here yet',
                'A precise empty state for a filtered strategy view with no matching programs.',
            ),
            renderData: [
                'summary' => 'No programs match that filter yet — the research desk will publish here as mandates go live.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Strategy book',
                        'heading' => 'No programs match that filter — yet',
                        'summary' => 'Nothing is published under this mandate or market filter. Clear the filter to see the full book, or tell the desk what you are looking for.',
                        'actions' => [
                            ['label' => 'View all strategies', 'url' => '#strategies', 'style' => 'primary'],
                            ['label' => 'Contact the desk', 'url' => '#contact', 'style' => 'secondary'],
                        ],
                    ],
                    [
                        'type' => 'content-listing',
                        'heading' => 'Nothing to show under this filter',
                        'summary' => 'When a program matching these criteria goes live, it will appear here with its mandate and capacity.',
                        'items' => [],
                    ],
                    $this->strategyCardsSection(
                        heading: 'While you are here',
                        summary: 'The three live programs the firm is best known for.',
                    ),
                    $this->riskDisclosureSection(),
                    $this->ctaSection(
                        heading: 'Looking for a specific mandate?',
                        summary: 'Tell the desk the objective and size of your allocation and we will point you to the right program.',
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
                'A 404 page that routes visitors back into the strategy book and the investor relations desk.',
            ),
            renderData: [
                'summary' => 'That page has moved or never existed — here is the way back into the firm.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => '404',
                        'heading' => 'That position could not be found',
                        'summary' => 'The link is broken or the page has moved. Head back to the strategy book, or reach the investor relations desk directly.',
                        'actions' => [
                            ['label' => 'Back to home', 'url' => '/', 'style' => 'primary'],
                            ['label' => 'View strategies', 'url' => '#strategies', 'style' => 'secondary'],
                        ],
                    ],
                    $this->ctaSection(
                        heading: 'Still looking for something?',
                        summary: 'Tell the desk what you needed and we will send the right documentation.',
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
            name: self::BRAND . ' Allocate',
            title: 'Start an allocation conversation — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Turn interest into an allocation conversation',
                'A focused conversion page inviting allocators to request documentation and engage the desk.',
            ),
            renderData: [
                'summary' => 'Turn interest into an allocation conversation. Request the tear sheet and risk documentation, then talk to a partner.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Engage the desk',
                        'heading' => 'Turn interest into an allocation conversation',
                        'summary' => 'Whether you are running due diligence or sizing a first ticket, you get the same documentation, the same risk transparency, and a direct line to a partner.',
                        'actions' => [
                            ['label' => 'Request the tear sheet', 'url' => '#contact', 'style' => 'primary'],
                            ['label' => 'Email the desk', 'url' => 'mailto:ir@halevycapital.example', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['cta'][0],
                        'mediaAlt' => 'Halevy Capital trading floor',
                    ],
                    $this->proofSection(
                        heading: 'Why allocators choose the firm',
                        summary: 'The track record behind the conversation.',
                    ),
                    $this->riskDisclosureSection(),
                    $this->ctaSection(
                        heading: 'One enquiry away',
                        summary: 'Send your mandate over and a partner will respond within one business day with the next step in diligence.',
                    ),
                ],
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function performanceChartSection(): array
    {
        return [
            'type' => 'performance-chart',
            'heading' => 'Performance, shown with transparency',
            'summary' => 'Illustrative composite equity curve for the firm, net of modelled fees. ' . self::RISK_NOTE,
            'items' => [
                ['title' => 'Annualised return', 'summary' => '14.2% net composite since inception (illustrative, 2016–2025).'],
                ['title' => 'Annualised volatility', 'summary' => '11.8% — managed to a 12% target across the combined book.'],
                ['title' => 'Sharpe ratio', 'summary' => '0.92 composite, computed on monthly returns net of fees.'],
                ['title' => 'Max drawdown', 'summary' => '-16.4% peak-to-trough, recovered within 11 months.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function metricCardsSection(): array
    {
        return [
            'type' => 'metric-cards',
            'heading' => 'The book at a glance',
            'summary' => 'Headline figures across the combined program. All figures illustrative and net of modelled fees.',
            'items' => [
                ['title' => '$1.4bn', 'summary' => 'Assets traded across the firm\'s programs.'],
                ['title' => '80+', 'summary' => 'Liquid global markets traded systematically.'],
                ['title' => '0.92', 'summary' => 'Composite Sharpe ratio since inception.'],
                ['title' => '24/5', 'summary' => 'Continuous risk monitoring across global sessions.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function strategyCardsSection(string $heading, string $summary): array
    {
        return [
            'type' => 'strategy-cards',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                [
                    'title' => 'Tessellate Trend',
                    'summary' => 'Medium-term trend following across 80+ futures markets, diversified by asset class and holding period. Targets 12% volatility. Capacity: open.',
                ],
                [
                    'title' => 'Aldgate Carry',
                    'summary' => 'A cross-asset carry program harvesting risk premia in rates, FX, and commodities, with systematic crash hedges. Targets 8% volatility. Capacity: limited.',
                ],
                [
                    'title' => 'Mercer Stat-Arb',
                    'summary' => 'Market-neutral statistical arbitrage in liquid global equities, held short term with strict gross and net exposure limits. Targets 9% volatility. Capacity: closed.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function strategyProfileSection(): array
    {
        return [
            'type' => 'strategy-cards',
            'heading' => 'Inside Tessellate Trend',
            'summary' => 'The mandate, markets, and risk budget that define the flagship program.',
            'items' => [
                ['title' => 'Mandate', 'summary' => 'Medium-term, fully systematic trend following. No discretionary overrides on signals.'],
                ['title' => 'Markets', 'summary' => 'More than 80 liquid futures across equities, rates, FX, energy, metals, and agriculture.'],
                ['title' => 'Holding period', 'summary' => 'Weeks to months, with position sizing scaled inversely to realised volatility.'],
                ['title' => 'Risk budget', 'summary' => 'Targets 12% annualised volatility with hard per-market and portfolio stop limits.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function trackRecordTableSection(): array
    {
        return [
            'type' => 'track-record-table',
            'heading' => 'Track record by year',
            'summary' => 'Illustrative composite net returns by calendar year. Past performance is not indicative of future results.',
            'items' => [
                ['title' => '2025', 'summary' => '+11.6% net — led by sustained trends in rates and energy.'],
                ['title' => '2024', 'summary' => '+9.1% net — positive across all three programs.'],
                ['title' => '2023', 'summary' => '-4.2% net — a choppy, mean-reverting year for trend.'],
                ['title' => '2022', 'summary' => '+28.7% net — the strongest year, driven by macro dislocation.'],
                ['title' => '2021', 'summary' => '+6.4% net — modest, with carry carrying the book.'],
                ['title' => '2020', 'summary' => '+18.3% net — March drawdown recovered by year end.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function riskDisclosureSection(): array
    {
        return [
            'type' => 'risk-disclosure',
            'heading' => 'Risk, stated plainly',
            'summary' => self::RISK_NOTE,
            'items' => [
                ['title' => 'Capital at risk', 'summary' => 'Systematic trading can and does lose money. Drawdowns of 15% or more are an expected feature, not a failure of the system.'],
                ['title' => 'Illustrative figures', 'summary' => 'Performance shown is backtested or representative, net of modelled fees, and is not an offer or a guarantee of future returns.'],
                ['title' => 'Hard limits', 'summary' => 'Every program runs to pre-set per-market and portfolio risk limits. The desk does not override the system to chase losses.'],
                ['title' => 'Eligibility', 'summary' => 'Programs are offered only to qualified and professional investors. Nothing here is investment advice.'],
            ],
        ];
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
            'items' => [
                ['title' => 'Research first', 'summary' => 'Every signal is backtested across decades of data and out-of-sample windows before a dollar trades it.'],
                ['title' => 'Independent risk', 'summary' => 'A separate risk function monitors exposure 24/5 and can de-risk the book without sign-off from the trading desk.'],
                ['title' => 'Institutional infrastructure', 'summary' => 'Third-party administration, daily reconciliation, and tier-one prime brokerage on every program.'],
                ['title' => 'Transparent reporting', 'summary' => 'Monthly tear sheets with returns, drawdowns, and exposure — written to be read, not buried.'],
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
                ['metric' => '9 yrs', 'name' => 'Live track record', 'quote' => 'Systematic programs traded continuously since 2016 across multiple regimes.'],
                ['metric' => '$1.4bn', 'name' => 'Assets traded', 'quote' => 'Institutional and family-office capital across the combined book.'],
                ['metric' => '1 day', 'name' => 'Reply to every allocator', 'quote' => 'A partner reads and responds to every enquiry within one business day.'],
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
            'variant' => 'list',
            'items' => [
                ['title' => 'Lowell Vol Premium', 'type' => 'Research stage', 'summary' => 'A systematic volatility-premium program in equity index options, in out-of-sample testing.', 'url' => '#strategy-lowell'],
                ['title' => 'Brent Curve', 'type' => 'Capacity limited', 'summary' => 'A relative-value energy curve program, closed to new capital at current size.', 'url' => '#strategy-brent'],
                ['title' => 'Ridgeway Macro', 'type' => 'Incubation', 'summary' => 'A slower macro overlay combining trend and carry signals, paper-traded since 2024.', 'url' => '#strategy-ridgeway'],
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
                ['label' => 'Request the tear sheet', 'url' => '#contact', 'style' => 'primary'],
                ['label' => 'View strategies', 'url' => '#strategies', 'style' => 'secondary'],
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
                ['label' => 'Strategies', 'url' => '#strategies'],
                ['label' => 'Performance', 'url' => '#performance'],
                ['label' => 'Track record', 'url' => '#track-record'],
                ['label' => 'Risk', 'url' => '#risk'],
                ['label' => 'Contact', 'url' => '#contact'],
            ],
            'ctaLabel' => 'Request the tear sheet',
            'ctaUrl' => '#contact',
            'consultationUrl' => '#contact',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        return [
            'brandName' => self::BRAND,
            'summary' => 'A systematic trading firm. London and Singapore, trading global markets.',
            'columns' => [
                [
                    'heading' => 'Firm',
                    'links' => [
                        ['label' => 'About', 'url' => '#firm'],
                        ['label' => 'Research', 'url' => '#research'],
                        ['label' => 'Risk framework', 'url' => '#risk'],
                        ['label' => 'Careers', 'url' => '#firm'],
                    ],
                ],
                [
                    'heading' => 'Strategies',
                    'links' => [
                        ['label' => 'Tessellate Trend', 'url' => '#strategies'],
                        ['label' => 'Aldgate Carry', 'url' => '#strategies'],
                        ['label' => 'Mercer Stat-Arb', 'url' => '#strategies'],
                        ['label' => 'Track record', 'url' => '#track-record'],
                    ],
                ],
                [
                    'heading' => 'Investor relations',
                    'links' => [
                        ['label' => 'Contact the desk', 'url' => '#contact'],
                        ['label' => 'ir@halevycapital.example', 'url' => 'mailto:ir@halevycapital.example'],
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
