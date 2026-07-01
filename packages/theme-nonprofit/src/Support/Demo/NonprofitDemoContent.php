<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\Nonprofit\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Nonprofit theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the
 * page adapter emits the theme's signature renderers (impact / campaigns /
 * donation-impact / volunteer-donate / events / stories / annual-report-proof /
 * volunteer-shifts) alongside the standard hero/proof/cta — giving every surface
 * a full, individual cause site rather than the shared five-section skeleton.
 */
final class NonprofitDemoContent implements ProvidesThemeDemoContent
{
    private const string BRAND = 'Greenfield Trust';

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
            title: self::BRAND . ' — Civic & Charity Causes',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'A cause supporters can move behind',
                'Greenfield Trust is a community charity turning local giving into lasting change — campaigns, volunteering, and transparent impact in one place.',
            ),
            renderData: [
                'summary' => 'Greenfield Trust is an impact-led community charity. We move supporters from your mission to a clear action — donate, volunteer, or follow — with proof and live campaign progress built in.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Nonprofit',
                        'heading' => 'A cause supporters can move behind',
                        'summary' => 'An impact-led civic homepage for campaigns, donations, volunteering, community events, and transparent stories — built to turn interest into sustained giving.',
                        'actions' => [
                            ['label' => 'Donate now', 'url' => '#volunteer-donate', 'style' => 'primary'],
                            ['label' => 'View campaigns', 'url' => '#campaigns', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['hero'][0],
                        'mediaAlt' => 'Greenfield Trust volunteers in the community',
                        'campaignTitle' => 'Winter shelter appeal',
                        'campaignValue' => '84%',
                        'campaignProgress' => 84,
                    ],
                    $this->impactSection(
                        heading: 'Outcomes a cause can show with confidence',
                        summary: 'Impact metrics and evidence cards prove results without turning the theme into corporate reporting.',
                    ),
                    $this->campaignsSection(
                        heading: 'Appeals built to make the next gift specific',
                        summary: 'Structured appeal cards with progress and urgency keep campaigns sharper than a broad landing page.',
                    ),
                    $this->donationImpactSection(
                        heading: 'See exactly what each gift unlocks',
                        summary: 'Every donation ties to a tangible outcome, so supporters know precisely what their giving makes possible.',
                    ),
                    $this->volunteerDonateSection(
                        heading: 'Two confident support routes, one clear path',
                        summary: 'Parallel donation and volunteer pathways keep practical support and giving accessible side by side.',
                    ),
                    $this->eventsSection(
                        heading: 'Community events in the cause\'s own language',
                        summary: 'Event cards promote gatherings and volunteering opportunities with a consistent civic rhythm.',
                    ),
                    $this->storiesSection(
                        heading: 'Human proof that leads back to support',
                        summary: 'Supporter and beneficiary stories carry emotion without drifting into case-study presentation.',
                    ),
                    $this->annualReportProofSection(
                        heading: 'Where every pound goes',
                        summary: 'A transparent annual-report breakdown so supporters can trust the numbers behind the mission.',
                    ),
                    $this->proofSection(
                        heading: 'Trusted by the community we serve',
                        summary: 'Evidence from the last year of campaigns, volunteering, and giving.',
                    ),
                    $this->ctaSection(
                        heading: 'Stand with the cause today',
                        summary: 'Whether you give once, give monthly, or give your time, every contribution moves the mission forward.',
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
            name: self::BRAND . ' Campaigns',
            title: 'Campaigns — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'Live appeals and community campaigns',
                'A directory of active appeals — each with a clear goal, live progress, and a specific way to help.',
            ),
            renderData: [
                'summary' => 'Browse every active appeal. Each campaign carries a clear goal, live progress, and a specific next gift.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Campaigns',
                        'heading' => 'Live appeals and community campaigns',
                        'summary' => 'Structured appeal cards with progress and urgency keep every campaign specific. Choose the cause that moves you and give in a click.',
                        'actions' => [
                            ['label' => 'Donate now', 'url' => '#volunteer-donate', 'style' => 'primary'],
                        ],
                        'mediaUrl' => $media['listing'][0] ?? $media['hero'][0],
                        'mediaAlt' => 'Greenfield Trust campaign work',
                        'campaignTitle' => 'Active appeals',
                        'campaignValue' => '67%',
                        'campaignProgress' => 67,
                    ],
                    $this->campaignsSection(
                        heading: 'Appeals built to make the next gift specific',
                        summary: 'Structured appeal cards with progress and urgency keep campaigns sharper than a broad landing page.',
                    ),
                    $this->donationImpactSection(
                        heading: 'See exactly what each gift unlocks',
                        summary: 'Every donation ties to a tangible outcome, so supporters know precisely what their giving makes possible.',
                    ),
                    $this->contentListingSection(
                        heading: 'More ways to get involved',
                        summary: 'Smaller appeals, partner drives, and community fundraisers.',
                    ),
                    $this->ctaSection(
                        heading: 'Found a cause that moves you?',
                        summary: 'Give once or set up a monthly gift — every campaign reaches its goal one supporter at a time.',
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
            name: self::BRAND . ' Appeal',
            title: 'Winter shelter appeal — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'Winter shelter appeal',
                'How a single appeal kept 300 neighbours warm, safe, and supported through the coldest months.',
            ),
            renderData: [
                'summary' => 'How the winter shelter appeal kept 300 neighbours warm, safe, and supported through the coldest months of the year.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Appeal',
                        'heading' => 'Winter shelter appeal',
                        'summary' => 'A focused appeal to keep the community night shelter open through winter — with beds, warm meals, and support workers funded gift by gift.',
                        'actions' => [
                            ['label' => 'View all campaigns', 'url' => '#campaigns', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['detail'][0],
                        'mediaAlt' => 'Greenfield Trust winter shelter',
                        'campaignTitle' => 'Winter shelter appeal',
                        'campaignValue' => '84%',
                        'campaignProgress' => 84,
                    ],
                    $this->donationImpactSection(
                        heading: 'See exactly what each gift unlocks',
                        summary: 'Every donation ties to a tangible outcome, so supporters know precisely what their giving makes possible.',
                    ),
                    $this->annualReportProofSection(
                        heading: 'Where this appeal\'s money went',
                        summary: 'A transparent breakdown of every pound raised for the winter shelter.',
                    ),
                    $this->storiesSection(
                        heading: 'The people behind the appeal',
                        summary: 'Supporters and neighbours on what the shelter meant this winter.',
                    ),
                    $this->ctaSection(
                        heading: 'Keep the shelter open next winter',
                        summary: 'A monthly gift means we can plan ahead and keep the doors open before the cold returns.',
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
            name: self::BRAND . ' Get Involved',
            title: 'Get involved — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Get involved',
                'Donate, volunteer, or partner with us. Tell us how you want to help and the team will point you to the right next step.',
            ),
            renderData: [
                'summary' => 'Donate, volunteer, or partner with the Trust. Email hello@greenfieldtrust.example or use the routes below — a real person replies within one working day.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Get involved',
                        'heading' => 'Get involved',
                        'summary' => 'Based in Greenfield, working across the county. Email hello@greenfieldtrust.example or choose a route below — we reply within one working day.',
                        'actions' => [
                            ['label' => 'Email the team', 'url' => 'mailto:hello@greenfieldtrust.example', 'style' => 'primary'],
                            ['label' => 'View campaigns', 'url' => '#campaigns', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['contact'][0],
                        'mediaAlt' => 'The Greenfield Trust team',
                    ],
                    $this->volunteerDonateSection(
                        heading: 'Two confident support routes, one clear path',
                        summary: 'Parallel donation and volunteer pathways keep practical support and giving accessible side by side.',
                    ),
                    $this->volunteerShiftsSection(
                        heading: 'Volunteer shifts you can join this month',
                        summary: 'Practical, time-boxed ways to give your time, from a single afternoon to a regular weekly slot.',
                    ),
                    $this->proofSection(
                        heading: 'What to expect when you reach out',
                        summary: 'How we welcome new supporters and volunteers.',
                    ),
                    $this->ctaSection(
                        heading: 'Prefer to talk it through?',
                        summary: 'Book a short call with the team and we will find the way to help that fits you best.',
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
            title: 'No campaigns — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'No campaigns match that filter yet',
                'A graceful empty state for a filtered campaigns archive with no matching appeals.',
            ),
            renderData: [
                'summary' => 'No campaigns match that filter yet — but there are still plenty of ways to support the cause.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Campaigns',
                        'heading' => 'No campaigns match that filter — yet',
                        'summary' => 'We have no live appeals in this category right now. Clear the filter to see everything, or tell us what you care about.',
                        'actions' => [
                            ['label' => 'View all campaigns', 'url' => '#campaigns', 'style' => 'primary'],
                            ['label' => 'Donate now', 'url' => '#volunteer-donate', 'style' => 'secondary'],
                        ],
                    ],
                    [
                        'type' => 'campaigns',
                        'heading' => 'Nothing to show here',
                        'summary' => 'When appeals land in this category they will appear here, newest first.',
                        'items' => [],
                    ],
                    $this->volunteerDonateSection(
                        heading: 'While you are here',
                        summary: 'Two confident ways to support the cause right now.',
                    ),
                    $this->ctaSection(
                        heading: 'Looking for a specific cause?',
                        summary: 'Tell us what matters to you and we will point you to the appeal that fits.',
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
                'A not-found page that routes visitors back into the campaigns and supporter paths.',
            ),
            renderData: [
                'summary' => 'That page has moved or never existed — here is the way back to the cause.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => '404',
                        'heading' => 'This page has moved on',
                        'summary' => 'The link is broken or the page has moved. Head back to the campaigns, or find a way to support the cause.',
                        'actions' => [
                            ['label' => 'Back to home', 'url' => '/', 'style' => 'primary'],
                            ['label' => 'View campaigns', 'url' => '#campaigns', 'style' => 'secondary'],
                        ],
                    ],
                    $this->ctaSection(
                        heading: 'Still looking for something?',
                        summary: 'Tell us what you needed and we will point you to the right place.',
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
            name: self::BRAND . ' Support',
            title: 'Support the cause — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Stand with the cause today',
                'A focused conversion page inviting supporters to donate, volunteer, or follow.',
            ),
            renderData: [
                'summary' => 'Stand with the cause today. Donate, volunteer, or follow — every contribution moves the mission forward.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Support the cause',
                        'heading' => 'Stand with the cause today',
                        'summary' => 'Whether you give once, give monthly, or give your time, you join a community keeping neighbours warm, fed, and supported.',
                        'actions' => [
                            ['label' => 'Donate now', 'url' => '#volunteer-donate', 'style' => 'primary'],
                            ['label' => 'Email the team', 'url' => 'mailto:hello@greenfieldtrust.example', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['cta'][0],
                        'mediaAlt' => 'Greenfield Trust supporters',
                        'campaignTitle' => 'This year\'s goal',
                        'campaignValue' => '72%',
                        'campaignProgress' => 72,
                    ],
                    $this->proofSection(
                        heading: 'Why supporters stay with us',
                        summary: 'The numbers behind a year of community giving.',
                    ),
                    $this->donationImpactSection(
                        heading: 'See exactly what each gift unlocks',
                        summary: 'Every donation ties to a tangible outcome, so supporters know precisely what their giving makes possible.',
                    ),
                    $this->ctaSection(
                        heading: 'One gift away from making a difference',
                        summary: 'Set up a monthly gift today and we will keep you posted on exactly what it makes possible.',
                    ),
                ],
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function impactSection(string $heading, string $summary): array
    {
        return [
            'type' => 'impact',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['metric' => '12k', 'summary' => 'Neighbours supported through shelter, food, and advice in the last year.'],
                ['metric' => '84%', 'summary' => 'Of the winter shelter appeal funded by community giving alone.'],
                ['metric' => '31', 'summary' => 'Local partners delivering services alongside our volunteers.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function campaignsSection(string $heading, string $summary): array
    {
        return [
            'type' => 'campaigns',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                [
                    'label' => 'Urgent',
                    'title' => 'Winter shelter appeal',
                    'summary' => 'Keep the community night shelter open through the coldest months with beds, meals, and support workers.',
                    'progress' => 84,
                    'goal' => '£40,000',
                    'url' => '#volunteer-donate',
                ],
                [
                    'label' => 'Matched giving',
                    'title' => 'Family food fund',
                    'summary' => 'Every pound doubled this month — stock the community pantry for families facing the hardest weeks.',
                    'progress' => 61,
                    'goal' => '£25,000',
                    'url' => '#volunteer-donate',
                ],
                [
                    'label' => 'Community',
                    'title' => 'Youth mentoring programme',
                    'summary' => 'Fund weekly mentoring sessions that help young people in the area build confidence and skills.',
                    'progress' => 47,
                    'goal' => '£18,000',
                    'url' => '#volunteer-donate',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function donationImpactSection(string $heading, string $summary): array
    {
        return [
            'type' => 'donation-impact',
            'heading' => $heading,
            'summary' => $summary,
            'primaryAction' => ['label' => 'Donate now', 'url' => '#volunteer-donate'],
            'secondaryAction' => ['label' => 'See our impact', 'url' => '#impact'],
            'items' => [
                ['metric' => '£10', 'title' => 'A warm night', 'summary' => 'Provides a bed, a hot meal, and a safe place to sleep for one neighbour.'],
                ['metric' => '£25', 'title' => 'A week of food', 'summary' => 'Stocks the community pantry for a family facing a difficult week.'],
                ['metric' => '£50', 'title' => 'A month of mentoring', 'summary' => 'Funds a month of weekly mentoring sessions for a young person.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function volunteerDonateSection(string $heading, string $summary): array
    {
        return [
            'type' => 'volunteer-donate',
            'heading' => $heading,
            'summary' => $summary,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function eventsSection(string $heading, string $summary): array
    {
        return [
            'type' => 'events',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['date' => 'Sat 14 Sep', 'title' => 'Community fun run', 'summary' => 'A family 5k through Greenfield Park raising funds for the youth programme.', 'location' => 'Greenfield Park', 'url' => '#events'],
                ['date' => 'Thu 26 Sep', 'title' => 'Volunteer welcome evening', 'summary' => 'Meet the team, tour the shelter, and find the volunteering role that fits you.', 'location' => 'The Trust Hub', 'url' => '#volunteer-donate'],
                ['date' => 'Fri 11 Oct', 'title' => 'Autumn fundraising gala', 'summary' => 'An evening celebrating a year of impact, with supper, stories, and a live appeal.', 'location' => 'Greenfield Hall', 'url' => '#events'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function storiesSection(string $heading, string $summary): array
    {
        return [
            'type' => 'stories',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['type' => 'Beneficiary', 'title' => 'A safe place to start again', 'summary' => 'After a hard year, the night shelter gave Maria the stability to find work and a home.', 'meta' => 'Maria, supported neighbour'],
                ['type' => 'Volunteer', 'title' => 'Giving back every week', 'summary' => 'Two evenings a month at the pantry turned into the most meaningful part of my week.', 'meta' => 'James, volunteer'],
                ['type' => 'Donor', 'title' => 'Giving I can actually see', 'summary' => 'I know exactly what my monthly gift pays for, and I get to read the stories behind it.', 'meta' => 'Priya, monthly donor'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function annualReportProofSection(string $heading, string $summary): array
    {
        return [
            'type' => 'annual-report-proof',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['metric' => '88p', 'title' => 'Goes to frontline services', 'summary' => 'For every pound raised, 88p reaches the people we serve directly.'],
                ['metric' => '9p', 'title' => 'Keeps the lights on', 'summary' => 'Core running costs that keep the shelter, pantry, and hub open.'],
                ['metric' => '3p', 'title' => 'Raises the next pound', 'summary' => 'Modest fundraising spend that grows community giving year on year.'],
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
                ['metric' => '12k', 'name' => 'Neighbours supported', 'description' => 'Reached through shelter, food, advice, and mentoring in the last year.'],
                ['metric' => '88p', 'name' => 'To frontline services', 'description' => 'Of every pound raised goes straight to the people we serve.'],
                ['metric' => '1 day', 'name' => 'To hear back', 'description' => 'A real person on the team replies to every supporter within one working day.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function volunteerShiftsSection(string $heading, string $summary): array
    {
        return [
            'type' => 'volunteer-shifts',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['type' => 'Weekly', 'title' => 'Community pantry shift', 'summary' => 'Help sort, stock, and welcome families at the pantry, Tuesday and Thursday afternoons.'],
                ['type' => 'Evening', 'title' => 'Night shelter support', 'summary' => 'Prepare meals and welcome guests on a rota of one or two evenings a month.'],
                ['type' => 'Flexible', 'title' => 'Event volunteer', 'summary' => 'Lend a hand at fundraisers and community events whenever your time allows.'],
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
            'variant' => 'gallery',
            'items' => [
                ['title' => 'Workplace giving', 'type' => 'Partner', 'summary' => 'Match your team\'s fundraising and double the difference your workplace makes.', 'url' => '#campaigns'],
                ['title' => 'Leave a legacy', 'type' => 'Long-term', 'summary' => 'A gift in your will keeps the shelter open for the neighbours who come after.', 'url' => '#campaigns'],
                ['title' => 'Community fundraiser', 'type' => 'Get involved', 'summary' => 'Run, bake, or organise your own event and raise funds for a cause you choose.', 'url' => '#events'],
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
                ['label' => 'Donate now', 'url' => '#volunteer-donate', 'style' => 'primary'],
                ['label' => 'Volunteer with us', 'url' => '#volunteer-donate', 'style' => 'secondary'],
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
                ['label' => 'Campaigns', 'url' => '#campaigns'],
                ['label' => 'Impact', 'url' => '#impact'],
                ['label' => 'Volunteer', 'url' => '#volunteer-donate'],
                ['label' => 'Stories', 'url' => '#stories'],
            ],
            'ctaLabel' => 'Donate now',
            'ctaUrl' => '#volunteer-donate',
            'consultationUrl' => '#volunteer-donate',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        return [
            'brandName' => self::BRAND,
            'summary' => 'An impact-led community charity. Greenfield, working across the county.',
            'columns' => [
                [
                    'heading' => 'Support',
                    'links' => [
                        ['label' => 'Donate', 'url' => '#volunteer-donate'],
                        ['label' => 'Volunteer', 'url' => '#volunteer-donate'],
                        ['label' => 'Campaigns', 'url' => '#campaigns'],
                        ['label' => 'Events', 'url' => '#events'],
                    ],
                ],
                [
                    'heading' => 'About',
                    'links' => [
                        ['label' => 'Our impact', 'url' => '#impact'],
                        ['label' => 'Stories', 'url' => '#stories'],
                        ['label' => 'Annual report', 'url' => '#impact'],
                        ['label' => 'Our team', 'url' => '#about'],
                    ],
                ],
                [
                    'heading' => 'Connect',
                    'links' => [
                        ['label' => 'Get involved', 'url' => '#volunteer-donate'],
                        ['label' => 'hello@greenfieldtrust.example', 'url' => 'mailto:hello@greenfieldtrust.example'],
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
