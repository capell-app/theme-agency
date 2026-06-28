<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\RoboticsHardware\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Robotics Hardware theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the
 * page adapter emits the theme's signature renderers (video-hero / spec-sheet /
 * capabilities / tech-deep-dive / preorder-cta) alongside the standard
 * hero/proof/cta — giving every surface a full deep-tech hardware site rather
 * than a shared skeleton. Copy is mined from the screenshot renderer and tuned
 * for engineering-led robotics buyers.
 */
final class RoboticsHardwareDemoContent implements ProvidesThemeDemoContent
{
    private const string BRAND = 'Axon Robotics';

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
            title: self::BRAND . ' — Robotics Hardware for the Production Line',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'Robotics hardware engineered for the next production run',
                'Axon Robotics builds modular robotic platforms, end effectors, and controllers for engineering teams running real production lines.',
            ),
            renderData: [
                'summary' => 'Axon Robotics builds modular robotic platforms for the line and the lab — specified, certified, and ready for the next production run.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Robotics hardware',
                        heading: 'Robotics hardware engineered for the next production run',
                        summary: 'Modular platforms, safe motion envelopes, and repeatable sub-millimetre accuracy — designed and certified for teams who put robots to work, not on a shelf.',
                        media: $media['hero'][0],
                        mediaAlt: 'Axon robotic arm on the assembly line',
                    ),
                    $this->specSheetSection(
                        heading: 'Engineered specifications buyers can trust',
                        summary: 'Key dimensions, payloads, and tolerances stay legible so deep-tech buyers can qualify the platform fast.',
                    ),
                    $this->capabilitiesSection(
                        heading: 'Capabilities tuned for the line and the lab',
                        summary: 'Modular tooling, safe motion envelopes, and repeatable accuracy framed for engineering-led evaluation.',
                    ),
                    $this->featuresSection(
                        heading: 'Built for engineers who ship',
                        summary: 'Every Axon platform arrives field-ready, with the interfaces and safety systems a controls team expects.',
                    ),
                    $this->techDeepDiveSection(
                        heading: 'Under the housing: how Axon holds tolerance',
                        summary: 'Harmonic drives, dual-encoder feedback, and a real-time motion core keep accuracy stable across millions of cycles.',
                    ),
                    $this->proofSection(
                        heading: 'Proven on real production lines',
                        summary: 'Uptime, accuracy, and payload numbers from Axon platforms running in the field today.',
                    ),
                    $this->preorderCtaSection(
                        heading: 'Reserve your place in the next production run',
                        summary: 'Pre-order slots for the AX-7 and AX-12 platforms open quarterly. Lock your build configuration and delivery window now.',
                    ),
                    $this->ctaSection(
                        heading: 'Talk to the robotics engineering team',
                        summary: 'Send us your cycle time, payload, and reach targets. We will spec a platform and come back within one working day.',
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
            name: self::BRAND . ' Platforms',
            title: 'Platforms & Hardware — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'Browse the robotics hardware catalogue',
                'A directory of robotic platforms, end effectors, and motion controllers across the Axon range.',
            ),
            renderData: [
                'summary' => 'A scannable directory of robotic platforms, end effectors, and controllers across the full Axon hardware range.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Platforms & hardware',
                        heading: 'Browse the robotics hardware catalogue',
                        summary: 'Filter by reach, payload, or end effector. Every platform ships with full specifications, CAD, and integration support.',
                        media: $media['listing'][0] ?? $media['hero'][0],
                        mediaAlt: 'Axon robotics hardware range',
                    ),
                    $this->contentListingSection(
                        heading: 'The Axon platform range',
                        summary: 'Six-axis arms, SCARA units, and mobile bases — each engineered for a specific payload and cycle profile.',
                    ),
                    $this->featuresSection(
                        heading: 'What every platform shares',
                        summary: 'A common controller, safety architecture, and tooling interface across the whole range.',
                    ),
                    $this->ctaSection(
                        heading: 'Not sure which platform fits?',
                        summary: 'Send us your application and our engineers will shortlist the platforms worth evaluating.',
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
            name: self::BRAND . ' AX-7 Platform',
            title: 'AX-7 Six-Axis Platform — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'AX-7 — a single platform, fully specified',
                'The AX-7 six-axis platform pairs a 12 kg payload with sub-0.02 mm repeatability for high-mix assembly cells.',
            ),
            renderData: [
                'summary' => 'The AX-7 six-axis platform: 12 kg payload, 1.3 m reach, and sub-0.02 mm repeatability — fully specified and integration-ready.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'AX-7 platform',
                        heading: 'AX-7 — a single platform, fully specified',
                        summary: 'A six-axis workhorse for high-mix assembly: 12 kg at the wrist, 1.3 m reach, and a real-time controller your team can program in hours.',
                        media: $media['detail'][0],
                        mediaAlt: 'AX-7 six-axis robotic platform',
                    ),
                    $this->specSheetSection(
                        heading: 'AX-7 specifications, fully laid out',
                        summary: 'Detail pages pair the spec sheet with deep-dive context so a buyer can commit with confidence.',
                    ),
                    $this->techDeepDiveSection(
                        heading: 'Inside the AX-7 motion core',
                        summary: 'Harmonic reduction, dual encoders, and a 1 kHz control loop keep the AX-7 accurate from the first cycle to the millionth.',
                    ),
                    $this->proofSection(
                        heading: 'How the AX-7 performs in the field',
                        summary: 'Cycle time, uptime, and accuracy measured on AX-7 cells in production today.',
                    ),
                    $this->ctaSection(
                        heading: 'Want an AX-7 evaluation unit?',
                        summary: 'Tell us your cell layout and we will arrange a configured AX-7 for on-site evaluation.',
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
            title: 'Talk to engineering — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Talk to the robotics engineering team',
                'Reach the Axon engineering team directly to scope a platform, request CAD, or book an evaluation.',
            ),
            renderData: [
                'summary' => 'Reach the Axon engineering team directly — no sales gatekeepers between you and the people who build the hardware.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Contact',
                        heading: 'Talk to the robotics engineering team',
                        summary: 'Engineering office in Cambridge, integration partners across the UK, EU, and US. Email engineering@axonrobotics.example or use the details below — we reply within one working day.',
                        media: $media['contact'][0],
                        mediaAlt: 'Axon Robotics engineering office',
                    ),
                    $this->capabilitiesSection(
                        heading: 'How we can help',
                        summary: 'Spec a platform, request CAD and integration files, or arrange an on-site evaluation cell.',
                    ),
                    $this->proofSection(
                        heading: 'What to expect once you reach out',
                        summary: 'How an Axon engagement runs from first email to a commissioned cell.',
                    ),
                    $this->ctaSection(
                        heading: 'Prefer to talk it through?',
                        summary: 'Book a 30-minute call with an applications engineer and we will tell you honestly whether Axon is the right fit.',
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
            title: 'No hardware listed yet — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'No hardware listed yet',
                'A graceful empty state for a filtered hardware catalogue with no matching platforms.',
            ),
            renderData: [
                'summary' => 'No platforms match that filter yet — but the engineering team can still point you to the right hardware.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Hardware catalogue',
                        heading: 'No platforms match that filter — yet',
                        summary: 'Nothing in the catalogue meets those parameters right now. Clear the filter to see the full range, or tell us your application.',
                        media: null,
                        mediaAlt: null,
                    ),
                    $this->contentListingSection(
                        heading: 'Nothing listed under these parameters',
                        summary: 'When platforms matching this payload and reach land, they will appear here, newest first.',
                        items: [],
                    ),
                    $this->capabilitiesSection(
                        heading: 'While you are here',
                        summary: 'The capabilities every Axon platform is built around.',
                    ),
                    $this->ctaSection(
                        heading: 'Looking for a specific platform?',
                        summary: 'Send us the payload, reach, and cycle target and we will tell you what fits — or what we can build.',
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
                'This page has powered down',
                'A not-found page that routes visitors back into the platform catalogue and contact paths.',
            ),
            renderData: [
                'summary' => 'That page has powered down or moved — here is the way back to the hardware.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: '404',
                        heading: 'This page has powered down',
                        summary: 'The link is broken or the page has moved. Head back to the platform catalogue, or reach the engineering team directly.',
                        media: null,
                        mediaAlt: null,
                    ),
                    $this->ctaSection(
                        heading: 'Still looking for a platform?',
                        summary: 'Tell us what you needed and we will route you to the right hardware or the right engineer.',
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
            name: self::BRAND . ' Pre-order',
            title: 'Reserve a pre-order — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Reserve your place in the next production run',
                'A focused conversion page inviting platform pre-orders for the next manufacturing batch.',
            ),
            renderData: [
                'summary' => 'Reserve your place in the next production run — lock your platform configuration and delivery window.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Pre-order',
                        heading: 'Reserve your place in the next production run',
                        summary: 'Pre-order slots for the AX-7 and AX-12 open quarterly and fill fast. Configure your build and secure a delivery window today.',
                        media: $media['cta'][0],
                        mediaAlt: 'Axon platform pre-order',
                    ),
                    $this->preorderCtaSection(
                        heading: 'Lock your configuration and delivery window',
                        summary: 'Choose reach, payload, and end effector now. A deposit holds your slot in the next batch and locks current pricing.',
                    ),
                    $this->proofSection(
                        heading: 'Why teams pre-order from Axon',
                        summary: 'The delivery and uptime record behind every production run.',
                    ),
                    $this->ctaSection(
                        heading: 'One configuration away',
                        summary: 'Send your build over and we will confirm the slot and delivery window within one working day.',
                    ),
                ],
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function heroSection(string $eyebrow, string $heading, string $summary, ?string $media, ?string $mediaAlt): array
    {
        $section = [
            'type' => 'hero',
            'eyebrow' => $eyebrow,
            'heading' => $heading,
            'summary' => $summary,
            'actions' => [
                ['label' => 'Reserve a pre-order', 'url' => '#preorder', 'style' => 'primary'],
                ['label' => 'View specifications', 'url' => '#specs', 'style' => 'secondary'],
            ],
        ];

        if ($media !== null) {
            $section['mediaUrl'] = $media;
            $section['mediaAlt'] = $mediaAlt ?? $heading;
        }

        return $section;
    }

    /**
     * @return array<string, mixed>
     */
    private function specSheetSection(string $heading, string $summary): array
    {
        return [
            'type' => 'spec-sheet',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Payload', 'summary' => '12 kg at full extension, 18 kg at the inner work envelope.'],
                ['title' => 'Reach', 'summary' => '1,300 mm horizontal reach with a 2,600 mm working diameter.'],
                ['title' => 'Repeatability', 'summary' => '±0.02 mm pose repeatability across the full motion range.'],
                ['title' => 'Axes', 'summary' => 'Six controlled axes with harmonic drives on every joint.'],
                ['title' => 'Footprint', 'summary' => '320 mm base plate, floor, wall, or ceiling mounting.'],
                ['title' => 'Protection', 'summary' => 'IP67 wrist and IP54 body for wash-down and dusty cells.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function capabilitiesSection(string $heading, string $summary): array
    {
        return [
            'type' => 'capabilities',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'High-mix assembly', 'summary' => 'Sub-0.02 mm repeatability and fast tool changes for cells that switch product every shift.'],
                ['title' => 'Safe collaborative motion', 'summary' => 'Power and force limiting certified to ISO/TS 15066 so operators work alongside the arm without a fence.'],
                ['title' => 'Machine tending', 'summary' => 'IP67 wrist, vision-guided pick, and a 12 kg payload for loading CNC and injection cells around the clock.'],
                ['title' => 'Vision-guided picking', 'summary' => 'A calibrated camera mount and onboard pose solver locate parts in clutter without bespoke fixtures.'],
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
                ['title' => 'Open controller', 'summary' => 'Program in Python, ROS 2, or the Axon teach pendant — your controls team is never locked in.'],
                ['title' => 'Tool-free changeover', 'summary' => 'A quick-release tool flange swaps grippers and effectors in under a minute, no recalibration.'],
                ['title' => 'Real-time safety core', 'summary' => 'A redundant safety controller monitors speed, force, and zones at 1 kHz, independent of the motion loop.'],
                ['title' => 'Field-serviceable joints', 'summary' => 'Every joint module is line-replaceable on site, so a single failure never idles a whole cell.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function techDeepDiveSection(string $heading, string $summary): array
    {
        return [
            'type' => 'tech-deep-dive',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Dual-encoder feedback', 'summary' => 'Motor-side and joint-side encoders close the loop after the gearbox, cancelling backlash and thermal drift.'],
                ['title' => 'Harmonic reduction', 'summary' => 'Zero-backlash harmonic drives hold position under load without the hunting of planetary gears.'],
                ['title' => '1 kHz motion core', 'summary' => 'A hard real-time control loop plans and corrects trajectories every millisecond for smooth, accurate paths.'],
                ['title' => 'Thermal compensation', 'summary' => 'Onboard sensors model joint expansion and adjust targets so accuracy holds from cold start to full duty.'],
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
                ['title' => '99.6% uptime', 'summary' => 'Median availability across Axon cells in production over the last twelve months.'],
                ['title' => '5M+ cycles', 'summary' => 'Validated joint life before scheduled service, proven on accelerated test rigs.'],
                ['title' => '0.02 mm held', 'summary' => 'Repeatability measured on field units after a full year of three-shift operation.'],
                ['title' => '< 6 weeks', 'summary' => 'Typical lead time from confirmed configuration to a commissioned cell on the floor.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function preorderCtaSection(string $heading, string $summary): array
    {
        return [
            'type' => 'preorder-cta',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Configure your build', 'summary' => 'Pick reach, payload, end effector, and mounting — every combination is priced transparently.'],
                ['title' => 'Hold your slot', 'summary' => 'A refundable deposit reserves capacity in the next production batch and locks current pricing.'],
                ['title' => 'Confirmed delivery window', 'summary' => 'You receive a firm ship date the moment your configuration is confirmed, not an open-ended queue.'],
            ],
        ];
    }

    /**
     * @param  list<array<string, mixed>>|null  $items
     * @return array<string, mixed>
     */
    private function contentListingSection(string $heading, string $summary, ?array $items = null): array
    {
        return [
            'type' => 'content-listing',
            'heading' => $heading,
            'summary' => $summary,
            'items' => $items ?? [
                ['title' => 'AX-7 six-axis platform', 'summary' => '12 kg payload, 1.3 m reach — the high-mix assembly workhorse.'],
                ['title' => 'AX-12 heavy platform', 'summary' => '20 kg payload, 1.8 m reach for palletising and machine tending.'],
                ['title' => 'AX-S SCARA unit', 'summary' => 'Four-axis SCARA for fast, flat pick-and-place at 0.01 mm repeatability.'],
                ['title' => 'AX-M mobile base', 'summary' => 'An autonomous base that docks any AX arm to move work between cells.'],
                ['title' => 'Adaptive gripper kit', 'summary' => 'A two-finger adaptive gripper with force feedback for mixed part handling.'],
                ['title' => 'Axon motion controller', 'summary' => 'The shared real-time controller and safety core behind every platform.'],
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
                ['label' => 'Reserve a pre-order', 'url' => '#preorder', 'style' => 'primary'],
                ['label' => 'Talk to engineering', 'url' => '#contact', 'style' => 'secondary'],
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
                ['label' => 'Platforms', 'url' => '#platforms'],
                ['label' => 'Capabilities', 'url' => '#capabilities'],
                ['label' => 'Specs', 'url' => '#specs'],
                ['label' => 'Pre-order', 'url' => '#preorder'],
                ['label' => 'Contact', 'url' => '#contact'],
            ],
            'ctaLabel' => 'Reserve a pre-order',
            'ctaUrl' => '#preorder',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        return [
            'brandName' => self::BRAND,
            'summary' => 'Modular robotic platforms engineered for the production line. Cambridge, shipping worldwide.',
            'columns' => [
                [
                    'heading' => 'Hardware',
                    'links' => [
                        ['label' => 'Platforms', 'url' => '#platforms'],
                        ['label' => 'End effectors', 'url' => '#platforms'],
                        ['label' => 'Controllers', 'url' => '#specs'],
                        ['label' => 'Specifications', 'url' => '#specs'],
                    ],
                ],
                [
                    'heading' => 'Engineering',
                    'links' => [
                        ['label' => 'Capabilities', 'url' => '#capabilities'],
                        ['label' => 'Integration', 'url' => '#capabilities'],
                        ['label' => 'Safety & compliance', 'url' => '#specs'],
                        ['label' => 'Pre-order', 'url' => '#preorder'],
                    ],
                ],
                [
                    'heading' => 'Connect',
                    'links' => [
                        ['label' => 'Contact', 'url' => '#contact'],
                        ['label' => 'engineering@axonrobotics.example', 'url' => 'mailto:engineering@axonrobotics.example'],
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
