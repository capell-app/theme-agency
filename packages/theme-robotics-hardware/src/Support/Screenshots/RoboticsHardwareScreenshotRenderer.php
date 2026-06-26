<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\RoboticsHardware\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class RoboticsHardwareScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-robotics-hardware::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (RoboticsHardwareScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-robotics-hardware::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#111827',
                accentColor: '#f97316',
                neutralColor: '#1c1917',
                headingFont: 'space-grotesk',
                bodyFont: 'inter',
                spacing: 'airy',
                cardStyle: 'elevated',
                navigationStyle: 'prominent',
                layoutPresentation: 'immersive',
                mediaTreatment: 'framed',
                radius: 'md',
                surfaceColor: '#f5f5f4',
                foregroundColor: '#111827',
                headingScale: 'dramatic',
                cardDensity: 'comfortable',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'robotics-hardware',
        ])->render();

        return view('capell-theme-robotics-hardware::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, RoboticsHardwareScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'robotics-hardware-homepage' => [
                $this->navigation(),
                $this->videoHero(),
                $this->section('spec-sheet', [
                    'heading' => 'Engineered specifications buyers can trust',
                    'summary' => 'Key dimensions, payloads, and tolerances stay legible so deep-tech buyers can qualify the platform fast.',
                ]),
                $this->section('capabilities', [
                    'heading' => 'Capabilities tuned for the line and the lab',
                    'summary' => 'Modular tooling, safe motion envelopes, and repeatable accuracy framed for engineering-led evaluation.',
                ]),
                $this->section('features'),
                $this->section('tech-deep-dive'),
                $this->section('proof'),
                $this->section('preorder-cta'),
                $this->section('cta'),
                $this->footer(),
            ],
            'robotics-hardware-directory' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'Browse the robotics hardware catalogue',
                    'summary' => 'A scannable directory of platforms, end effectors, and controllers without the theme owning product records.',
                ]),
                $this->section('features'),
                $this->section('cta'),
                $this->footer(),
            ],
            'robotics-hardware-detail' => [
                $this->navigation(),
                $this->videoHero(),
                $this->section('spec-sheet', [
                    'heading' => 'A single platform, fully specified',
                    'summary' => 'Detail pages pair the spec sheet with deep-dive context so a buyer can commit with confidence.',
                ]),
                $this->section('tech-deep-dive'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'robotics-hardware-contact' => [
                $this->navigation(),
                $this->section('cta', [
                    'heading' => 'Talk to the robotics engineering team',
                    'summary' => 'A non-submitting contact CTA proves the enquiry path feels like part of the engineering-led brand.',
                ]),
                $this->section('proof'),
                $this->footer(),
            ],
            'robotics-hardware-empty' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'No hardware listed yet',
                    'summary' => 'The empty directory state stays premium and on-brand while a catalogue is still being populated.',
                ]),
                $this->footer(),
            ],
            'robotics-hardware-not-found' => [
                $this->navigation(),
                $this->section('cta', [
                    'heading' => 'This page has powered down',
                    'summary' => 'A graphite 404 keeps buyers oriented and routes them back to the robotics hardware catalogue.',
                ]),
                $this->footer(),
            ],
            'robotics-hardware-cta' => [
                $this->navigation(),
                $this->section('preorder-cta', [
                    'heading' => 'Reserve your place in the next production run',
                    'summary' => 'A focused conversion CTA frames the pre-order journey as part of the engineering-led launch.',
                ]),
                $this->section('cta'),
                $this->footer(),
            ],
            'robotics-hardware-product' => [
                $this->navigation(),
                $this->videoHero(),
                $this->section('capabilities', [
                    'heading' => 'What this platform does on day one',
                    'summary' => 'Product pages lead with capabilities and proof so buyers grasp the value before reading the spec sheet.',
                ]),
                $this->section('features'),
                $this->section('proof'),
                $this->section('preorder-cta'),
                $this->footer(),
            ],
            'robotics-hardware-specs' => [
                $this->navigation(),
                $this->section('spec-sheet', [
                    'heading' => 'Full specifications, ready to scan',
                    'summary' => 'A dense, legible spec sheet lets engineers compare tolerances, payloads, and interfaces at a glance.',
                ]),
                $this->section('tech-deep-dive'),
                $this->section('cta'),
                $this->footer(),
            ],
            'robotics-hardware-preorder' => [
                $this->navigation(),
                $this->section('preorder-cta', [
                    'heading' => 'Pre-order the next robotics platform',
                    'summary' => 'A confident pre-order panel pairs timeline and proof so launch buyers commit without leaving the venue story.',
                ]),
                $this->section('spec-sheet'),
                $this->section('proof'),
                $this->footer(),
            ],
            default => [],
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function section(string $sectionKey, array $data = []): RoboticsHardwareScreenshotSection
    {
        return new RoboticsHardwareScreenshotSection($sectionKey, $data);
    }

    private function navigation(): RoboticsHardwareScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Axon Robotics',
            'items' => [
                ['label' => 'Platforms', 'url' => '#platforms'],
                ['label' => 'Capabilities', 'url' => '#capabilities'],
                ['label' => 'Specs', 'url' => '#specs'],
                ['label' => 'Pre-order', 'url' => '#preorder'],
            ],
            'preorderUrl' => '#preorder',
        ]);
    }

    private function videoHero(): RoboticsHardwareScreenshotSection
    {
        return $this->section('video-hero', [
            'heading' => 'Robotics hardware engineered for the next production run',
            'eyebrow' => 'Robotics Hardware',
            'summary' => 'An immersive deep-tech homepage for platforms, capabilities, specifications, proof, and pre-order journeys.',
            'actions' => [
                ['label' => 'Reserve a pre-order', 'url' => '#preorder'],
                ['label' => 'View specifications', 'url' => '#specs'],
            ],
        ]);
    }

    private function footer(): RoboticsHardwareScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Axon Robotics',
            'items' => [
                ['label' => 'Platforms', 'url' => '#platforms'],
                ['label' => 'Capabilities', 'url' => '#capabilities'],
                ['label' => 'Specs', 'url' => '#specs'],
                ['label' => 'Pre-order', 'url' => '#preorder'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'robotics-hardware-directory' => 'Theme Robotics Hardware directory',
            'robotics-hardware-detail' => 'Theme Robotics Hardware detail',
            'robotics-hardware-contact' => 'Theme Robotics Hardware contact',
            'robotics-hardware-empty' => 'Theme Robotics Hardware empty state',
            'robotics-hardware-not-found' => 'Theme Robotics Hardware 404 state',
            'robotics-hardware-cta' => 'Theme Robotics Hardware conversion CTA',
            'robotics-hardware-product' => 'Theme Robotics Hardware product',
            'robotics-hardware-specs' => 'Theme Robotics Hardware specs',
            'robotics-hardware-preorder' => 'Theme Robotics Hardware preorder',
            default => 'Theme Robotics Hardware homepage',
        };
    }
}
