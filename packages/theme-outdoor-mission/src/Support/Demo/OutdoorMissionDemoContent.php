<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\OutdoorMission\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Outdoor Mission theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the
 * page adapter emits the theme's signature renderers (seasonal-essentials /
 * sport-categories / environmental-campaign / repair-reuse / field-stories /
 * newsletter) alongside the standard hero/proof/features/content-listing/cta —
 * giving every surface a full, individual outdoor mission-commerce site rather
 * than the shared five-section skeleton.
 */
final class OutdoorMissionDemoContent implements ProvidesThemeDemoContent
{
    private const string BRAND = 'Summit & Spruce';

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
            title: self::BRAND . ' — Outdoor Gear Built for the Mission',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'Gear a mission can stand behind',
                'Summit & Spruce builds rugged outdoor kit for climbers, trail runners, surfers, and snow crews — designed to be repaired, not replaced.',
            ),
            renderData: [
                'summary' => 'Summit & Spruce is a mission-led outdoor brand. Field-tested gear, repair-first product care, and campaigns that put crews to work for the places they love.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Outdoor Mission',
                        'heading' => 'Gear a mission can stand behind',
                        'summary' => 'Shells that shrug off a week of weather, packs that carry the long days, and a repair desk that keeps every piece in the field for years. Built for the crew, built for the cause.',
                        'actions' => [
                            ['label' => 'Shop the field kit', 'url' => '#seasonal-essentials', 'style' => 'primary'],
                            ['label' => 'Join a campaign', 'url' => '#environmental-campaign', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['hero'][0],
                        'mediaAlt' => 'A trail crew loading packs at the trailhead',
                    ],
                    $this->seasonalEssentialsSection(
                        heading: 'Kit for the season ahead',
                        summary: 'Three weatherproof systems chosen for the conditions your crew is heading into next.',
                    ),
                    $this->sportCategoriesSection(
                        heading: 'Built for your sport',
                        summary: 'Purpose-made kit for the disciplines we live and breathe.',
                    ),
                    $this->environmentalCampaignSection(
                        heading: 'Gear that fights for the ground it crosses',
                        summary: 'One percent of every sale funds the field campaigns below — and your crew can join the work, not just bankroll it.',
                    ),
                    $this->repairReuseSection(
                        heading: 'Repaired, not replaced',
                        summary: 'A worn shell is a repair job, not landfill. Our care desk keeps your kit in the field for the long haul.',
                    ),
                    $this->fieldStoriesSection(
                        heading: 'Dispatches from the field',
                        summary: 'Routes, repairs, and campaigns from the crews who put the kit to work.',
                    ),
                    $this->proofSection(
                        heading: 'The mission, in numbers',
                        summary: 'What the repair desk and the campaign fund have added up to.',
                    ),
                    $this->newsletterSection(
                        heading: 'Join the crew dispatch',
                        summary: 'Field notes, restock alerts, and campaign call-outs — once a fortnight, never more.',
                    ),
                    $this->ctaSection(
                        heading: 'Kit up for the next mission',
                        summary: 'Build a field kit that lasts and put your purchase to work for wild places.',
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
            name: self::BRAND . ' Field Journal',
            title: 'Field Journal — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'The field journal',
                'Routes, repairs, and campaign reports from the Summit & Spruce crew, scannable and field-ready.',
            ),
            renderData: [
                'summary' => 'Routes, gear repairs, and campaign reports from the crews who run our kit hard — a field archive built to be scanned.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Field journal',
                        'heading' => 'Routes, repairs, and reports from the crew',
                        'summary' => 'Long days and hard-won lessons from climbers, runners, surfers, and snow crews. Filter by discipline or read the whole archive.',
                        'actions' => [
                            ['label' => 'Browse the latest', 'url' => '#content-listing', 'style' => 'primary'],
                            ['label' => 'Join a campaign', 'url' => '#environmental-campaign', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['listing'][0] ?? $media['hero'][0],
                        'mediaAlt' => 'A field journalist writing notes at a mountain camp',
                    ],
                    $this->fieldStoriesSection(
                        heading: 'A field archive built to be scanned',
                        summary: 'Structured story and guide cards keep the archive rugged and legible.',
                    ),
                    $this->contentListingSection(
                        heading: 'More from the journal',
                        summary: 'Trip reports, gear teardowns, and repair guides, newest first.',
                    ),
                    $this->ctaSection(
                        heading: 'Got a route or repair to share?',
                        summary: 'Send the crew your field notes and we will work them into the next dispatch.',
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
            name: self::BRAND . ' Gear Story',
            title: 'The Cascade 3-Layer Shell — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'The Cascade 3-Layer Shell',
                'How we built a storm shell to be field-repaired, re-waxed, and handed down rather than thrown away.',
            ),
            renderData: [
                'summary' => 'A storm shell engineered around the repair desk — taped seams you can re-tape, panels you can patch, and a wax you can renew in the field.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Gear story',
                        'heading' => 'The Cascade 3-Layer Shell',
                        'summary' => 'A four-season storm shell with a recycled face fabric, fully taped seams, and a panelled cut designed so any worn section can be patched instead of binned.',
                        'actions' => [
                            ['label' => 'Add to the kit', 'url' => '#seasonal-essentials', 'style' => 'primary'],
                            ['label' => 'Book a repair', 'url' => '#repair-reuse', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['detail'][0],
                        'mediaAlt' => 'The Cascade storm shell laid out with repair patches',
                    ],
                    $this->featuresSection(
                        heading: 'Engineered to be kept',
                        summary: 'Every spec on this shell is chosen to extend its years in the field.',
                    ),
                    $this->repairReuseSection(
                        heading: 'How we keep this shell going',
                        summary: 'Three repair paths that keep the Cascade out of landfill and on the hill.',
                    ),
                    $this->fieldStoriesSection(
                        heading: 'The Cascade in the field',
                        summary: 'Crews who have run this shell hard and brought it back for more.',
                    ),
                    $this->ctaSection(
                        heading: 'Build a kit that lasts',
                        summary: 'Pair the Cascade with the rest of the field system and put your purchase to work.',
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
            name: self::BRAND . ' Join the Mission',
            title: 'Join the mission — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Join the mission',
                'Sign up for the crew dispatch, book a repair, or bring your community campaign to the Summit & Spruce team.',
            ),
            renderData: [
                'summary' => 'Sign up for the crew dispatch, book a gear repair, or partner on a community campaign — one confident path into the mission.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Join the mission',
                        'heading' => 'Join the mission through one confident path',
                        'summary' => 'Workshop in the valley, crews across the range. Email crew@summitandspruce.example or use the form below — a real person on the team replies within one working day.',
                        'actions' => [
                            ['label' => 'Email the crew', 'url' => 'mailto:crew@summitandspruce.example', 'style' => 'primary'],
                            ['label' => 'Book a repair', 'url' => '#repair-reuse', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['contact'][0],
                        'mediaAlt' => 'The Summit & Spruce repair workshop',
                    ],
                    $this->newsletterSection(
                        heading: 'Start with the crew dispatch',
                        summary: 'A non-submitting sign-up that proves the newsletter and repair-request journey feels part of the field experience.',
                    ),
                    $this->featuresSection(
                        heading: 'What we can help with',
                        summary: 'Whether it is gear, a repair, or a campaign, pick the path that fits.',
                    ),
                    $this->ctaSection(
                        heading: 'Ready to get involved?',
                        summary: 'Tell us what you are planning and the crew will come back with the next step.',
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
            title: 'No matches yet — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'Nothing on this route yet',
                'A graceful empty state for a filtered journal or gear search with no matching entries.',
            ),
            renderData: [
                'summary' => 'No journal entries match that filter yet — but the crew can still point you somewhere worth your time.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Field journal',
                        'heading' => 'Nothing on this route yet',
                        'summary' => 'We have not filed anything under that filter. Clear it to see the whole archive, or tell the crew what you were after.',
                        'actions' => [
                            ['label' => 'Browse everything', 'url' => '#content-listing', 'style' => 'primary'],
                            ['label' => 'Join a campaign', 'url' => '#environmental-campaign', 'style' => 'secondary'],
                        ],
                    ],
                    $this->sportCategoriesSection(
                        heading: 'Pick a discipline instead',
                        summary: 'Jump straight to the kit and stories for your sport.',
                    ),
                    $this->newsletterSection(
                        heading: 'Hear it first next time',
                        summary: 'Join the crew dispatch and new entries land in your inbox as they are filed.',
                    ),
                    $this->ctaSection(
                        heading: 'Looking for something specific?',
                        summary: 'Tell the crew the brief and we will point you to the right route.',
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
            title: 'Off the map — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-404',
            content: $this->prose(
                'You are off the map',
                'A not-found page that routes visitors back into the gear, the journal, and the campaign paths.',
            ),
            renderData: [
                'summary' => 'That page wandered off the map — here is the trail back.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => '404',
                        'heading' => 'You have wandered off the map',
                        'summary' => 'The link is broken or the page has moved camp. Head back to the gear, the field journal, or join a campaign.',
                        'actions' => [
                            ['label' => 'Back to base', 'url' => '/', 'style' => 'primary'],
                            ['label' => 'Shop the field kit', 'url' => '#seasonal-essentials', 'style' => 'secondary'],
                        ],
                    ],
                    $this->ctaSection(
                        heading: 'Still looking for the trail?',
                        summary: 'Tell the crew what you needed and we will point you the right way.',
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
            name: self::BRAND . ' Kit Up',
            title: 'Kit up for the mission — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Kit up for the mission',
                'A focused conversion page inviting new crew to build a field kit and back the campaigns.',
            ),
            renderData: [
                'summary' => 'Build a field kit that lasts, back the campaigns, and join the crew dispatch.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Kit up',
                        'heading' => 'Kit up for the mission',
                        'summary' => 'Whether it is one shell or a full season system, you get gear built to be repaired and a purchase that funds the field work.',
                        'actions' => [
                            ['label' => 'Shop the field kit', 'url' => '#seasonal-essentials', 'style' => 'primary'],
                            ['label' => 'Email the crew', 'url' => 'mailto:crew@summitandspruce.example', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['cta'][0],
                        'mediaAlt' => 'A crew cresting a ridge at dawn',
                    ],
                    $this->proofSection(
                        heading: 'Why crews ride with us',
                        summary: 'What the repair desk and campaign fund have added up to.',
                    ),
                    $this->newsletterSection(
                        heading: 'Join the crew dispatch',
                        summary: 'Field notes and campaign call-outs, once a fortnight.',
                    ),
                    $this->ctaSection(
                        heading: 'One kit away',
                        summary: 'Build your field system and the crew will help you keep it running for years.',
                    ),
                ],
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function seasonalEssentialsSection(string $heading, string $summary): array
    {
        return [
            'type' => 'seasonal-essentials',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Storm-season shells', 'summary' => 'Three-layer waterproofs with re-waxable faces and seams you can re-tape in the field.'],
                ['title' => 'Heat-of-the-day layers', 'summary' => 'Sun hoodies and merino tees that breathe on long ridge days and pack down to nothing.'],
                ['title' => 'Camp & basecamp kit', 'summary' => 'Packs, sleep systems, and stoves built to be serviced season after season.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function sportCategoriesSection(string $heading, string $summary): array
    {
        return [
            'type' => 'sport-categories',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Climb', 'summary' => 'Harnesses, soft goods, and abrasion-tough layers for the crag and the alpine.'],
                ['title' => 'Trail', 'summary' => 'Run vests, fast shells, and grippy kit for long days on the singletrack.'],
                ['title' => 'Surf', 'summary' => 'Repairable wetsuits and changing kit built for cold-water dawn patrols.'],
                ['title' => 'Snow', 'summary' => 'Touring shells and insulation that keep working when the temperature drops.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function environmentalCampaignSection(string $heading, string $summary): array
    {
        return [
            'type' => 'environmental-campaign',
            'heading' => $heading,
            'summary' => $summary,
            'label' => 'Join a campaign',
            'url' => '#environmental-campaign',
            'items' => [
                ['title' => 'Sign the river petition', 'summary' => 'Back the campaign to win bathing-water status for three more upland rivers this year.'],
                ['title' => 'Map a trail clean-up', 'summary' => 'Pin a litter-pick or path-repair day and we will send the crew, the kit, and the snacks.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function repairReuseSection(string $heading, string $summary): array
    {
        return [
            'type' => 'repair-reuse',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Patch & re-tape', 'summary' => 'Send us a torn shell and our workshop re-tapes the seam or patches the panel, often within a week.'],
                ['title' => 'Trade-in & resell', 'summary' => 'Hand back outgrown kit for store credit; we clean, repair, and re-home it through the depot.'],
                ['title' => 'Care for the long haul', 'summary' => 'Free re-waxing and zip servicing for the life of the garment, booked through the crew desk.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function fieldStoriesSection(string $heading, string $summary): array
    {
        return [
            'type' => 'field-stories',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['meta' => 'Trip report', 'title' => 'Three days on the river traverse', 'summary' => 'A packraft crew runs the upper gorge and files notes on what held up and what needed a field patch.'],
                ['meta' => 'Gear teardown', 'title' => 'An alpine shell after five seasons', 'summary' => 'We cut open a well-worn Cascade to show where the repairs went and why it is still on the hill.'],
                ['meta' => 'Repair diary', 'title' => 'The pack that refused to retire', 'summary' => 'Eleven repairs, two owners, one haul bag — a case study in keeping kit out of landfill.'],
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
                ['meta' => 'Storm shell', 'title' => 'Cascade 3-layer waterproof', 'summary' => 'Recycled face fabric, fully taped seams, and a panelled cut made to be patched, not binned.', 'repair_note' => 'Field-repairable'],
                ['meta' => 'Hauling', 'title' => 'Granite 45 alpine pack', 'summary' => 'A welded haul pack with replaceable hip belt and a back panel you can service yourself.', 'repair_note' => 'Serviceable parts'],
                ['meta' => 'Warmth', 'title' => 'Tarn grid fleece', 'summary' => 'A bluesign-certified grid fleece that re-fluffs after washing and outlasts three of its rivals.', 'repair_note' => 'Re-waxable shell pairing'],
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
                ['value' => '18,400', 'label' => 'Repairs completed at the workshop since 2019.'],
                ['value' => '92%', 'label' => 'Recycled or bluesign-certified materials across the range.'],
                ['value' => '£640k', 'label' => 'Raised for river, trail, and access campaigns.'],
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
                ['category' => 'Trip report', 'title' => 'Dawn patrol on the cold coast', 'summary' => 'A surf crew logs a week of pre-work sessions and the wetsuit repairs that kept them in the water.'],
                ['category' => 'Repair guide', 'title' => 'Re-taping a delaminated seam', 'summary' => 'A step-by-step from the workshop bench on rescuing a tired storm shell at home.'],
                ['category' => 'Campaign report', 'title' => 'What the river petition won', 'summary' => 'The crew that pushed for bathing-water status reports back on a year of fieldwork.'],
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
            'label' => 'Shop the field kit',
            'url' => '#seasonal-essentials',
            'actions' => [
                ['label' => 'Shop the field kit', 'url' => '#seasonal-essentials', 'style' => 'primary'],
                ['label' => 'Join a campaign', 'url' => '#environmental-campaign', 'style' => 'secondary'],
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
            'brand' => self::BRAND,
            'items' => [
                ['label' => 'Gear', 'url' => '#seasonal-essentials'],
                ['label' => 'Sports', 'url' => '#sport-categories'],
                ['label' => 'Campaigns', 'url' => '#environmental-campaign'],
                ['label' => 'Field journal', 'url' => '#field-stories'],
                ['label' => 'Repairs', 'url' => '#repair-reuse'],
            ],
            'ctaLabel' => 'Join the crew',
            'ctaUrl' => '#newsletter',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        return [
            'brandName' => self::BRAND,
            'summary' => 'Mission-led outdoor gear, built to be repaired and put to work for wild places.',
            'columns' => [
                [
                    'heading' => 'Shop',
                    'title' => 'Shop',
                    'links' => [
                        ['label' => 'Shells & waterproofs', 'url' => '#seasonal-essentials'],
                        ['label' => 'Packs & hauling', 'url' => '#seasonal-essentials'],
                        ['label' => 'Layers & insulation', 'url' => '#seasonal-essentials'],
                    ],
                ],
                [
                    'heading' => 'Care',
                    'title' => 'Care',
                    'links' => [
                        ['label' => 'Book a repair', 'url' => '#repair-reuse'],
                        ['label' => 'Trade-in & resell', 'url' => '#repair-reuse'],
                        ['label' => 'Materials & care', 'url' => '#repair-reuse'],
                    ],
                ],
                [
                    'heading' => 'Action',
                    'title' => 'Action',
                    'links' => [
                        ['label' => 'Campaigns', 'url' => '#environmental-campaign'],
                        ['label' => 'Field journal', 'url' => '#field-stories'],
                        ['label' => 'Join the crew', 'url' => '#newsletter'],
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
