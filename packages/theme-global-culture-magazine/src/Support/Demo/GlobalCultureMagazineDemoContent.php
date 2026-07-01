<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\GlobalCultureMagazine\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
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
 * Navigation and footer chrome are seeded in both key shapes: the strict demo
 * contract reads `navigation.brandName` / `navigation.items` / `footer.columns`,
 * while the rendered blade reads `navigation.brand` / `footer.items`. Both are
 * populated so the contract and the live render agree.
 */
final class GlobalCultureMagazineDemoContent implements ProvidesThemeDemoContent
{
    private const string BRAND = 'The Global Review';

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
            $this->emptyState($themeKey, $media),
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
            title: self::BRAND . ' — A Global Magazine for Stories That Matter',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'A global magazine for the stories that matter',
                'The Global Review is an editorial home for world affairs, travel and culture, radio and audio, city guides, columnists, and the books worth reading next.',
            ),
            renderData: [
                'summary' => 'The Global Review is an editorial magazine covering world affairs, travel and culture, radio, city guides, and the columnists shaping the conversation.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'The Global Review',
                        heading: 'A global magazine for the stories that matter',
                        summary: 'Long-form reporting, dispatches from the field, and the cultural criticism that makes sense of a fast-moving world — published daily, read everywhere.',
                        primaryLabel: 'Read the lead dispatch',
                        primaryUrl: '#lead-dispatch',
                        secondaryLabel: 'Browse city guides',
                        secondaryUrl: '#city-guides',
                        mediaUrl: $media['hero'][0],
                        mediaAlt: 'The Global Review newsroom',
                    ),
                    $this->leadDispatchSection($media),
                    $this->radioAudioSection(),
                    $this->cityGuidesSection(),
                    $this->travelCultureSection(),
                    $this->shopBooksSection(),
                    $this->columnistsSection(),
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
            name: self::BRAND . ' Archive',
            title: 'The Archive — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'The archive, built to be scanned',
                'Every dispatch, city guide, radio episode, and column in one structured, scannable index.',
            ),
            renderData: [
                'summary' => 'Browse the full magazine — affairs, travel and culture, radio, city guides, and columnists — in one structured archive.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'The archive',
                        heading: 'An archive built to be scanned',
                        summary: 'Filter by desk, region, or format. Read the long-form story behind every dispatch, episode, and guide we have published.',
                        primaryLabel: 'Browse city guides',
                        primaryUrl: '#city-guides',
                        secondaryLabel: 'Subscribe',
                        secondaryUrl: '#newsletter',
                        mediaUrl: $media['listing'][0] ?? $media['hero'][0],
                        mediaAlt: 'The Global Review archive',
                    ),
                    $this->contentListingSection(
                        heading: 'Across every desk',
                        summary: 'Affairs, travel, culture, and radio kept legible in one premium grid.',
                    ),
                    $this->cityGuidesSection(),
                    $this->columnistsSection(),
                    $this->ctaSection(
                        heading: 'Read the whole archive without limits',
                        summary: 'Membership opens every dispatch, guide, and episode we have ever published.',
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
            name: self::BRAND . ' Dispatch',
            title: 'The Long Road Out of Tbilisi — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'The long road out of Tbilisi',
                'A reported feature on the people remaking a Caucasus capital, and the culture they carry with them.',
            ),
            renderData: [
                'summary' => 'A reported feature on the people remaking Tbilisi, and the music, food, and writing they carry with them.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Dispatch · Affairs',
                        heading: 'The long road out of Tbilisi',
                        summary: 'Three months in a city caught between empires, told through the cafes, recording studios, and night markets where its next chapter is being written.',
                        primaryLabel: 'Listen to the audio version',
                        primaryUrl: '#radio-audio',
                        secondaryLabel: 'More from Affairs',
                        secondaryUrl: '#lead-dispatch',
                        mediaUrl: $media['detail'][0],
                        mediaAlt: 'A street in Tbilisi at dusk',
                    ),
                    $this->leadDispatchSection($media, heading: 'Reading alongside this story'),
                    $this->radioAudioSection(),
                    $this->travelCultureSection(),
                    $this->proofSection(
                        heading: 'Reporting that travels',
                        summary: 'How this magazine covers the world.',
                    ),
                    $this->ctaSection(
                        heading: 'Follow the story as it develops',
                        summary: 'Members get every follow-up dispatch from this desk the moment it publishes.',
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
                'Join the readers who follow the world with us',
                'Become a member, write to the newsroom, or pitch the desk — one clear path into the magazine.',
            ),
            renderData: [
                'summary' => 'Become a member, write to the newsroom, or pitch a story — one confident path into The Global Review.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Subscribe',
                        heading: 'One confident path into the magazine',
                        summary: 'Members read everything, hear the full audio archive, and get the Saturday culture edition. Write to us at desk@theglobalreview.example — we read every message.',
                        primaryLabel: 'Become a member',
                        primaryUrl: '#newsletter',
                        secondaryLabel: 'Email the newsroom',
                        secondaryUrl: 'mailto:desk@theglobalreview.example',
                        mediaUrl: $media['contact'][0],
                        mediaAlt: 'The Global Review editorial desk',
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
                    ),
                ],
            ],
            layout: LayoutEnum::System,
        );
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     */
    private function emptyState(string $themeKey, array $media): ThemeDemoPageDefinition
    {
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
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'The archive',
                        heading: 'No stories match that filter — yet',
                        summary: 'We have not published in this corner of the archive. Clear the filter to read everything, or start with what the desk is reading now.',
                        primaryLabel: 'Read the lead dispatch',
                        primaryUrl: '#lead-dispatch',
                        secondaryLabel: 'Browse city guides',
                        secondaryUrl: '#city-guides',
                        mediaUrl: $media['hero'][0],
                        mediaAlt: 'An empty reading room',
                    ),
                    $this->columnistsSection(),
                    $this->cityGuidesSection(),
                    $this->ctaSection(
                        heading: 'Looking for a particular story?',
                        summary: 'Tell the desk what you are after and we will point you to the right dispatch.',
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
                'This page has gone to press elsewhere',
                'A not-found page that routes readers back into the magazine and the subscribe path.',
            ),
            renderData: [
                'summary' => 'That page has moved or never ran — here is the way back into the magazine.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: '404',
                        heading: 'This page has gone to press elsewhere',
                        summary: 'The link is broken or the story has been moved. Head back to the front page, or pick up the latest dispatch.',
                        primaryLabel: 'Back to the front page',
                        primaryUrl: '/',
                        secondaryLabel: 'Read the lead dispatch',
                        secondaryUrl: '#lead-dispatch',
                        mediaUrl: $media['hero'][0],
                        mediaAlt: 'A printing press',
                    ),
                    $this->ctaSection(
                        heading: 'Still looking for a story?',
                        summary: 'Subscribe and we will send the morning dispatch straight to you.',
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
                'Read the world without limits',
                'A focused conversion page inviting readers to become members of The Global Review.',
            ),
            renderData: [
                'summary' => 'Read the world without limits — become a member of The Global Review.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Membership',
                        heading: 'Read the world without limits',
                        summary: 'Unlimited dispatches, the full radio archive, the Saturday culture edition, and the satisfaction of funding independent reporting.',
                        primaryLabel: 'Become a member',
                        primaryUrl: '#newsletter',
                        secondaryLabel: 'See what is inside',
                        secondaryUrl: '#radio-audio',
                        mediaUrl: $media['cta'][0],
                        mediaAlt: 'Readers of The Global Review',
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
                    ),
                ],
            ],
        );
    }

    private function heroSection(
        string $eyebrow,
        string $heading,
        string $summary,
        string $primaryLabel,
        string $primaryUrl,
        string $secondaryLabel,
        string $secondaryUrl,
        ?string $mediaUrl,
        string $mediaAlt,
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
            'mediaUrl' => $mediaUrl,
            'mediaAlt' => $mediaAlt,
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function leadDispatchSection(array $media, string $heading = 'The lead dispatch'): array
    {
        $images = array_values(array_unique(array_merge($media['listing'], $media['detail'])));

        $items = [
            ['title' => 'Inside the new Lagos sound', 'summary' => 'How a generation of producers turned a power-cut city into the loudest studio on the continent.'],
            ['title' => 'The river that moved a border', 'summary' => 'A reported feature on the families caught between two states as a map quietly redraws itself.'],
            ['title' => 'What the archive remembers', 'summary' => 'A historian opens a century of letters and finds the war nobody wrote down.'],
        ];

        $cards = [];

        foreach ($items as $index => $item) {
            $cards[] = [
                ...$item,
                'meta' => 'Affairs',
                'url' => '#dispatch-' . ($index + 1),
                'image' => $images[$index % max(count($images), 1)] ?? null,
                'imageUrl' => $images[$index % max(count($images), 1)] ?? null,
            ];
        }

        return [
            'type' => 'lead-dispatch',
            'heading' => $heading,
            'summary' => 'The reporting we led with this week, chosen by the editors.',
            'items' => $cards,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function radioAudioSection(): array
    {
        return [
            'type' => 'radio-audio',
            'heading' => 'Radio & audio',
            'summary' => 'Every dispatch, read aloud — plus the interviews and field recordings that did not fit on the page.',
            'label' => 'Open the audio archive',
            'url' => '#radio-audio',
            'items' => [
                ['title' => 'The Tbilisi tapes', 'summary' => 'Forty minutes of street sound and conversation from our Caucasus correspondent.'],
                ['title' => 'A history of the protest song', 'summary' => 'Three episodes tracing how a melody crosses a border faster than any reporter.'],
                ['title' => 'The kitchen interview', 'summary' => 'Chefs in five cities on what their menus say about the year just gone.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function cityGuidesSection(): array
    {
        return [
            'type' => 'city-guides',
            'heading' => 'City guides',
            'summary' => 'Where our correspondents eat, walk, and listen — written by the people who actually live there.',
            'items' => [
                ['title' => 'Mexico City after dark', 'summary' => 'The cantinas, listening bars, and late markets our writer returns to on every trip.'],
                ['title' => 'Lisbon, beyond the miradouros', 'summary' => 'A guide to the city behind the postcards, from the docks to the hill kitchens.'],
                ['title' => 'A weekend in Hanoi', 'summary' => 'Two days of coffee, archive, and noodle stalls mapped by our Southeast Asia desk.'],
                ['title' => 'Reykjavik in the long dark', 'summary' => 'Where to read, swim, and hear music when the sun barely clears the horizon.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function travelCultureSection(): array
    {
        return [
            'type' => 'travel-culture',
            'heading' => 'Travel & culture',
            'summary' => 'Criticism, reportage, and the cultural arguments worth having from the places they are happening.',
            'items' => [
                ['title' => 'The biennale nobody expected', 'meta' => 'Culture', 'summary' => 'A small port city stages the most talked-about art show of the year.', 'care_note' => 'Long read · 14 min'],
                ['title' => 'On the night train again', 'meta' => 'Travel', 'summary' => 'Why a slower way of crossing Europe is suddenly the only way worth writing about.', 'care_note' => 'Reported feature'],
                ['title' => 'The translator’s dilemma', 'meta' => 'Books', 'summary' => 'What is lost, and quietly gained, when a novel crosses a language.', 'care_note' => 'Essay'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function shopBooksSection(): array
    {
        return [
            'type' => 'shop-books',
            'heading' => 'The reading list',
            'summary' => 'The books our writers reviewed this month, and the back issues worth keeping on the shelf.',
            'items' => [
                ['title' => 'The Sea Between Us', 'meta' => 'Reportage', 'summary' => 'A correspondent’s account of a decade covering the world’s busiest strait.'],
                ['title' => 'Notes on a Borrowed City', 'meta' => 'Essays', 'summary' => 'Twelve essays on belonging, place, and the cities that adopt us.'],
                ['title' => 'The Global Review: Year in Print', 'meta' => 'Annual', 'summary' => 'The best of twelve months of reporting, bound and ready for the shelf.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function columnistsSection(): array
    {
        return [
            'type' => 'columnists',
            'heading' => 'The columnists',
            'summary' => 'The regular voices our readers argue with, agree with, and come back to every week.',
            'items' => [
                ['title' => 'Amara Okonkwo on the new economics', 'summary' => 'A weekly column on the money stories hiding inside the culture stories.'],
                ['title' => 'Daniel Rourke from the road', 'summary' => 'Dispatches from wherever the night train happens to stop next.'],
                ['title' => 'Mei Lin on the screen', 'summary' => 'What the world is watching, and what it says about the year we are having.'],
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
    private function contentListingSection(string $heading, string $summary): array
    {
        return [
            'type' => 'content-listing',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'The long road out of Tbilisi', 'category' => 'Affairs', 'summary' => 'A reported feature on the people remaking a Caucasus capital.'],
                ['title' => 'Mexico City after dark', 'category' => 'City guides', 'summary' => 'The cantinas and late markets our writer returns to every trip.'],
                ['title' => 'The Tbilisi tapes', 'category' => 'Radio', 'summary' => 'Forty minutes of street sound from our Caucasus correspondent.'],
                ['title' => 'On the night train again', 'category' => 'Travel', 'summary' => 'Why a slower way of crossing Europe is the only way worth writing about.'],
                ['title' => 'The biennale nobody expected', 'category' => 'Culture', 'summary' => 'A small port city stages the year’s most talked-about art show.'],
                ['title' => 'Amara Okonkwo on the new economics', 'category' => 'Columnists', 'summary' => 'The money stories hiding inside the culture stories.'],
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
            'label' => 'Become a member',
            'url' => '#newsletter',
            'actions' => [
                ['label' => 'Become a member', 'url' => '#newsletter', 'style' => 'primary'],
                ['label' => 'Read the lead dispatch', 'url' => '#lead-dispatch', 'style' => 'secondary'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function navigation(): array
    {
        $items = [
            ['label' => 'Affairs', 'url' => '#lead-dispatch'],
            ['label' => 'Travel & culture', 'url' => '#travel-culture'],
            ['label' => 'Radio', 'url' => '#radio-audio'],
            ['label' => 'City guides', 'url' => '#city-guides'],
            ['label' => 'Columnists', 'url' => '#columnists'],
        ];

        return [
            'brandName' => self::BRAND,
            'brand' => self::BRAND,
            'items' => $items,
            'ctaLabel' => 'Subscribe',
            'ctaUrl' => '#newsletter',
            'consultationUrl' => '#newsletter',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        $columns = [
            [
                'title' => 'Sections',
                'heading' => 'Sections',
                'links' => [
                    ['label' => 'Affairs', 'url' => '#lead-dispatch'],
                    ['label' => 'Travel & culture', 'url' => '#travel-culture'],
                    ['label' => 'Radio & audio', 'url' => '#radio-audio'],
                    ['label' => 'City guides', 'url' => '#city-guides'],
                ],
            ],
            [
                'title' => 'The magazine',
                'heading' => 'The magazine',
                'links' => [
                    ['label' => 'Columnists', 'url' => '#columnists'],
                    ['label' => 'The reading list', 'url' => '#shop-books'],
                    ['label' => 'The archive', 'url' => '#lead-dispatch'],
                    ['label' => 'About us', 'url' => '#columnists'],
                ],
            ],
            [
                'title' => 'Read with us',
                'heading' => 'Read with us',
                'links' => [
                    ['label' => 'Become a member', 'url' => '#newsletter'],
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
