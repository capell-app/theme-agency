<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\FitnessWellness\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class FitnessWellnessScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-fitness-wellness::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (FitnessWellnessScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-fitness-wellness::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#84cc16',
                accentColor: '#f97316',
                neutralColor: '#18181b',
                headingFont: 'sora',
                bodyFont: 'inter',
                spacing: 'balanced',
                cardStyle: 'elevated',
                navigationStyle: 'prominent',
                layoutPresentation: 'immersive',
                mediaTreatment: 'framed',
                radius: 'lg',
                surfaceColor: '#0f1115',
                foregroundColor: '#f4f4f5',
                headingScale: 'dramatic',
                cardDensity: 'comfortable',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'fitness-wellness',
        ])->render();

        return view('capell-theme-fitness-wellness::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, FitnessWellnessScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'fitness-wellness-homepage' => [
                $this->navigation(),
                $this->hero(),
                $this->section('class-schedule'),
                $this->section('coach-profiles'),
                $this->section('membership-plans'),
                $this->section('challenge-board'),
                $this->section('features'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'fitness-wellness-directory' => [
                $this->navigation(),
                $this->section('class-schedule', [
                    'heading' => 'Browse the full studio timetable',
                    'summary' => 'Strength, conditioning, and recovery sessions stay scannable across the week without the theme owning class records.',
                ]),
                $this->section('coach-profiles'),
                $this->section('features'),
                $this->footer(),
            ],
            'fitness-wellness-detail' => [
                $this->navigation(),
                $this->section('coach-profiles', [
                    'heading' => 'Meet the coach behind the session',
                    'summary' => 'A focused coach detail pairs specialism, credentials, and proof so members book with confidence.',
                ]),
                $this->section('class-schedule'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'fitness-wellness-contact' => [
                $this->navigation(),
                $this->section('cta', [
                    'heading' => 'Talk to the team and book a free trial',
                    'summary' => 'A non-submitting contact CTA proves the enquiry journey feels like part of the studio experience.',
                ]),
                $this->section('features'),
                $this->footer(),
            ],
            'fitness-wellness-empty' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'Nothing scheduled here yet',
                    'summary' => 'An intentional empty state keeps the studio feeling premium when a listing has no records to show.',
                ]),
                $this->footer(),
            ],
            'fitness-wellness-not-found' => [
                $this->navigation(),
                $this->section('hero', [
                    'heading' => 'That page took a rest day',
                    'summary' => 'A branded 404 state guides lost members back to classes, coaches, and membership without breaking the experience.',
                ]),
                $this->section('cta'),
                $this->footer(),
            ],
            'fitness-wellness-cta' => [
                $this->navigation(),
                $this->section('cta', [
                    'heading' => 'Start your free trial this week',
                    'summary' => 'A confident single-path conversion CTA turns interest into a booked first session.',
                ]),
                $this->section('membership-plans'),
                $this->section('proof'),
                $this->footer(),
            ],
            'fitness-wellness-classes' => [
                $this->navigation(),
                $this->section('class-schedule', [
                    'heading' => '40+ studio classes every week',
                    'summary' => 'Editorial class groupings keep the timetable premium and legible without the theme owning schedule records.',
                ]),
                $this->section('challenge-board'),
                $this->section('cta'),
                $this->footer(),
            ],
            'fitness-wellness-coaches' => [
                $this->navigation(),
                $this->section('coach-profiles', [
                    'heading' => 'Coaches who know your goals',
                    'summary' => 'Specialist coach profiles pair credentials and proof so members choose the right session.',
                ]),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'fitness-wellness-membership' => [
                $this->navigation(),
                $this->section('membership-plans', [
                    'heading' => 'Membership built around your week',
                    'summary' => 'Clear tiers pair access, recovery, and coaching so members pick a plan with confidence.',
                ]),
                $this->section('nutrition-guides'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            default => [],
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function section(string $sectionKey, array $data = []): FitnessWellnessScreenshotSection
    {
        return new FitnessWellnessScreenshotSection($sectionKey, $data);
    }

    private function navigation(): FitnessWellnessScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Forge Strength & Wellness',
            'items' => [
                ['label' => 'Classes', 'url' => '#classes'],
                ['label' => 'Coaches', 'url' => '#coaches'],
                ['label' => 'Membership', 'url' => '#membership'],
                ['label' => 'Contact', 'url' => '#contact'],
            ],
            'reservationUrl' => '#trial',
        ]);
    }

    private function hero(): FitnessWellnessScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'Train stronger in central Leeds',
            'eyebrow' => 'Fitness & Wellness',
            'summary' => 'A coached strength floor, 40+ studio classes a week, and recovery rooms under one roof. Book a free trial and feel the difference in a week.',
            'actions' => [
                ['label' => 'Book a free trial', 'url' => '#trial'],
                ['label' => 'View the timetable', 'url' => '#classes'],
            ],
        ]);
    }

    private function footer(): FitnessWellnessScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Forge Strength & Wellness',
            'items' => [
                ['label' => 'Classes', 'url' => '#classes'],
                ['label' => 'Coaches', 'url' => '#coaches'],
                ['label' => 'Membership', 'url' => '#membership'],
                ['label' => 'Contact', 'url' => '#contact'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'fitness-wellness-directory' => 'Theme Fitness & Wellness directory',
            'fitness-wellness-detail' => 'Theme Fitness & Wellness detail',
            'fitness-wellness-contact' => 'Theme Fitness & Wellness contact',
            'fitness-wellness-empty' => 'Theme Fitness & Wellness empty state',
            'fitness-wellness-not-found' => 'Theme Fitness & Wellness 404 state',
            'fitness-wellness-cta' => 'Theme Fitness & Wellness conversion CTA',
            'fitness-wellness-classes' => 'Theme Fitness & Wellness classes',
            'fitness-wellness-coaches' => 'Theme Fitness & Wellness coaches',
            'fitness-wellness-membership' => 'Theme Fitness & Wellness membership',
            default => 'Theme Fitness & Wellness homepage',
        };
    }
}
