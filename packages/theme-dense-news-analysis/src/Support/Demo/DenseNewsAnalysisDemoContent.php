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
 *
 * In-page anchors are resolved per surface: links only use `#anchor` when the
 * target section is present on that page, otherwise they fall back to the
 * demo page that carries the section, so no link ever goes nowhere.
 */
final class DenseNewsAnalysisDemoContent implements ProvidesThemeDemoContent
{
    private const string BRAND = 'The Meridian Review';

    private string $themeKey = 'dense-news-analysis';

    /**
     * @return array<int, ThemeDemoPageDefinition>
     */
    public function definitions(string $themeKey, string $themeName, string $baseUrl): array
    {
        $this->themeKey = $themeKey;
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
        $anchors = ['top-stories', 'live-brief', 'topic-navigation', 'opinion-analysis', 'video-row', 'missed-it', 'subscribe'];

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
                'navigation' => $this->navigation($anchors),
                'footer' => $this->footer($anchors),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Front page',
                        heading: 'News and analysis a newsroom can stand behind',
                        summary: 'Today: negotiators return for the final day of talks, the central bank holds the rate, and a court ruling narrows the government\'s options. Live coverage and analysis through the day.',
                        mediaUrl: $media['hero'][0],
                        mediaAlt: 'The Meridian Review newsroom',
                        anchors: $anchors,
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
                        summary: 'Follow The Meridian Review for reporting, live coverage, and analysis you can trust.',
                        anchors: $anchors,
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
        $anchors = ['topic-navigation', 'missed-it'];

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
                'navigation' => $this->navigation($anchors),
                'footer' => $this->footer($anchors),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Topics',
                        heading: 'Every desk, from world affairs to the weekend read',
                        summary: 'Browse the newsroom by topic — reporting, live coverage, analysis, and opinion from every desk, updated through the day.',
                        mediaUrl: $media['listing'][0] ?? $media['hero'][0],
                        mediaAlt: 'Topic archive front',
                        anchors: $anchors,
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
                        anchors: $anchors,
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
        $anchors = ['live-brief', 'opinion-analysis', 'video-row'];

        return new ThemeDemoPageDefinition(
            surface: 'detail',
            name: self::BRAND . ' Story',
            title: 'Government faces pressure as talks enter final day — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'Government faces pressure as talks enter final day',
                'Negotiators returned to the table this morning with both sides signalling that a deal remained possible but far from certain.',
            ),
            renderData: [
                'summary' => 'A lead news story with live context, analysis, and related coverage.',
                'navigation' => $this->navigation($anchors),
                'footer' => $this->footer($anchors),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Live coverage',
                        heading: 'Government faces pressure as talks enter final day',
                        summary: 'Negotiators returned to the table this morning with both sides signalling that a deal remained possible but far from certain. Analysis and live coverage below.',
                        mediaUrl: $media['detail'][0],
                        mediaAlt: 'Talks enter their final day',
                        anchors: $anchors,
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
                        anchors: $anchors,
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
        $anchors = ['subscribe'];

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
                'navigation' => $this->navigation($anchors),
                'footer' => $this->footer($anchors),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Subscribe',
                        heading: 'Reach the newsroom, join the readership',
                        summary: 'Tips go to tips@meridianreview.example and are read by an editor the same day. Subscriptions to the daily briefing start below.',
                        mediaUrl: $media['contact'][0],
                        mediaAlt: 'The Meridian Review newsroom desk',
                        anchors: $anchors,
                    ),
                    $this->newsletterSection(),
                    $this->proofSection(),
                    $this->ctaSection(
                        heading: 'Read the newsroom every morning',
                        summary: 'Start the daily briefing and we will land the most important coverage in your inbox before the day begins.',
                        anchors: $anchors,
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
        $anchors = ['topic-navigation', 'missed-it'];

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
                'navigation' => $this->navigation($anchors),
                'footer' => $this->footer($anchors),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Search',
                        heading: 'No stories match that search — yet',
                        summary: 'We have not published coverage matching those terms. Clear the search to see the latest, or browse by topic below.',
                        mediaUrl: null,
                        mediaAlt: null,
                        anchors: $anchors,
                    ),
                    $this->topicNavigationSection(),
                    $this->missedItSection(),
                    $this->ctaSection(
                        heading: 'Looking for a specific story?',
                        summary: 'Subscribe to the briefing and we will keep the coverage you care about within reach.',
                        anchors: $anchors,
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
        $anchors = ['topic-navigation'];

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
                'navigation' => $this->navigation($anchors),
                'footer' => $this->footer($anchors),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Page not found',
                        heading: 'This page has moved on with the news',
                        summary: 'The link is broken or the story has been archived. Head back to the front page, or browse the latest by topic.',
                        mediaUrl: null,
                        mediaAlt: null,
                        anchors: $anchors,
                    ),
                    $this->topicNavigationSection(),
                    $this->ctaSection(
                        heading: 'Back to the front page',
                        summary: 'Return to today\'s top stories, or subscribe to keep the newsroom close.',
                        anchors: $anchors,
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
        $anchors = ['subscribe'];

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
                'navigation' => $this->navigation($anchors),
                'footer' => $this->footer($anchors),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Membership',
                        heading: 'Independent journalism you can rely on',
                        summary: 'Members fund the reporting, analysis, and live coverage that keeps the newsroom independent. Join readers who back the work.',
                        mediaUrl: $media['cta'][0],
                        mediaAlt: 'Support The Meridian Review',
                        anchors: $anchors,
                    ),
                    $this->proofSection(),
                    $this->newsletterSection(),
                    $this->ctaSection(
                        heading: 'Back the newsroom today',
                        summary: 'Become a member and keep public-interest journalism free of paywalls for the readers who need it most.',
                        anchors: $anchors,
                    ),
                ],
            ],
        );
    }

    /**
     * @param  list<string>  $anchors
     * @return array<string, mixed>
     */
    private function heroSection(string $eyebrow, string $heading, string $summary, ?string $mediaUrl, ?string $mediaAlt, array $anchors): array
    {
        // Only the HeroSectionData whitelist survives the page adapter:
        // heading / eyebrow / summary / actions / mediaUrl / mediaAlt. The
        // wire-panel dateline and notes stay lang-owned in the blade.
        return [
            'type' => 'hero',
            'eyebrow' => $eyebrow,
            'heading' => $heading,
            'summary' => $summary,
            'actions' => [
                ['label' => 'Read top stories', 'url' => $this->linkTo('top-stories', $anchors), 'style' => 'primary'],
                ['label' => 'Follow live coverage', 'url' => $this->linkTo('live-brief', $anchors), 'style' => 'secondary'],
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
            'heading' => 'The stories driving today\'s agenda',
            'summary' => 'Reporting, analysis, and live coverage from the overnight desk to this afternoon\'s developments.',
            'items' => [
                ['title' => 'Government faces pressure as talks enter final day', 'summary' => 'Negotiators returned to the table this morning with both sides signalling that a deal remains possible but far from certain. Officials say a joint statement is already being drafted.', 'url' => $this->pageUrl('detail')],
                ['title' => 'What the new figures mean for households', 'summary' => 'Behind the headline number, the data points to slower price growth for essentials — but not for housing, where the squeeze is set to continue into next year.', 'url' => $this->pageUrl('detail')],
                ['title' => 'Five developments you may have missed', 'summary' => 'The overnight decisions, resignations, and rulings that set up today\'s agenda, in the time it takes to pour a coffee.', 'url' => $this->pageUrl('directory')],
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
            'heading' => 'Live: talks reach their final hours',
            'summary' => 'Rolling updates from the negotiating room, with analysis from the political desk as the deadline approaches.',
            'label' => 'Follow updates',
            'url' => '#live-brief',
            'items' => [
                ['title' => 'Key decisions expected this afternoon', 'summary' => 'Officials say a joint statement is being drafted, though two sticking points remain unresolved after the morning session.', 'meta' => '14:32'],
                ['title' => 'Explainer: why the vote matters', 'summary' => 'The background to the deadlock in three short paragraphs, for readers joining the story late.', 'meta' => '13:05'],
                ['title' => 'Delegations break for private consultations', 'summary' => 'Both sides withdrew to separate rooms shortly before midday. A spokesperson called the pause "procedural, not political".', 'meta' => '11:47'],
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
            'heading' => 'Browse the desks',
            'summary' => 'Every desk from world affairs to culture and sport, updated through the day.',
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
            'heading' => 'Beyond the headlines',
            'summary' => 'Columnists and the editorial board on the week\'s defining arguments.',
            'items' => [
                ['title' => 'The policy argument beneath the headline', 'summary' => 'The negotiation is not really about the deadline — it is about who carries the cost of the last decade\'s decisions.', 'meta' => 'Analysis', 'care_note' => 'Priya Nair · 8 min read', 'url' => $this->pageUrl('detail')],
                ['title' => 'A test of public trust', 'summary' => 'This settlement will be judged not by the signing ceremony but by what happens after the cameras leave.', 'meta' => 'Opinion', 'care_note' => 'The editorial board', 'url' => $this->pageUrl('detail')],
                ['title' => 'Institutions move slowly until they do not', 'summary' => 'On the sudden collapse of a long consensus, and what history suggests comes next.', 'meta' => 'Column', 'care_note' => 'Amara Osei · Every Tuesday', 'url' => $this->pageUrl('detail')],
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
            'heading' => 'Watch: the day in three screens',
            'summary' => 'Short packages, explainers, and interviews from the video desk.',
            'items' => [
                ['title' => 'The exchange that shifted the hearing', 'summary' => 'The three-minute confrontation everyone is quoting, with context from our correspondent in the room.', 'meta' => 'Video · 3:12', 'url' => $this->pageUrl('detail')],
                ['title' => 'The map behind the story', 'summary' => 'How the disputed corridor became the deal\'s final sticking point, in one animated map.', 'meta' => 'Explainer · 2:04', 'url' => $this->pageUrl('detail')],
                ['title' => 'Inside the newsroom decision', 'summary' => 'Our editor on why we published the leaked memo — and what we held back.', 'meta' => 'Interview · 6:40', 'url' => $this->pageUrl('detail')],
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
            'heading' => 'Catch up in five minutes',
            'summary' => 'The stories readers came back to, and the long reads worth your weekend.',
            'items' => [
                ['title' => 'The five-minute catch-up', 'summary' => 'Everything that mattered since this morning\'s briefing, in one compact list.', 'url' => $this->pageUrl('home')],
                ['title' => 'Most read in analysis', 'summary' => 'Why the settlement maths does not add up — this week\'s most-shared explainer.', 'url' => $this->pageUrl('detail')],
                ['title' => 'Weekend reading', 'summary' => 'A city rebuilding after the flood, the quiet rise of the procurement bar, and more long reads.', 'url' => $this->pageUrl('directory')],
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
                ['value' => '212 journalists', 'label' => 'Reporters, editors, and producers across nine desks and four bureaux.'],
                ['value' => '06:00 daily', 'label' => 'The morning briefing lands before the day begins, every day of the year.'],
                ['value' => 'Corrections, published', 'label' => 'Every correction is logged, dated, and linked from the story it amends.'],
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
                ['title' => 'Markets steady as central bank holds rate', 'category' => 'Business', 'summary' => 'A wrap of the day\'s market reaction with analysis of what the decision signals for borrowers.', 'url' => $this->pageUrl('detail')],
                ['title' => 'Inside the committee room: how the vote turned', 'category' => 'Politics', 'summary' => 'A reconstruction of the afternoon\'s decisive session, with sources on both sides.', 'url' => $this->pageUrl('detail')],
                ['title' => 'The long read: a city rebuilding after the flood', 'category' => 'World', 'summary' => 'A reported feature on recovery, resilience, and the politics of who pays.', 'url' => $this->pageUrl('detail')],
                ['title' => 'Court ruling narrows options before the spending review', 'category' => 'Politics', 'summary' => 'The judgment on the procurement challenge lands weeks before the chancellor\'s statement.', 'url' => $this->pageUrl('detail')],
                ['title' => 'The coalition maths behind the deal', 'category' => 'Analysis', 'summary' => 'Why the numbers in the chamber matter more than the numbers in the treaty.', 'url' => $this->pageUrl('detail')],
                ['title' => 'Watch: the week in 90 seconds', 'category' => 'Video', 'summary' => 'The hearing, the ruling, and the resignation — the week\'s biggest moments in one package.', 'url' => $this->pageUrl('detail')],
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
            'summary' => 'One email at 06:00 with the top stories, the live desk\'s schedule, and the analysis worth your time.',
            'action' => '#',
        ];
    }

    /**
     * @param  list<string>  $anchors
     * @return array<string, mixed>
     */
    private function ctaSection(string $heading, string $summary, array $anchors): array
    {
        return [
            'type' => 'cta',
            'heading' => $heading,
            'summary' => $summary,
            'label' => 'Subscribe to the briefing',
            'url' => $this->linkTo('subscribe', $anchors),
            'actions' => [
                ['label' => 'Subscribe to the briefing', 'url' => $this->linkTo('subscribe', $anchors), 'style' => 'primary'],
                ['label' => 'Browse topics', 'url' => $this->linkTo('topic-navigation', $anchors), 'style' => 'secondary'],
            ],
        ];
    }

    /**
     * @param  list<string>  $anchors
     * @return array<string, mixed>
     */
    private function navigation(array $anchors): array
    {
        return [
            'brand' => self::BRAND,
            'brandName' => self::BRAND,
            'items' => [
                ['label' => 'Top stories', 'url' => $this->linkTo('top-stories', $anchors)],
                ['label' => 'Live brief', 'url' => $this->linkTo('live-brief', $anchors)],
                ['label' => 'Opinion', 'url' => $this->linkTo('opinion-analysis', $anchors)],
                ['label' => 'Video', 'url' => $this->linkTo('video-row', $anchors)],
                ['label' => 'Topics', 'url' => $this->linkTo('topic-navigation', $anchors)],
            ],
            'ctaLabel' => 'Subscribe',
            'ctaUrl' => $this->linkTo('subscribe', $anchors),
        ];
    }

    /**
     * @param  list<string>  $anchors
     * @return array<string, mixed>
     */
    private function footer(array $anchors): array
    {
        $columns = [
            [
                'heading' => 'Sections',
                'title' => 'Sections',
                'links' => [
                    ['label' => 'World', 'url' => $this->linkTo('topic-navigation', $anchors)],
                    ['label' => 'Politics', 'url' => $this->linkTo('topic-navigation', $anchors)],
                    ['label' => 'Business', 'url' => $this->linkTo('topic-navigation', $anchors)],
                    ['label' => 'Culture and sport', 'url' => $this->linkTo('topic-navigation', $anchors)],
                ],
            ],
            [
                'heading' => 'Formats',
                'title' => 'Formats',
                'links' => [
                    ['label' => 'Live coverage', 'url' => $this->linkTo('live-brief', $anchors)],
                    ['label' => 'Opinion', 'url' => $this->linkTo('opinion-analysis', $anchors)],
                    ['label' => 'Video', 'url' => $this->linkTo('video-row', $anchors)],
                    ['label' => 'Newsletters', 'url' => $this->linkTo('subscribe', $anchors)],
                ],
            ],
            [
                'heading' => 'More',
                'title' => 'More',
                'links' => [
                    ['label' => 'Most read', 'url' => $this->linkTo('missed-it', $anchors)],
                    ['label' => 'Subscribe', 'url' => $this->linkTo('subscribe', $anchors)],
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

    /**
     * Absolute demo-page URL for a surface, for story/card title links.
     */
    private function pageUrl(string $surface): string
    {
        return match ($surface) {
            'home' => '/theme-' . $this->themeKey,
            default => '/theme-' . $this->themeKey . '-' . $surface,
        };
    }

    /**
     * Resolve an in-page anchor to a link that always lands somewhere: the
     * `#anchor` when the section exists on the current surface, otherwise the
     * demo page that carries that section.
     *
     * @param  list<string>  $anchors
     */
    private function linkTo(string $anchor, array $anchors): string
    {
        if (in_array($anchor, $anchors, true)) {
            return '#' . $anchor;
        }

        return match ($anchor) {
            'top-stories' => '/theme-' . $this->themeKey,
            'live-brief', 'opinion-analysis', 'video-row' => '/theme-' . $this->themeKey . '-detail',
            'topic-navigation', 'missed-it' => '/theme-' . $this->themeKey . '-directory',
            'subscribe' => '/theme-' . $this->themeKey . '-contact',
            default => '/',
        };
    }

    private function prose(string $heading, string $summary): string
    {
        return sprintf('<h2>%s</h2><p>%s</p>', e($heading), e($summary));
    }
}
