<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\PodcastShow\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Podcast Show theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the
 * page adapter emits the theme's signature podcast renderers (latest-episode /
 * episode-list / subscribe-platforms / hosts / guests) alongside the standard
 * hero / proof / cta — giving every surface a full audio-show site rather than
 * the shared five-section skeleton.
 */
final class PodcastShowDemoContent implements ProvidesThemeDemoContent
{
    private const string BRAND = 'The Long Game';

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
            title: self::BRAND . ' — Conversations on building things that last',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'A show worth subscribing to before the next episode drops',
                'The Long Game is a weekly interview show about founders, builders, and operators playing for the long haul.',
            ),
            renderData: [
                'summary' => 'The Long Game is a weekly interview show about the people building companies, products, and careers for the long haul. New episodes every Tuesday.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Weekly interview podcast',
                        'heading' => 'A show worth subscribing to before the next episode drops',
                        'summary' => 'Every Tuesday we sit down with the founders, builders, and operators playing for the long haul — and unpack exactly how they did it.',
                        'actions' => [
                            ['label' => 'Subscribe', 'url' => '#subscribe', 'style' => 'primary'],
                            ['label' => 'Browse episodes', 'url' => '#episodes', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['hero'][0],
                        'mediaAlt' => 'The Long Game studio microphone',
                    ],
                    $this->latestEpisodeSection($media),
                    $this->episodeListSection(
                        heading: 'Recent episodes',
                        summary: 'Catch up on the conversations listeners are sharing this month.',
                    ),
                    $this->subscribePlatformsSection(),
                    $this->hostsSection(
                        heading: 'Your hosts',
                        summary: 'Two operators who have built and sold companies, asking the questions they wish someone had asked them.',
                    ),
                    $this->guestsSection(
                        heading: 'Guests you will hear from',
                        summary: 'Founders, investors, and makers who have built things that outlasted the hype cycle.',
                    ),
                    $this->proofSection(
                        heading: 'A show people actually finish',
                        summary: 'The numbers behind four seasons of The Long Game.',
                    ),
                    $this->ctaSection(
                        heading: 'Subscribe and never miss an episode',
                        summary: 'New episodes land every Tuesday at 6am. Follow the show wherever you listen and get each one the moment it drops.',
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
            name: self::BRAND . ' Episodes',
            title: 'Episodes — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'The full episode archive, built to be scanned',
                'Every episode of The Long Game across four seasons, newest first.',
            ),
            renderData: [
                'summary' => 'The complete episode archive across four seasons of The Long Game. Filter by season or search by guest.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Episode archive',
                        'heading' => 'The full episode archive, built to be scanned',
                        'summary' => 'Four seasons, more than ninety conversations. Browse the whole catalogue, newest first, or jump straight to a guest you already follow.',
                        'actions' => [
                            ['label' => 'Subscribe', 'url' => '#subscribe', 'style' => 'primary'],
                        ],
                        'mediaUrl' => $media['listing'][0] ?? $media['hero'][0],
                        'mediaAlt' => 'The Long Game episode catalogue',
                    ],
                    $this->episodeListSection(
                        heading: 'All episodes',
                        summary: 'Every conversation we have published, newest first.',
                    ),
                    $this->subscribePlatformsSection(),
                    $this->ctaSection(
                        heading: 'Want each new episode automatically?',
                        summary: 'Subscribe once and every future episode arrives in your feed the moment it publishes.',
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
            name: self::BRAND . ' Episode',
            title: 'Episode 92: Building for a decade — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'Episode 92 — Building for a decade with Dana Okonkwo',
                'A show detail page built around the latest episode, with supporting episodes and subscribe paths.',
            ),
            renderData: [
                'summary' => 'Episode 92: Dana Okonkwo on why she turned down three acquisition offers to keep building Cartography for the long haul.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Episode 92 · Season 4',
                        'heading' => 'Building for a decade with Dana Okonkwo',
                        'summary' => 'The Cartography founder on turning down three acquisition offers, surviving a near-death funding gap, and what patience actually costs.',
                        'actions' => [
                            ['label' => 'Play episode', 'url' => '#listen', 'style' => 'primary'],
                            ['label' => 'All episodes', 'url' => '#episodes', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['detail'][0],
                        'mediaAlt' => 'Dana Okonkwo in the studio',
                    ],
                    $this->latestEpisodeSection($media),
                    $this->episodeListSection(
                        heading: 'More from this season',
                        summary: 'Other conversations from Season 4 you might have missed.',
                    ),
                    $this->subscribePlatformsSection(),
                    $this->proofSection(
                        heading: 'Why listeners keep coming back',
                        summary: 'What four seasons of conversations have added up to.',
                    ),
                    $this->ctaSection(
                        heading: 'Enjoyed this episode?',
                        summary: 'Subscribe to The Long Game and get every new conversation the moment it drops.',
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
            title: 'Get in touch with the show — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Get in touch with the show',
                'Pitch a guest, ask about sponsorship, or just tell us which episode landed.',
            ),
            renderData: [
                'summary' => 'Pitch a guest, ask about sponsorship, or tell us which episode stuck with you. We read every message.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Contact',
                        'heading' => 'Get in touch with the show',
                        'summary' => 'Email hello@thelonggame.example to pitch a guest, talk sponsorship, or send a listener question we might read on air. We reply within two working days.',
                        'actions' => [
                            ['label' => 'Email the show', 'url' => 'mailto:hello@thelonggame.example', 'style' => 'primary'],
                            ['label' => 'Subscribe', 'url' => '#subscribe', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['contact'][0],
                        'mediaAlt' => 'The Long Game recording desk',
                    ],
                    $this->hostsSection(
                        heading: 'Who you are reaching',
                        summary: 'Both hosts read listener mail and guest pitches personally.',
                    ),
                    $this->subscribePlatformsSection(),
                    $this->proofSection(
                        heading: 'What to expect when you write',
                        summary: 'How we handle guest pitches and listener questions.',
                    ),
                    $this->ctaSection(
                        heading: 'Rather just listen first?',
                        summary: 'Subscribe to the show and get a feel for the conversations before you pitch.',
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
            name: self::BRAND . ' No Episodes',
            title: 'No episodes yet — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'No episodes published yet',
                'A graceful empty state that invites visitors to subscribe before the first episode lands.',
            ),
            renderData: [
                'summary' => 'No episodes match that filter yet — but you can subscribe now and be there for the first one.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Coming soon',
                        'heading' => 'No episodes published here yet',
                        'summary' => 'This season has not started, or no episodes match that filter. Subscribe now and the first one will land in your feed automatically.',
                        'actions' => [
                            ['label' => 'Subscribe', 'url' => '#subscribe', 'style' => 'primary'],
                            ['label' => 'Browse all episodes', 'url' => '#episodes', 'style' => 'secondary'],
                        ],
                    ],
                    [
                        'type' => 'episode-list',
                        'heading' => 'Nothing to play here yet',
                        'summary' => 'When episodes publish in this season they will appear here, newest first.',
                        'items' => [],
                    ],
                    $this->subscribePlatformsSection(),
                    $this->ctaSection(
                        heading: 'Be there for episode one',
                        summary: 'Subscribe today and you will not miss the first conversation when it drops.',
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
            title: 'Episode not found — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-404',
            content: $this->prose(
                'That episode could not be found',
                'A not-found page that keeps listeners moving back into the catalogue.',
            ),
            renderData: [
                'summary' => 'That episode has moved or never existed — here is the way back into the catalogue.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => '404',
                        'heading' => 'That episode could not be found',
                        'summary' => 'The link is broken or the episode has moved. Head back to the archive, or subscribe so the next one finds you instead.',
                        'actions' => [
                            ['label' => 'Back to home', 'url' => '/', 'style' => 'primary'],
                            ['label' => 'Browse episodes', 'url' => '#episodes', 'style' => 'secondary'],
                        ],
                    ],
                    $this->episodeListSection(
                        heading: 'Popular episodes to start with',
                        summary: 'The conversations new listeners reach for first.',
                    ),
                    $this->ctaSection(
                        heading: 'Lost the thread?',
                        summary: 'Subscribe to The Long Game and let the next episode come to you.',
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
            name: self::BRAND . ' Subscribe',
            title: 'Subscribe to the show — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Subscribe and never miss an episode',
                'A focused conversion page pairing subscribe platforms with proof.',
            ),
            renderData: [
                'summary' => 'Subscribe and never miss an episode of The Long Game. New conversations every Tuesday.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Subscribe',
                        'heading' => 'Subscribe and never miss an episode',
                        'summary' => 'Join more than forty thousand listeners who get every new conversation the moment it drops, wherever they already listen.',
                        'actions' => [
                            ['label' => 'Subscribe now', 'url' => '#subscribe', 'style' => 'primary'],
                            ['label' => 'Browse episodes', 'url' => '#episodes', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['cta'][0],
                        'mediaAlt' => 'The Long Game podcast cover',
                    ],
                    $this->subscribePlatformsSection(),
                    $this->proofSection(
                        heading: 'Why listeners subscribe',
                        summary: 'What keeps forty thousand people coming back every Tuesday.',
                    ),
                    $this->ctaSection(
                        heading: 'One tap and you are in',
                        summary: 'Follow The Long Game on your platform of choice and the next episode finds you automatically.',
                    ),
                ],
            ],
        );
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function latestEpisodeSection(array $media): array
    {
        return [
            'type' => 'latest-episode',
            'heading' => 'Latest episode',
            'summary' => 'Episode 92: Dana Okonkwo on building Cartography for a decade and turning down three acquisitions.',
            'mediaUrl' => $media['detail'][0],
            'mediaAlt' => 'Dana Okonkwo on The Long Game',
            'items' => [
                [
                    'title' => 'Episode 92 · The ten-year company',
                    'summary' => 'Dana Okonkwo on why patience is a strategy, not a personality trait, and how she funded the slow road.',
                ],
                [
                    'title' => 'Runtime · 1h 04m',
                    'summary' => 'Recorded live in the studio, lightly edited so the long pauses stay in.',
                ],
                [
                    'title' => 'Chapters · 7',
                    'summary' => 'Jump straight to the funding gap, the first acquisition offer, or the decision to stay independent.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function episodeListSection(string $heading, string $summary): array
    {
        return [
            'type' => 'episode-list',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                [
                    'title' => 'Episode 91 · Pricing without flinching',
                    'summary' => 'Marta Reyes on raising prices three times in a year and keeping every customer that mattered.',
                ],
                [
                    'title' => 'Episode 90 · The boring road to a billion',
                    'summary' => 'Idris Bello on why his payments company refused to chase trends and won anyway.',
                ],
                [
                    'title' => 'Episode 89 · Hiring your replacement',
                    'summary' => 'Sophie Lindqvist on stepping back from the company she founded without breaking it.',
                ],
                [
                    'title' => 'Episode 88 · When the moat is patience',
                    'summary' => 'Hassan Park on outlasting better-funded rivals by simply refusing to quit.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function subscribePlatformsSection(): array
    {
        return [
            'type' => 'subscribe-platforms',
            'heading' => 'Listen wherever you already are',
            'summary' => 'New episodes publish to every major platform at 6am every Tuesday. Pick yours and follow once.',
            'items' => [
                ['title' => 'Apple Podcasts', 'summary' => 'Follow the show and get every episode in your library automatically.'],
                ['title' => 'Spotify', 'summary' => 'Tap follow once and new episodes appear in Your Episodes each week.'],
                ['title' => 'YouTube', 'summary' => 'Watch the full video conversations and subscribe for the weekly upload.'],
                ['title' => 'RSS feed', 'summary' => 'Prefer your own player? Paste the raw feed and own your subscription.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function hostsSection(string $heading, string $summary): array
    {
        return [
            'type' => 'hosts',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                [
                    'title' => 'Amara Diallo',
                    'summary' => 'Founder of two companies, one sold and one shut down. Asks the questions about what it actually costs.',
                ],
                [
                    'title' => 'Jonas Weller',
                    'summary' => 'Former operator turned investor. Keeps every conversation grounded in the unglamorous mechanics.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function guestsSection(string $heading, string $summary): array
    {
        return [
            'type' => 'guests',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                [
                    'title' => 'Dana Okonkwo',
                    'summary' => 'Founder of Cartography, the mapping platform that stayed independent through three acquisition offers.',
                ],
                [
                    'title' => 'Marta Reyes',
                    'summary' => 'Built a profitable SaaS business by raising prices when everyone told her not to.',
                ],
                [
                    'title' => 'Idris Bello',
                    'summary' => 'Payments founder who spent a decade on infrastructure before anyone noticed.',
                ],
                [
                    'title' => 'Sophie Lindqvist',
                    'summary' => 'Stepped back from her own company and wrote the playbook for founder succession.',
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
                ['metric' => '40k+', 'name' => 'Weekly listeners', 'quote' => 'A loyal audience that grows by word of mouth, not ad spend.'],
                ['metric' => '92%', 'name' => 'Episode completion', 'quote' => 'Listeners finish what they start — rare for a long-form show.'],
                ['metric' => '4', 'name' => 'Seasons and counting', 'quote' => 'More than ninety conversations published since 2022.'],
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
                ['label' => 'Subscribe', 'url' => '#subscribe', 'style' => 'primary'],
                ['label' => 'Browse episodes', 'url' => '#episodes', 'style' => 'secondary'],
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
                ['label' => 'Episodes', 'url' => '#episodes'],
                ['label' => 'Guests', 'url' => '#guests'],
                ['label' => 'Hosts', 'url' => '#hosts'],
                ['label' => 'Subscribe', 'url' => '#subscribe'],
                ['label' => 'Contact', 'url' => '#contact'],
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
        return [
            'brandName' => self::BRAND,
            'summary' => 'A weekly interview show about building things that last. New episodes every Tuesday.',
            'columns' => [
                [
                    'heading' => 'Listen',
                    'links' => [
                        ['label' => 'Latest episode', 'url' => '#episodes'],
                        ['label' => 'All episodes', 'url' => '#episodes'],
                        ['label' => 'Apple Podcasts', 'url' => '#subscribe'],
                        ['label' => 'Spotify', 'url' => '#subscribe'],
                    ],
                ],
                [
                    'heading' => 'The show',
                    'links' => [
                        ['label' => 'Hosts', 'url' => '#hosts'],
                        ['label' => 'Guests', 'url' => '#guests'],
                        ['label' => 'Pitch a guest', 'url' => '#contact'],
                    ],
                ],
                [
                    'heading' => 'Connect',
                    'links' => [
                        ['label' => 'Contact', 'url' => '#contact'],
                        ['label' => 'hello@thelonggame.example', 'url' => 'mailto:hello@thelonggame.example'],
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
