<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\AeoAnalytics\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the AEO Analytics theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the
 * page adapter emits the theme's signature renderers (dashboard-preview /
 * metric-cards / coverage-map / integrations-grid / report-gallery) alongside
 * the standard hero/proof/features/cta — giving every surface a full, individual
 * AI-search-visibility analytics product site rather than a five-section
 * skeleton.
 */
final class AeoAnalyticsDemoContent implements ProvidesThemeDemoContent
{
    private const string BRAND = 'Citebase';

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
            title: self::BRAND . ' — AI Search Visibility Analytics',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'See where AI answers mention your brand',
                'Citebase tracks how ChatGPT, Perplexity, Gemini, and Google AI Overviews cite your brand, so you can grow the visibility that buyers now act on.',
            ),
            renderData: [
                'summary' => 'Citebase is the answer-engine analytics platform. Track citations, share of voice, and sources across every major AI assistant from one dashboard.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Answer-engine optimisation analytics',
                        'heading' => 'See where AI answers mention your brand',
                        'summary' => 'Buyers ask assistants before they ask Google. Citebase shows exactly when ChatGPT, Perplexity, Gemini, and AI Overviews cite you, who they cite instead, and which sources move the needle.',
                        'actions' => [
                            ['label' => 'Start tracking free', 'url' => '#contact', 'style' => 'primary'],
                            ['label' => 'View a live dashboard', 'url' => '#dashboard', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['hero'][0],
                        'mediaAlt' => 'Citebase AI visibility dashboard',
                    ],
                    $this->dashboardPreviewSection(
                        heading: 'Your AI visibility, on one dashboard',
                        summary: 'Every assistant, every market, every prompt cluster in a single view your whole team can read at a glance.',
                    ),
                    $this->metricCardsSection(
                        heading: 'The numbers that decide who gets cited',
                        summary: 'Track the four signals that determine whether an assistant recommends you or a competitor.',
                    ),
                    $this->featuresSection(
                        heading: 'Built for the teams who own AI search',
                        summary: 'From the first audit to weekly reporting, Citebase fits the way SEO, content, and brand teams already work.',
                    ),
                    $this->coverageMapSection(
                        heading: 'Coverage across every answer engine',
                        summary: 'We monitor the assistants your buyers actually use, in the markets and languages that matter to you.',
                    ),
                    $this->integrationsGridSection(
                        heading: 'Plugs into the stack you already run',
                        summary: 'Pipe visibility data into the tools your team lives in — no spreadsheets, no copy-paste.',
                    ),
                    $this->proofSection(
                        heading: 'Teams trust Citebase with their AI share of voice',
                        summary: 'Results from brands tracking answer-engine visibility with us.',
                    ),
                    $this->ctaSection(
                        heading: 'Find out how often AI recommends you',
                        summary: 'Run a free visibility audit across the major assistants. You will see your first report in minutes.',
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
            name: self::BRAND . ' Reports',
            title: 'Visibility reports — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'A directory of AI visibility reports',
                'Browse every saved report — citation trackers, competitor benchmarks, and prompt-cluster breakdowns, built to be scanned.',
            ),
            renderData: [
                'summary' => 'Saved reports across citation tracking, competitor benchmarks, and prompt-cluster analysis — filter, open, and share in a click.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Reports library',
                        'heading' => 'A directory of reports built to be scanned',
                        'summary' => 'Structured report cards keep your AI visibility legible. Filter by assistant, market, or competitor and open the full breakdown in one click.',
                        'actions' => [
                            ['label' => 'Build a new report', 'url' => '#contact', 'style' => 'primary'],
                        ],
                        'mediaUrl' => $media['listing'][0] ?? $media['hero'][0],
                        'mediaAlt' => 'Citebase report library',
                    ],
                    $this->reportGallerySection(
                        heading: 'Featured visibility reports',
                        summary: 'The reports teams open every week to brief stakeholders.',
                    ),
                    $this->contentListingSection(
                        heading: 'More from the report archive',
                        summary: 'Competitor benchmarks, prompt-cluster studies, and source audits.',
                    ),
                    $this->ctaSection(
                        heading: 'Need a report you do not see here?',
                        summary: 'Tell us the questions you care about and we will build a tracker around the exact prompts your buyers ask.',
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
            name: self::BRAND . ' Report',
            title: 'ChatGPT visibility report — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'A report view that reads with clarity',
                'A single visibility report pairs the dashboard, the metrics, and the source coverage so anyone can judge where you stand.',
            ),
            renderData: [
                'summary' => 'One report view brings the dashboard, the headline metrics, and the source coverage together so buyers can assess visibility with confidence.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Visibility report',
                        'heading' => 'A report view that reads with clarity',
                        'summary' => 'Q2 share of voice across ChatGPT, Perplexity, and Gemini for the buyer questions that matter most — and the sources driving every citation.',
                        'actions' => [
                            ['label' => 'Back to all reports', 'url' => '#reports', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['detail'][0],
                        'mediaAlt' => 'Citebase visibility report',
                    ],
                    $this->dashboardPreviewSection(
                        heading: 'The headline view',
                        summary: 'Share of voice and citation trend over the reporting window, side by side with your top two competitors.',
                    ),
                    $this->metricCardsSection(
                        heading: 'What changed this quarter',
                        summary: 'The movements behind the trend — citations won, sources gained, and prompts where you slipped.',
                    ),
                    $this->coverageMapSection(
                        heading: 'Where the citations came from',
                        summary: 'The assistants, markets, and source domains feeding this report.',
                    ),
                    $this->ctaSection(
                        heading: 'Want a report like this for your brand?',
                        summary: 'We will build the same view around your prompts and competitors. Most teams see their first report inside a day.',
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
            title: 'Talk to the team — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Reach the team through one confident path',
                'Tell us which assistants and competitors you want to track. We will stand up your first visibility report and walk you through it.',
            ),
            renderData: [
                'summary' => 'Tell us which assistants and competitors matter to you. We scope every workspace with the team that builds the trackers — no hand-offs.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Talk to the team',
                        'heading' => 'Reach the team through one confident path',
                        'summary' => 'Email hello@citebase.example or book a 20-minute walkthrough. We will show your live visibility before you commit to anything.',
                        'actions' => [
                            ['label' => 'Email the team', 'url' => 'mailto:hello@citebase.example', 'style' => 'primary'],
                            ['label' => 'See a live dashboard', 'url' => '#dashboard', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['contact'][0],
                        'mediaAlt' => 'The Citebase team',
                    ],
                    $this->featuresSection(
                        heading: 'What you get on the first call',
                        summary: 'A real look at how assistants cite you across answer engines, with your live share-of-voice on screen.',
                    ),
                    $this->metricCardsSection(
                        heading: 'The questions we answer for you',
                        summary: 'By the end of onboarding you will know exactly where you stand on each signal.',
                    ),
                    $this->ctaSection(
                        heading: 'Prefer to start with a free audit?',
                        summary: 'Send us your domain and top three competitors. We will email a visibility snapshot within one working day.',
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
            title: 'No reports yet — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'Nothing tracked here yet',
                'A graceful empty state for a workspace that is still gathering its first AI visibility data.',
            ),
            renderData: [
                'summary' => 'No reports match that filter yet — but Citebase can still point you to the next useful step while data lands.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Reports library',
                        'heading' => 'Nothing tracked here yet',
                        'summary' => 'No reports match that filter. Clear it to see every tracker, or start a new one and your first citations will land within the hour.',
                        'actions' => [
                            ['label' => 'Start a new tracker', 'url' => '#contact', 'style' => 'primary'],
                            ['label' => 'View all reports', 'url' => '#reports', 'style' => 'secondary'],
                        ],
                    ],
                    [
                        'type' => 'content-listing',
                        'heading' => 'No reports to show here',
                        'summary' => 'When trackers in this category collect data, their reports appear here, newest first.',
                        'items' => [],
                    ],
                    $this->featuresSection(
                        heading: 'While your data lands',
                        summary: 'The three things Citebase sets up the moment a tracker goes live.',
                    ),
                    $this->ctaSection(
                        heading: 'Want help building your first tracker?',
                        summary: 'Tell us the buyer questions you care about and we will configure the prompts for you.',
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
                'A not-found page that routes visitors back into the dashboard and reports.',
            ),
            renderData: [
                'summary' => 'That page has moved or never existed — here is the way back to your dashboard.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => '404',
                        'heading' => 'That page could not be found',
                        'summary' => 'The link is broken or the report has moved. Head back to your dashboard, or jump into the reports library.',
                        'actions' => [
                            ['label' => 'Back to dashboard', 'url' => '#dashboard', 'style' => 'primary'],
                            ['label' => 'View all reports', 'url' => '#reports', 'style' => 'secondary'],
                        ],
                    ],
                    $this->ctaSection(
                        heading: 'Looking for a specific report?',
                        summary: 'Tell us what you needed and we will point you to the right tracker.',
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
            title: 'Track your AI visibility — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Turn intent into a tracked workspace',
                'A focused conversion page that turns "are we showing up in AI answers?" into a live dashboard.',
            ),
            renderData: [
                'summary' => 'Turn intent into a tracked workspace. Stand up your AI visibility dashboard and see your first citations today.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Get started',
                        'heading' => 'Turn intent into a tracked workspace',
                        'summary' => 'Whether you track one brand or a whole portfolio, you get the same coverage and the same dashboard. Set up takes minutes.',
                        'actions' => [
                            ['label' => 'Start tracking free', 'url' => '#contact', 'style' => 'primary'],
                            ['label' => 'Email the team', 'url' => 'mailto:hello@citebase.example', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['cta'][0],
                        'mediaAlt' => 'Citebase workspace',
                    ],
                    $this->proofSection(
                        heading: 'Why teams choose Citebase',
                        summary: 'The numbers behind the platform.',
                    ),
                    $this->metricCardsSection(
                        heading: 'What you start measuring on day one',
                        summary: 'The signals that go live the moment your workspace is set up.',
                    ),
                    $this->ctaSection(
                        heading: 'One audit away from clarity',
                        summary: 'Send your domain and competitors and we will reply within one working day with your first visibility snapshot.',
                    ),
                ],
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function dashboardPreviewSection(string $heading, string $summary): array
    {
        return [
            'type' => 'dashboard-preview',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Share of voice', 'summary' => 'Your slice of AI answers for tracked questions, trended week over week against named competitors.'],
                ['title' => 'Citation feed', 'summary' => 'Every mention as it happens — the assistant, the prompt, and the exact source it cited.'],
                ['title' => 'Competitor gap', 'summary' => 'The prompts where rivals are recommended and you are not, ranked by how often buyers ask them.'],
                ['title' => 'Source leaderboard', 'summary' => 'The pages and domains assistants trust most, so you know where to earn your next citation.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function metricCardsSection(string $heading, string $summary): array
    {
        return [
            'type' => 'metric-cards',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Citation rate', 'summary' => 'How often assistants name you when buyers ask a question in your category.'],
                ['title' => 'Share of voice', 'summary' => 'Your visibility measured against the competitors you choose to track.'],
                ['title' => 'Sentiment', 'summary' => 'Whether the answer frames you as a leader, an option, or an afterthought.'],
                ['title' => 'Source authority', 'summary' => 'The strength of the pages assistants cite when they recommend you.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function coverageMapSection(string $heading, string $summary): array
    {
        return [
            'type' => 'coverage-map',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'ChatGPT & GPT search', 'summary' => 'Daily monitoring across the assistant most of your buyers reach for first.'],
                ['title' => 'Perplexity', 'summary' => 'Full citation capture, including the source list shown beneath every answer.'],
                ['title' => 'Google AI Overviews', 'summary' => 'Track when your category triggers an overview and whether you make the cut.'],
                ['title' => 'Gemini & Copilot', 'summary' => 'Coverage across the assistants built into the tools your buyers already use.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function integrationsGridSection(string $heading, string $summary): array
    {
        return [
            'type' => 'integrations-grid',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Looker & BigQuery', 'summary' => 'Stream raw visibility data into your warehouse and blend it with the rest of your funnel.'],
                ['title' => 'Slack & Teams', 'summary' => 'Get alerted the moment you win or lose a citation on a high-intent prompt.'],
                ['title' => 'Google Search Console', 'summary' => 'Sit AI visibility next to classic search so you can see the whole picture.'],
                ['title' => 'Webhooks & API', 'summary' => 'Pull every metric into your own tools with a documented REST API and webhooks.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function reportGallerySection(string $heading, string $summary): array
    {
        return [
            'type' => 'report-gallery',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Q2 citation tracker', 'summary' => 'Share of voice across four assistants for your top 50 buyer questions.'],
                ['title' => 'Competitor benchmark', 'summary' => 'Head-to-head visibility against three named rivals, refreshed weekly.'],
                ['title' => 'Prompt-cluster study', 'summary' => 'Where you win and lose grouped by buyer intent, from research to decision.'],
                ['title' => 'Source audit', 'summary' => 'The domains assistants cite in your category and how to earn a place on them.'],
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
                ['title' => 'Prompt tracking', 'summary' => 'Tell us the questions your buyers ask and we run them across every assistant on a schedule.'],
                ['title' => 'Competitor watch', 'summary' => 'Name your rivals and see exactly where they are recommended ahead of you.'],
                ['title' => 'Scheduled reports', 'summary' => 'Share-ready reports land in your inbox and Slack on the cadence you choose.'],
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
                ['metric' => '4', 'name' => 'Major assistants tracked', 'quote' => 'ChatGPT, Perplexity, Gemini, and AI Overviews, monitored daily.'],
                ['metric' => '+41%', 'name' => 'Average citation lift', 'quote' => 'Median gain in answer-engine share of voice within a quarter.'],
                ['metric' => '1 day', 'name' => 'To your first report', 'quote' => 'Most workspaces have a live tracker before the end of day one.'],
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
                ['title' => 'Sentiment over time', 'summary' => 'How assistants frame your brand across the reporting window.'],
                ['title' => 'New sources this month', 'summary' => 'Pages that started citing you, ranked by the authority they carry.'],
                ['title' => 'Lost-citation alerts', 'summary' => 'Prompts where you dropped out of the answer, with the competitor who took your place.'],
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
                ['label' => 'Start tracking free', 'url' => '#contact', 'style' => 'primary'],
                ['label' => 'View a dashboard', 'url' => '#dashboard', 'style' => 'secondary'],
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
                ['label' => 'Dashboard', 'url' => '#dashboard'],
                ['label' => 'Reports', 'url' => '#reports'],
                ['label' => 'Coverage', 'url' => '#coverage'],
                ['label' => 'Integrations', 'url' => '#integrations'],
                ['label' => 'Pricing', 'url' => '#pricing'],
            ],
            'ctaLabel' => 'Start tracking',
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
            'summary' => 'Answer-engine analytics for teams who need to show up where AI recommends brands.',
            'columns' => [
                [
                    'heading' => 'Product',
                    'links' => [
                        ['label' => 'Dashboard', 'url' => '#dashboard'],
                        ['label' => 'Reports', 'url' => '#reports'],
                        ['label' => 'Coverage', 'url' => '#coverage'],
                        ['label' => 'Integrations', 'url' => '#integrations'],
                    ],
                ],
                [
                    'heading' => 'Company',
                    'links' => [
                        ['label' => 'About', 'url' => '#about'],
                        ['label' => 'Customers', 'url' => '#customers'],
                        ['label' => 'Careers', 'url' => '#careers'],
                        ['label' => 'Blog', 'url' => '#blog'],
                    ],
                ],
                [
                    'heading' => 'Connect',
                    'links' => [
                        ['label' => 'Contact', 'url' => '#contact'],
                        ['label' => 'hello@citebase.example', 'url' => 'mailto:hello@citebase.example'],
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
