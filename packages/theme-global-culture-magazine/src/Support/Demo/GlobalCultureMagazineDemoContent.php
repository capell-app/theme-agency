<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\GlobalCultureMagazine\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Global Culture Magazine theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the page
 * adapter emits the magazine's signature editorial renderers (lead-dispatch /
 * radio-audio / city-guides / travel-culture / shop-books / columnists / newsletter)
 * alongside the shared hero/proof/cta — giving every surface a full, individual
 * publication rather than the shared five-section skeleton.
 *
 * Every in-page anchor is resolved per surface through linkTo(): when the target
 * section renders on the same page the link stays an anchor, otherwise it points
 * at the demo page that actually carries that section — so no CTA, nav item, or
 * footer link is ever a dead anchor. Story, guide, episode, and column titles
 * link to the detail dispatch so the archive is genuinely clickable.
 *
 * Navigation and footer chrome are seeded in both key shapes: the strict demo
 * contract reads `navigation.brandName` / `navigation.items` / `footer.columns`,
 * while the rendered blade reads `navigation.brand` / `footer.items`. Both are
 * populated so the contract and the live render agree.
 *
 * Heroes and lead plates deliberately ship without stock imagery so the
 * theme's composed serif drop-cap plates render as warm editorial art
 * instead of mismatched photography.
 */
final class GlobalCultureMagazineDemoContent implements ProvidesThemeDemoContent
{
    private const string BRAND = 'The Global Review';

    /**
     * @return array<int, ThemeDemoPageDefinition>
     */
    public function definitions(string $themeKey, string $themeName, string $baseUrl): array
    {
        return [
            $this->homepage($themeKey),
            $this->directory($themeKey),
            $this->detail($themeKey),
            $this->contact($themeKey),
            $this->emptyState($themeKey),
            $this->notFound($themeKey),
            $this->cta($themeKey),
        ];
    }

    private function homepage(string $themeKey): ThemeDemoPageDefinition
    {
        $presentSections = ['lead-dispatch', 'radio-audio', 'city-guides', 'travel-culture', 'shop-books', 'columnists', 'newsletter'];

        return new ThemeDemoPageDefinition(
            surface: 'homepage',
            name: self::BRAND . ' Home',
            title: self::BRAND . ' — A Global Magazine for Stories That Matter',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'A global magazine for the stories that matter',
                'The Global Review is an editorial home for world affairs, travel and culture, radio and audio, city guides, columnists, and the books worth reading next.',
            ),
            renderData: [
                'summary' => 'The Global Review is an editorial magazine covering world affairs, travel and culture, radio, city guides, and the columnists shaping the conversation.',
                'navigation' => $this->navigation($presentSections, $themeKey),
                'footer' => $this->footer($presentSections, $themeKey),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'The Global Review',
                        heading: 'A global magazine for the stories that matter',
                        summary: 'Long-form reporting, dispatches from the field, and the cultural criticism that makes sense of a fast-moving world — published daily, read everywhere.',
                        primaryLabel: 'Read the lead dispatch',
                        primaryUrl: $this->linkTo('lead-dispatch', $presentSections, $themeKey),
                        secondaryLabel: 'Browse city guides',
                        secondaryUrl: $this->linkTo('city-guides', $presentSections, $themeKey),
                    ),
                    $this->leadDispatchSection($themeKey),
                    $this->radioAudioSection($themeKey),
                    $this->cityGuidesSection($themeKey),
                    $this->travelCultureSection($themeKey),
                    $this->shopBooksSection($themeKey),
                    $this->columnistsSection($themeKey),
                    $this->proofSection(
                        heading: 'Read in 140 countries',
                        summary: 'The numbers behind a magazine that travels as far as its stories.',
                    ),
                    $this->newsletterSection(
                        heading: 'The dispatch, in your inbox each morning',
                        summary: 'One considered email a day — the lead story, the best of culture, and what our columnists are watching.',
                    ),
                    $this->ctaSection(
                        heading: 'Become a member of The Global Review',
                        summary: 'Unlimited reading, the full audio archive, and the Saturday culture edition. Cancel any time.',
                        presentSections: $presentSections,
                        themeKey: $themeKey,
                    ),
                ],
            ],
            type: PageTypeEnum::Home,
            layout: LayoutEnum::Home,
        );
    }

    private function directory(string $themeKey): ThemeDemoPageDefinition
    {
        $presentSections = ['city-guides', 'columnists'];

        return new ThemeDemoPageDefinition(
            surface: 'directory',
            name: self::BRAND . ' Archive',
            title: 'The Archive — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'The archive, built to be scanned',
                'Every dispatch, city guide, radio episode, and column in one structured, scannable index.',
            ),
            renderData: [
                'summary' => 'Browse the full magazine — affairs, travel and culture, radio, city guides, and columnists — in one structured archive.',
                'navigation' => $this->navigation($presentSections, $themeKey),
                'footer' => $this->footer($presentSections, $themeKey),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'The archive',
                        heading: 'An archive built to be scanned',
                        summary: 'Filter by desk, region, or format. Read the long-form story behind every dispatch, episode, and guide we have published.',
                        primaryLabel: 'Browse city guides',
                        primaryUrl: $this->linkTo('city-guides', $presentSections, $themeKey),
                        secondaryLabel: 'Subscribe',
                        secondaryUrl: $this->linkTo('newsletter', $presentSections, $themeKey),
                    ),
                    $this->contentListingSection(
                        heading: 'Across every desk',
                        summary: 'Affairs, travel, culture, and radio kept legible in one premium grid.',
                        themeKey: $themeKey,
                    ),
                    $this->cityGuidesSection($themeKey),
                    $this->columnistsSection($themeKey),
                    $this->ctaSection(
                        heading: 'Read the whole archive without limits',
                        summary: 'Membership opens every dispatch, guide, and episode we have ever published.',
                        presentSections: $presentSections,
                        themeKey: $themeKey,
                    ),
                ],
            ],
            layout: LayoutEnum::Results,
        );
    }

    private function detail(string $themeKey): ThemeDemoPageDefinition
    {
        $presentSections = ['lead-dispatch', 'radio-audio', 'travel-culture'];

        return new ThemeDemoPageDefinition(
            surface: 'detail',
            name: self::BRAND . ' Dispatch',
            title: 'The Long Road Out of Tbilisi — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'The long road out of Tbilisi',
                'A reported feature on the people remaking a Caucasus capital, and the culture they carry with them.',
            ),
            renderData: [
                'summary' => 'A reported feature on the people remaking Tbilisi, and the music, food, and writing they carry with them.',
                'navigation' => $this->navigation($presentSections, $themeKey),
                'footer' => $this->footer($presentSections, $themeKey),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Dispatch · Affairs',
                        heading: 'The long road out of Tbilisi',
                        summary: 'Three months in a city caught between empires, told through the cafes, recording studios, and night markets where its next chapter is being written.',
                        primaryLabel: 'Listen to the audio version',
                        primaryUrl: $this->linkTo('radio-audio', $presentSections, $themeKey),
                        secondaryLabel: 'More from Affairs',
                        secondaryUrl: $this->linkTo('lead-dispatch', $presentSections, $themeKey),
                    ),
                    $this->leadDispatchSection(
                        themeKey: $themeKey,
                        heading: 'Reading alongside this story',
                        summary: 'Three more dispatches from the Caucasus desk, chosen by the editors of this feature.',
                        items: [
                            ['title' => 'The wine cellars of Kakheti reopen', 'summary' => 'A harvest report from the valley where eight thousand years of winemaking meet a new export economy.'],
                            ['title' => 'Night buses across the Caucasus', 'summary' => 'Twelve hours between Yerevan and Tbilisi with the traders, students, and musicians who ride them weekly.'],
                            ['title' => 'The polyphony that would not die', 'summary' => 'How Georgian table song survived empire, radio, and exile — and what it sounds like now.'],
                        ],
                    ),
                    $this->radioAudioSection($themeKey),
                    $this->travelCultureSection($themeKey),
                    $this->proofSection(
                        heading: 'Reporting that travels',
                        summary: 'How this magazine covers the world.',
                    ),
                    $this->ctaSection(
                        heading: 'Follow the story as it develops',
                        summary: 'Members get every follow-up dispatch from this desk the moment it publishes.',
                        presentSections: $presentSections,
                        themeKey: $themeKey,
                    ),
                ],
            ],
        );
    }

    private function contact(string $themeKey): ThemeDemoPageDefinition
    {
        $presentSections = ['newsletter'];

        return new ThemeDemoPageDefinition(
            surface: 'contact',
            name: self::BRAND . ' Subscribe',
            title: 'Subscribe — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Join the readers who follow the world with us',
                'Become a member, write to the newsroom, or pitch the desk — one clear path into the magazine.',
            ),
            renderData: [
                'summary' => 'Become a member, write to the newsroom, or pitch a story — one confident path into The Global Review.',
                'navigation' => $this->navigation($presentSections, $themeKey),
                'footer' => $this->footer($presentSections, $themeKey),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Subscribe',
                        heading: 'One confident path into the magazine',
                        summary: 'Members read everything, hear the full audio archive, and get the Saturday culture edition. Write to us at desk@theglobalreview.example — we read every message.',
                        primaryLabel: 'Become a member',
                        primaryUrl: $this->linkTo('newsletter', $presentSections, $themeKey),
                        secondaryLabel: 'Email the newsroom',
                        secondaryUrl: 'mailto:desk@theglobalreview.example',
                    ),
                    $this->newsletterSection(
                        heading: 'Start with the morning dispatch',
                        summary: 'A free daily email to see how we read the world before you commit to membership.',
                    ),
                    $this->proofSection(
                        heading: 'What membership opens',
                        summary: 'What you can expect from the day you join.',
                    ),
                    $this->ctaSection(
                        heading: 'Ready to read without limits?',
                        summary: 'Membership is the price of a coffee a month and supports independent reporting from every continent.',
                        presentSections: $presentSections,
                        themeKey: $themeKey,
                    ),
                ],
            ],
            layout: LayoutEnum::System,
        );
    }

    private function emptyState(string $themeKey): ThemeDemoPageDefinition
    {
        $presentSections = ['columnists', 'city-guides'];

        return new ThemeDemoPageDefinition(
            surface: 'empty',
            name: self::BRAND . ' No Results',
            title: 'Nothing in this section yet — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'Nothing in this section yet',
                'A graceful empty state for a filtered archive view with no matching stories.',
            ),
            renderData: [
                'summary' => 'No stories match that filter yet — but there is always something worth reading here.',
                'navigation' => $this->navigation($presentSections, $themeKey),
                'footer' => $this->footer($presentSections, $themeKey),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'The archive',
                        heading: 'No stories match that filter — yet',
                        summary: 'We have not published in this corner of the archive. Clear the filter to read everything, or start with what the desk is reading now.',
                        primaryLabel: 'Read the lead dispatch',
                        primaryUrl: $this->linkTo('lead-dispatch', $presentSections, $themeKey),
                        secondaryLabel: 'Browse city guides',
                        secondaryUrl: $this->linkTo('city-guides', $presentSections, $themeKey),
                    ),
                    $this->columnistsSection($themeKey),
                    $this->cityGuidesSection($themeKey),
                    $this->ctaSection(
                        heading: 'Looking for a particular story?',
                        summary: 'Tell the desk what you are after and we will point you to the right dispatch.',
                        presentSections: $presentSections,
                        themeKey: $themeKey,
                    ),
                ],
            ],
        );
    }

    private function notFound(string $themeKey): ThemeDemoPageDefinition
    {
        $presentSections = [];

        return new ThemeDemoPageDefinition(
            surface: 'not-found',
            name: self::BRAND . ' 404',
            title: 'Page not found — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-404',
            content: $this->prose(
                'This page has gone to press elsewhere',
                'A not-found page that routes readers back into the magazine and the subscribe path.',
            ),
            renderData: [
                'summary' => 'That page has moved or never ran — here is the way back into the magazine.',
                'navigation' => $this->navigation($presentSections, $themeKey),
                'footer' => $this->footer($presentSections, $themeKey),
                'sections' => [
                    $this->heroSection(
                        eyebrow: '404',
                        heading: 'This page has gone to press elsewhere',
                        summary: 'The link is broken or the story has been moved. Head back to the front page, or pick up the latest dispatch.',
                        primaryLabel: 'Back to the front page',
                        primaryUrl: '/theme-' . $themeKey,
                        secondaryLabel: 'Read the lead dispatch',
                        secondaryUrl: $this->linkTo('lead-dispatch', $presentSections, $themeKey),
                    ),
                    $this->ctaSection(
                        heading: 'Still looking for a story?',
                        summary: 'Subscribe and we will send the morning dispatch straight to you.',
                        presentSections: $presentSections,
                        themeKey: $themeKey,
                    ),
                ],
            ],
            type: PageTypeEnum::NotFound,
            layout: LayoutEnum::System,
        );
    }

    private function cta(string $themeKey): ThemeDemoPageDefinition
    {
        $presentSections = ['newsletter'];

        return new ThemeDemoPageDefinition(
            surface: 'cta',
            name: self::BRAND . ' Membership',
            title: 'Become a member — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Read the world without limits',
                'A focused conversion page inviting readers to become members of The Global Review.',
            ),
            renderData: [
                'summary' => 'Read the world without limits — become a member of The Global Review.',
                'navigation' => $this->navigation($presentSections, $themeKey),
                'footer' => $this->footer($presentSections, $themeKey),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Membership',
                        heading: 'Read the world without limits',
                        summary: 'Unlimited dispatches, the full radio archive, the Saturday culture edition, and the satisfaction of funding independent reporting.',
                        primaryLabel: 'Become a member',
                        primaryUrl: $this->linkTo('newsletter', $presentSections, $themeKey),
                        secondaryLabel: 'See what is inside',
                        secondaryUrl: $this->linkTo('radio-audio', $presentSections, $themeKey),
                    ),
                    $this->proofSection(
                        heading: 'Why readers join',
                        summary: 'What members tell us keeps them subscribed.',
                    ),
                    $this->newsletterSection(
                        heading: 'Try the morning dispatch first',
                        summary: 'A free daily email — no card required — to see how we read the world.',
                    ),
                    $this->ctaSection(
                        heading: 'Join The Global Review today',
                        summary: 'A coffee a month, cancel any time, and every story we publish is yours.',
                        presentSections: $presentSections,
                        themeKey: $themeKey,
                    ),
                ],
            ],
        );
    }

    /**
     * Resolve a section link for a surface: an in-page anchor when the target
     * section renders on that surface, otherwise the demo page that carries it.
     * Section anchors match their section type, the newsletter form lives on
     * the subscribe page, and every other section renders on the homepage.
     *
     * @param  list<string>  $presentSections
     */
    private function linkTo(string $sectionType, array $presentSections, string $themeKey): string
    {
        if (in_array($sectionType, $presentSections, true)) {
            return '#' . $sectionType;
        }

        $carrierSlug = $sectionType === 'newsletter'
            ? 'theme-' . $themeKey . '-contact'
            : 'theme-' . $themeKey;

        return '/' . $carrierSlug . '#' . $sectionType;
    }

    private function detailUrl(string $themeKey): string
    {
        return '/theme-' . $themeKey . '-detail';
    }

    /**
     * @return array<string, mixed>
     */
    private function heroSection(
        string $eyebrow,
        string $heading,
        string $summary,
        string $primaryLabel,
        string $primaryUrl,
        string $secondaryLabel,
        string $secondaryUrl,
    ): array {
        return [
            'type' => 'hero',
            'eyebrow' => $eyebrow,
            'heading' => $heading,
            'summary' => $summary,
            'primary_label' => $primaryLabel,
            'primary_url' => $primaryUrl,
            'secondary_label' => $secondaryLabel,
            'secondary_url' => $secondaryUrl,
            'actions' => [
                ['label' => $primaryLabel, 'url' => $primaryUrl, 'style' => 'primary'],
                ['label' => $secondaryLabel, 'url' => $secondaryUrl, 'style' => 'secondary'],
            ],
        ];
    }

    /**
     * @param  list<array{title: string, summary: string}>|null  $items
     * @return array<string, mixed>
     */
    private function leadDispatchSection(
        string $themeKey,
        string $heading = 'The lead dispatch',
        string $summary = 'The reporting we led with this week, chosen by the editors.',
        ?array $items = null,
    ): array {
        $items ??= [
            ['title' => 'Inside the new Lagos sound', 'summary' => 'How a generation of producers turned a power-cut city into the loudest studio on the continent.'],
            ['title' => 'The river that moved a border', 'summary' => 'A reported feature on the families caught between two states as a map quietly redraws itself.'],
            ['title' => 'What the archive remembers', 'summary' => 'A historian opens a century of letters and finds the war nobody wrote down.'],
        ];

        $cards = [];

        foreach ($items as $item) {
            $cards[] = [
                ...$item,
                'meta' => 'Affairs',
                'url' => $this->detailUrl($themeKey),
            ];
        }

        return [
            'type' => 'lead-dispatch',
            'heading' => $heading,
            'summary' => $summary,
            'items' => $cards,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function radioAudioSection(string $themeKey): array
    {
        return [
            'type' => 'radio-audio',
            'heading' => 'Radio & audio',
            'summary' => 'Every dispatch, read aloud — plus the interviews and field recordings that did not fit on the page.',
            'label' => 'Open the audio archive',
            'url' => '/theme-' . $themeKey . '-directory',
            'items' => [
                ['title' => 'The Tbilisi tapes', 'summary' => 'Forty minutes of street sound and conversation from our Caucasus correspondent.', 'url' => $this->detailUrl($themeKey)],
                ['title' => 'A history of the protest song', 'summary' => 'Three episodes tracing how a melody crosses a border faster than any reporter.', 'url' => $this->detailUrl($themeKey)],
                ['title' => 'The kitchen interview', 'summary' => 'Chefs in five cities on what their menus say about the year just gone.', 'url' => $this->detailUrl($themeKey)],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function cityGuidesSection(string $themeKey): array
    {
        return [
            'type' => 'city-guides',
            'heading' => 'City guides',
            'summary' => 'Where our correspondents eat, walk, and listen — written by the people who actually live there.',
            'items' => [
                ['title' => 'Mexico City after dark', 'summary' => 'The cantinas, listening bars, and late markets our writer returns to on every trip.', 'url' => $this->detailUrl($themeKey)],
                ['title' => 'Lisbon, beyond the miradouros', 'summary' => 'A guide to the city behind the postcards, from the docks to the hill kitchens.', 'url' => $this->detailUrl($themeKey)],
                ['title' => 'A weekend in Hanoi', 'summary' => 'Two days of coffee, archive, and noodle stalls mapped by our Southeast Asia desk.', 'url' => $this->detailUrl($themeKey)],
                ['title' => 'Reykjavik in the long dark', 'summary' => 'Where to read, swim, and hear music when the sun barely clears the horizon.', 'url' => $this->detailUrl($themeKey)],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function travelCultureSection(string $themeKey): array
    {
        return [
            'type' => 'travel-culture',
            'heading' => 'Travel & culture',
            'summary' => 'Criticism, reportage, and the cultural arguments worth having from the places they are happening.',
            'items' => [
                ['title' => 'The biennale nobody expected', 'meta' => 'Culture', 'summary' => 'A small port city stages the most talked-about art show of the year.', 'care_note' => 'Long read · 14 min', 'url' => $this->detailUrl($themeKey)],
                ['title' => 'On the night train again', 'meta' => 'Travel', 'summary' => 'Why a slower way of crossing Europe is suddenly the only way worth writing about.', 'care_note' => 'Reported feature', 'url' => $this->detailUrl($themeKey)],
                ['title' => 'The translator’s dilemma', 'meta' => 'Books', 'summary' => 'What is lost, and quietly gained, when a novel crosses a language.', 'care_note' => 'Essay', 'url' => $this->detailUrl($themeKey)],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function shopBooksSection(string $themeKey): array
    {
        return [
            'type' => 'shop-books',
            'heading' => 'The reading list',
            'summary' => 'The books our writers reviewed this month, and the back issues worth keeping on the shelf.',
            'items' => [
                ['title' => 'The Sea Between Us', 'meta' => 'Reportage', 'summary' => 'A correspondent’s account of a decade covering the world’s busiest strait.', 'url' => $this->detailUrl($themeKey)],
                ['title' => 'Notes on a Borrowed City', 'meta' => 'Essays', 'summary' => 'Twelve essays on belonging, place, and the cities that adopt us.', 'url' => $this->detailUrl($themeKey)],
                ['title' => 'The Global Review: Year in Print', 'meta' => 'Annual', 'summary' => 'The best of twelve months of reporting, bound and ready for the shelf.', 'url' => $this->detailUrl($themeKey)],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function columnistsSection(string $themeKey): array
    {
        return [
            'type' => 'columnists',
            'heading' => 'The columnists',
            'summary' => 'The regular voices our readers argue with, agree with, and come back to every week.',
            'items' => [
                ['title' => 'Amara Okonkwo on the new economics', 'summary' => 'A weekly column on the money stories hiding inside the culture stories.', 'url' => $this->detailUrl($themeKey)],
                ['title' => 'Daniel Rourke from the road', 'summary' => 'Dispatches from wherever the night train happens to stop next.', 'url' => $this->detailUrl($themeKey)],
                ['title' => 'Mei Lin on the screen', 'summary' => 'What the world is watching, and what it says about the year we are having.', 'url' => $this->detailUrl($themeKey)],
            ],
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
            'label' => 'Subscribe to the dispatch',
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
                ['value' => '140', 'label' => 'Countries where the magazine is read each month.'],
                ['value' => '38', 'label' => 'Correspondents filing from the field, on six continents.'],
                ['value' => 'Daily', 'label' => 'A new lead dispatch, every morning before breakfast.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function contentListingSection(string $heading, string $summary, string $themeKey): array
    {
        return [
            'type' => 'content-listing',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'The long road out of Tbilisi', 'category' => 'Affairs', 'summary' => 'A reported feature on the people remaking a Caucasus capital.', 'url' => $this->detailUrl($themeKey)],
                ['title' => 'Mexico City after dark', 'category' => 'City guides', 'summary' => 'The cantinas and late markets our writer returns to every trip.', 'url' => $this->detailUrl($themeKey)],
                ['title' => 'The Tbilisi tapes', 'category' => 'Radio', 'summary' => 'Forty minutes of street sound from our Caucasus correspondent.', 'url' => $this->detailUrl($themeKey)],
                ['title' => 'On the night train again', 'category' => 'Travel', 'summary' => 'Why a slower way of crossing Europe is the only way worth writing about.', 'url' => $this->detailUrl($themeKey)],
                ['title' => 'The biennale nobody expected', 'category' => 'Culture', 'summary' => 'A small port city stages the year’s most talked-about art show.', 'url' => $this->detailUrl($themeKey)],
                ['title' => 'Amara Okonkwo on the new economics', 'category' => 'Columnists', 'summary' => 'The money stories hiding inside the culture stories.', 'url' => $this->detailUrl($themeKey)],
            ],
        ];
    }

    /**
     * @param  list<string>  $presentSections
     * @return array<string, mixed>
     */
    private function ctaSection(string $heading, string $summary, array $presentSections, string $themeKey): array
    {
        $memberUrl = $this->linkTo('newsletter', $presentSections, $themeKey);
        $dispatchUrl = $this->linkTo('lead-dispatch', $presentSections, $themeKey);

        return [
            'type' => 'cta',
            'heading' => $heading,
            'summary' => $summary,
            'label' => 'Become a member',
            'url' => $memberUrl,
            'actions' => [
                ['label' => 'Become a member', 'url' => $memberUrl, 'style' => 'primary'],
                ['label' => 'Read the lead dispatch', 'url' => $dispatchUrl, 'style' => 'secondary'],
            ],
        ];
    }

    /**
     * @param  list<string>  $presentSections
     * @return array<string, mixed>
     */
    private function navigation(array $presentSections, string $themeKey): array
    {
        $items = [
            ['label' => 'Affairs', 'url' => $this->linkTo('lead-dispatch', $presentSections, $themeKey)],
            ['label' => 'Travel & culture', 'url' => $this->linkTo('travel-culture', $presentSections, $themeKey)],
            ['label' => 'Radio', 'url' => $this->linkTo('radio-audio', $presentSections, $themeKey)],
            ['label' => 'City guides', 'url' => $this->linkTo('city-guides', $presentSections, $themeKey)],
            ['label' => 'Columnists', 'url' => $this->linkTo('columnists', $presentSections, $themeKey)],
        ];

        $subscribeUrl = $this->linkTo('newsletter', $presentSections, $themeKey);

        return [
            'brandName' => self::BRAND,
            'brand' => self::BRAND,
            'items' => $items,
            'ctaLabel' => 'Subscribe',
            'ctaUrl' => $subscribeUrl,
            'consultationUrl' => $subscribeUrl,
        ];
    }

    /**
     * @param  list<string>  $presentSections
     * @return array<string, mixed>
     */
    private function footer(array $presentSections, string $themeKey): array
    {
        $columns = [
            [
                'title' => 'Sections',
                'heading' => 'Sections',
                'links' => [
                    ['label' => 'Affairs', 'url' => $this->linkTo('lead-dispatch', $presentSections, $themeKey)],
                    ['label' => 'Travel & culture', 'url' => $this->linkTo('travel-culture', $presentSections, $themeKey)],
                    ['label' => 'Radio & audio', 'url' => $this->linkTo('radio-audio', $presentSections, $themeKey)],
                    ['label' => 'City guides', 'url' => $this->linkTo('city-guides', $presentSections, $themeKey)],
                ],
            ],
            [
                'title' => 'The magazine',
                'heading' => 'The magazine',
                'links' => [
                    ['label' => 'Columnists', 'url' => $this->linkTo('columnists', $presentSections, $themeKey)],
                    ['label' => 'The reading list', 'url' => $this->linkTo('shop-books', $presentSections, $themeKey)],
                    ['label' => 'The archive', 'url' => '/theme-' . $themeKey . '-directory'],
                    ['label' => 'About us', 'url' => '/theme-' . $themeKey . '-contact'],
                ],
            ],
            [
                'title' => 'Read with us',
                'heading' => 'Read with us',
                'links' => [
                    ['label' => 'Become a member', 'url' => $this->linkTo('newsletter', $presentSections, $themeKey)],
                    ['label' => 'desk@theglobalreview.example', 'url' => 'mailto:desk@theglobalreview.example'],
                ],
            ],
        ];

        return [
            'brandName' => self::BRAND,
            'brand' => self::BRAND,
            'summary' => 'An independent global magazine. Reported from every continent, read in 140 countries.',
            'items' => $columns,
            'columns' => $columns,
        ];
    }

    private function prose(string $heading, string $summary): string
    {
        return sprintf('<h2>%s</h2><p>%s</p>', e($heading), e($summary));
    }
}
