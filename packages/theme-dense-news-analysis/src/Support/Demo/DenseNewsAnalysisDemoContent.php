<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\DenseNewsAnalysis\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Dense News Analysis theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the
 * page adapter emits the theme's signature newsroom renderers (top-stories /
 * live-brief / topic-navigation / opinion-analysis / video-row / missed-it /
 * newsletter) alongside the shared hero/proof/cta — giving every surface a
 * full, individual publisher site rather than the shared five-section skeleton.
 *
 * Navigation seeds both `brand` (blade payload key) and `brandName` (contract
 * key); footer seeds both `items` (blade payload key) and `columns` (contract
 * key) so the live render and the completeness contract both stay satisfied.
 */
final class DenseNewsAnalysisDemoContent implements ProvidesThemeDemoContent
{
    private const BRAND = 'The Meridian Review';

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
            name: self::BRAND . ' Front Page',
            title: self::BRAND . ' — News, analysis, and live coverage',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'A serious newsroom front page',
                'The Meridian Review covers public-interest news, analysis, opinion, and video with a dense, trustworthy editorial hierarchy.',
            ),
            renderData: [
                'summary' => 'Top stories, live coverage, analysis, opinion, and video from The Meridian Review newsroom.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        heading: 'News and analysis a newsroom can stand behind',
                        summary: 'The front page for top stories, live coverage, opinion, video, topic navigation, and reader briefings — built to stay legible under a busy news day.',
                        mediaUrl: $media['hero'][0],
                        mediaAlt: 'The Meridian Review newsroom',
                    ),
                    $this->topStoriesSection(),
                    $this->liveBriefSection(),
                    $this->topicNavigationSection(),
                    $this->opinionAnalysisSection(),
                    $this->videoRowSection(),
                    $this->missedItSection(),
                    $this->proofSection(),
                    $this->newsletterSection(),
                    $this->ctaSection(
                        heading: 'Read the news that holds up to scrutiny',
                        summary: 'Follow The Meridian Review for dense hierarchy, clear labels, live context, and analysis you can trust.',
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
            name: self::BRAND . ' Topics',
            title: 'Topics — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'A topic archive built to be scanned',
                'Browse news, analysis, opinion, and video across world affairs, politics, business, culture, and sport.',
            ),
            renderData: [
                'summary' => 'A structured topic archive across world, politics, business, culture, and sport.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        heading: 'Every story, sorted by the way readers think',
                        summary: 'Structured topic navigation keeps news, analysis, and opinion legible across a large newsroom without burying the lead.',
                        mediaUrl: $media['listing'][0] ?? $media['hero'][0],
                        mediaAlt: 'Topic archive front',
                    ),
                    $this->topicNavigationSection(),
                    $this->contentListingSection(
                        heading: 'Latest across every desk',
                        summary: 'News, analysis, opinion, video, live coverage, and briefings, newest first.',
                    ),
                    $this->missedItSection(),
                    $this->ctaSection(
                        heading: 'Follow the topics that matter to you',
                        summary: 'Subscribe to topic alerts and the daily briefing to keep the coverage close.',
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
            title: 'Government faces pressure as talks enter final day — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'Government faces pressure as talks enter final day',
                'A lead story with standfirst, byline, timestamp, live context, and related analysis.',
            ),
            renderData: [
                'summary' => 'A lead news story with live context, analysis, and related coverage.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        heading: 'Government faces pressure as talks enter final day',
                        summary: 'Negotiators returned to the table this morning with both sides signalling that a deal remained possible but far from certain. Analysis and live coverage below.',
                        mediaUrl: $media['detail'][0],
                        mediaAlt: 'Talks enter their final day',
                    ),
                    $this->liveBriefSection(),
                    $this->opinionAnalysisSection(),
                    $this->videoRowSection(),
                    $this->contentListingSection(
                        heading: 'Related coverage',
                        summary: 'Background, explainers, and earlier reporting on this story.',
                    ),
                    $this->ctaSection(
                        heading: 'Stay with the story as it develops',
                        summary: 'Follow live coverage and get the analysis sent to your inbox as the day unfolds.',
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
            title: 'Subscribe — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Subscribe through one confident path',
                'Get the daily briefing, live alerts, analysis, and weekend reading from The Meridian Review newsroom.',
            ),
            renderData: [
                'summary' => 'Subscribe to the daily briefing, live alerts, and weekend reading.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        heading: 'Get the briefing without losing the nuance',
                        summary: 'Reach the newsroom and join the readership through one clear path. Tips go to tips@meridianreview.example; subscriptions start below.',
                        mediaUrl: $media['contact'][0],
                        mediaAlt: 'The Meridian Review newsroom desk',
                    ),
                    $this->newsletterSection(),
                    $this->proofSection(),
                    $this->ctaSection(
                        heading: 'Read the newsroom every morning',
                        summary: 'Start the daily briefing and we will land the most important coverage in your inbox before the day begins.',
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
            title: 'No results — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'No stories match that search yet',
                'A graceful empty state for a filtered newsroom search with no matching coverage.',
            ),
            renderData: [
                'summary' => 'No coverage matches that search yet — here is the way back into the newsroom.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        heading: 'No stories match that search — yet',
                        summary: 'We have not published coverage matching those terms. Clear the search to see the latest, or browse by topic below.',
                        mediaUrl: null,
                        mediaAlt: null,
                    ),
                    $this->topicNavigationSection(),
                    $this->missedItSection(),
                    $this->ctaSection(
                        heading: 'Looking for a specific story?',
                        summary: 'Subscribe to the briefing and we will keep the coverage you care about within reach.',
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
                'A not-found page that routes readers back into the newsroom and the daily briefing.',
            ),
            renderData: [
                'summary' => 'That page has moved or never existed — here is the way back into the newsroom.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        heading: 'This page has moved on with the news',
                        summary: 'The link is broken or the story has been archived. Head back to the front page, or browse the latest by topic.',
                        mediaUrl: null,
                        mediaAlt: null,
                    ),
                    $this->topicNavigationSection(),
                    $this->ctaSection(
                        heading: 'Back to the front page',
                        summary: 'Return to today\'s top stories, or subscribe to keep the newsroom close.',
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
            name: self::BRAND . ' Membership',
            title: 'Become a member — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Independent journalism you can rely on',
                'A focused conversion page inviting readers to support the newsroom and join the membership.',
            ),
            renderData: [
                'summary' => 'Support independent journalism and join the membership.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        heading: 'Independent journalism you can rely on',
                        summary: 'Members fund the reporting, analysis, and live coverage that keeps the newsroom independent. Join readers who back the work.',
                        mediaUrl: $media['cta'][0],
                        mediaAlt: 'Support The Meridian Review',
                    ),
                    $this->proofSection(),
                    $this->newsletterSection(),
                    $this->ctaSection(
                        heading: 'Back the newsroom today',
                        summary: 'Become a member and keep public-interest journalism free of paywalls for the readers who need it most.',
                    ),
                ],
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function heroSection(string $heading, string $summary, ?string $mediaUrl, ?string $mediaAlt): array
    {
        return [
            'type' => 'hero',
            'eyebrow' => 'Dense news analysis',
            'kicker' => 'The Meridian Review',
            'heading' => $heading,
            'summary' => $summary,
            'primary_label' => 'Read top stories',
            'primary_url' => '#top-stories',
            'secondary_label' => 'Follow live coverage',
            'secondary_url' => '#live-brief',
            'actions' => [
                ['label' => 'Read top stories', 'url' => '#top-stories', 'style' => 'primary'],
                ['label' => 'Follow live coverage', 'url' => '#live-brief', 'style' => 'secondary'],
            ],
            'notes' => [
                'Top stories, live briefings, opinion, video, topics, and recaps stay modular.',
                'Lead story, secondary columns, briefs, analysis blocks, and video rows keep their hierarchy.',
                'Newsletter and membership inserts sit inside the editorial rhythm.',
            ],
            'mediaUrl' => $mediaUrl,
            'mediaAlt' => $mediaAlt,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function topStoriesSection(): array
    {
        return [
            'type' => 'top-stories',
            'heading' => 'Lead story, secondary columns, and briefs in one system',
            'summary' => 'The lead module carries urgent public-interest coverage with analysis, live context, and related reporting alongside it.',
            'items' => [
                ['title' => 'Government faces pressure as talks enter final day', 'summary' => 'Negotiators returned this morning with both sides signalling a deal remains possible but far from certain. Standfirst, byline, and timestamp included.'],
                ['title' => 'What the new figures mean for households', 'summary' => 'A secondary analysis column unpacking the data behind the headline, with a link to deeper coverage on the cost of living.'],
                ['title' => 'Five developments you may have missed', 'summary' => 'A brief list that keeps readers oriented through a busy day without crowding the lead story.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function liveBriefSection(): array
    {
        return [
            'type' => 'live-brief',
            'heading' => 'Updates with timestamps and visible urgency',
            'summary' => 'A live blog for breaking developments, minute-by-minute coverage, and newsroom explainers as the story moves.',
            'label' => 'Follow updates',
            'url' => '#live-brief',
            'items' => [
                ['title' => 'Live: key decisions expected this afternoon', 'summary' => 'A live update card with timestamp, status label, and a concise summary of what just changed.'],
                ['title' => 'Explainer: why the vote matters', 'summary' => 'A context card for readers joining the story late, with the background in three short paragraphs.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function topicNavigationSection(): array
    {
        return [
            'type' => 'topic-navigation',
            'heading' => 'Practical routes through a large newsroom',
            'summary' => 'Topic navigation keeps a sprawling newsroom legible — readers move straight to the desk they came for.',
            'items' => [
                ['title' => 'World', 'summary' => 'Global affairs, diplomacy, conflict, climate, health, and development.'],
                ['title' => 'Politics', 'summary' => 'Parliament, elections, policy, courts, local government, and accountability.'],
                ['title' => 'Business', 'summary' => 'Markets, companies, regulation, work, consumer affairs, and analysis.'],
                ['title' => 'Culture and sport', 'summary' => 'Reviews, sport, media, books, television, and public life.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function opinionAnalysisSection(): array
    {
        return [
            'type' => 'opinion-analysis',
            'heading' => 'Make interpretation visibly different from reporting',
            'summary' => 'Opinion and analysis cards carry labels, authors, timestamps, and read-time so readers always know what they are reading.',
            'items' => [
                ['title' => 'The policy argument beneath the headline', 'summary' => 'An analysis piece that signals interpretation while keeping the news context close at hand.', 'meta' => 'Analysis. 8 min read.'],
                ['title' => 'The editorial board: a test of public trust', 'summary' => 'An opinion column with board attribution and links to the reporting it responds to.', 'meta' => 'Opinion. Editorial.'],
                ['title' => 'Institutions move slowly until they do not', 'summary' => 'A columnist with a recurring voice, dated and tagged so readers can follow the thread.', 'meta' => 'Column.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function videoRowSection(): array
    {
        return [
            'type' => 'video-row',
            'heading' => 'Video rows that still feel like news',
            'summary' => 'Video sits inside the editorial rhythm — short packages, explainers, and interviews with the same trust signals as text.',
            'items' => [
                ['title' => 'Watch: the exchange that shifted the hearing', 'summary' => 'A video card with a short description, topic, duration, and related reading.', 'meta' => 'Video. 3:12.'],
                ['title' => 'The map behind the story', 'summary' => 'A visual explainer for maps, charts, and short documentary packages.', 'meta' => 'Visual explainer.'],
                ['title' => 'Interview: inside the newsroom decision', 'summary' => 'A video interview with guest, department, and timestamp metadata.', 'meta' => 'Interview.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function missedItSection(): array
    {
        return [
            'type' => 'missed-it',
            'heading' => 'A disciplined recap for returning readers',
            'summary' => 'Missed-it lists, most-read rows, and weekend reads bring readers back up to speed without overwhelming them.',
            'items' => [
                ['title' => 'The five-minute catch-up', 'summary' => 'A compact story list for readers returning after several hours away from the news.'],
                ['title' => 'Most read in analysis', 'summary' => 'A ranked row for the explainers and opinion pieces readers came back to most.'],
                ['title' => 'Weekend reading', 'summary' => 'A curated block for long reads, investigations, reviews, and backgrounders.'],
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
            'heading' => 'Why readers trust the newsroom',
            'summary' => 'The editorial standards behind every front page.',
            'items' => [
                ['value' => 'Dense hierarchy', 'label' => 'Lead stories, side columns, briefs, opinion, video, and recaps all carry distinct weight.'],
                ['value' => 'Trust signals', 'label' => 'Cards support byline, timestamp, label, department, and related coverage context.'],
                ['value' => 'Reader paths', 'label' => 'Newsletter, live coverage, topic navigation, and missed-it sections guide repeat visits.'],
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
                ['title' => 'Markets steady as central bank holds rate', 'category' => 'Business', 'summary' => 'A wrap of the day\'s market reaction with analysis of what the decision signals for borrowers.'],
                ['title' => 'Inside the committee room: how the vote turned', 'category' => 'Politics', 'summary' => 'A reconstruction of the afternoon\'s decisive session, with sources on both sides.'],
                ['title' => 'The long read: a city rebuilding after the flood', 'category' => 'World', 'summary' => 'A reported feature on recovery, resilience, and the politics of who pays.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function newsletterSection(): array
    {
        return [
            'type' => 'newsletter',
            'heading' => 'Get the briefing without losing the nuance',
            'summary' => 'A focused signup for the daily briefing, live alerts, analysis, and weekend reading from the newsroom.',
            'action' => '#subscribe',
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
            'label' => 'Subscribe to the briefing',
            'url' => '#subscribe',
            'actions' => [
                ['label' => 'Subscribe to the briefing', 'url' => '#subscribe', 'style' => 'primary'],
                ['label' => 'Browse topics', 'url' => '#topic-navigation', 'style' => 'secondary'],
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
                ['label' => 'Top stories', 'url' => '#top-stories'],
                ['label' => 'Live brief', 'url' => '#live-brief'],
                ['label' => 'Opinion', 'url' => '#opinion-analysis'],
                ['label' => 'Video', 'url' => '#video-row'],
                ['label' => 'Topics', 'url' => '#topic-navigation'],
            ],
            'ctaLabel' => 'Subscribe',
            'ctaUrl' => '#subscribe',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        $columns = [
            [
                'heading' => 'Sections',
                'title' => 'Sections',
                'links' => [
                    ['label' => 'World', 'url' => '#topic-navigation'],
                    ['label' => 'Politics', 'url' => '#topic-navigation'],
                    ['label' => 'Business', 'url' => '#topic-navigation'],
                    ['label' => 'Culture and sport', 'url' => '#topic-navigation'],
                ],
            ],
            [
                'heading' => 'Formats',
                'title' => 'Formats',
                'links' => [
                    ['label' => 'Live coverage', 'url' => '#live-brief'],
                    ['label' => 'Opinion', 'url' => '#opinion-analysis'],
                    ['label' => 'Video', 'url' => '#video-row'],
                    ['label' => 'Newsletters', 'url' => '#subscribe'],
                ],
            ],
            [
                'heading' => 'More',
                'title' => 'More',
                'links' => [
                    ['label' => 'Most read', 'url' => '#missed-it'],
                    ['label' => 'Subscribe', 'url' => '#subscribe'],
                    ['label' => 'tips@meridianreview.example', 'url' => 'mailto:tips@meridianreview.example'],
                ],
            ],
        ];

        return [
            'brand' => self::BRAND,
            'brandName' => self::BRAND,
            'summary' => 'Independent news, analysis, opinion, and live coverage from The Meridian Review.',
            'items' => $columns,
            'columns' => $columns,
        ];
    }

    private function prose(string $heading, string $summary): string
    {
        return sprintf('<h2>%s</h2><p>%s</p>', e($heading), e($summary));
    }
}
