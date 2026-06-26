<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\PersonalDev\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class PersonalDevScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-personal-dev::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (PersonalDevScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-personal-dev::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#111827',
                accentColor: '#6366f1',
                neutralColor: '#1f2937',
                headingFont: 'inter',
                bodyFont: 'inter',
                spacing: 'airy',
                cardStyle: 'flat',
                navigationStyle: 'minimal',
                layoutPresentation: 'editorial',
                mediaTreatment: 'flat',
                radius: 'sm',
                surfaceColor: '#fcfcfc',
                foregroundColor: '#111827',
                headingScale: 'balanced',
                cardDensity: 'comfortable',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'personal-dev',
        ])->render();

        return view('capell-theme-personal-dev::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, PersonalDevScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'personal-dev-homepage' => [
                $this->navigation(),
                $this->hero(),
                $this->section('about-intro'),
                $this->section('now'),
                $this->section('writing-index'),
                $this->section('projects'),
                $this->section('features'),
                $this->section('proof'),
                $this->section('newsletter-inline'),
                $this->section('cta'),
                $this->footer(),
            ],
            'personal-dev-directory' => [
                $this->navigation(),
                $this->section('writing-index', [
                    'heading' => 'Everything I have written, in one scannable index',
                    'summary' => 'An editorial writing directory keeps essays and notes legible without the theme owning content records.',
                ]),
                $this->section('content-listing'),
                $this->section('cta'),
                $this->footer(),
            ],
            'personal-dev-detail' => [
                $this->navigation(),
                $this->section('about-intro', [
                    'heading' => 'A focused detail surface for a single piece',
                    'summary' => 'Detail pages stay quiet and typographic so the reader keeps their attention on the writing itself.',
                ]),
                $this->section('proof'),
                $this->section('newsletter-inline'),
                $this->footer(),
            ],
            'personal-dev-contact' => [
                $this->navigation(),
                $this->section('newsletter-inline', [
                    'heading' => 'Stay in touch without a heavy contact form',
                    'summary' => 'A non-submitting newsletter and contact prompt proves the conversation path feels native to the site.',
                ]),
                $this->section('cta'),
                $this->footer(),
            ],
            'personal-dev-empty' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'Nothing here yet, and that is fine',
                    'summary' => 'The empty state stays calm and on-brand so a fresh site never looks broken.',
                ]),
                $this->footer(),
            ],
            'personal-dev-not-found' => [
                $this->navigation(),
                $this->section('about-intro', [
                    'heading' => 'That page wandered off',
                    'summary' => 'A typographic 404 keeps the reader oriented and offers a clear way back into the writing.',
                ]),
                $this->section('cta'),
                $this->footer(),
            ],
            'personal-dev-cta' => [
                $this->navigation(),
                $this->section('cta', [
                    'heading' => 'One confident invitation to subscribe',
                    'summary' => 'A focused conversion CTA proves the theme can sell a newsletter or product without feeling loud.',
                ]),
                $this->section('newsletter-inline'),
                $this->footer(),
            ],
            'personal-dev-writing' => [
                $this->navigation(),
                $this->section('writing-index', [
                    'heading' => 'Writing that reads like a well-set page',
                    'summary' => 'Essays and notes stay premium and scannable while the theme leaves content ownership to Capell.',
                ]),
                $this->section('content-listing'),
                $this->section('newsletter-inline'),
                $this->footer(),
            ],
            'personal-dev-projects' => [
                $this->navigation(),
                $this->section('projects', [
                    'heading' => 'Projects framed as a working portfolio',
                    'summary' => 'A project index pairs context and outcomes so visitors understand the work without leaving the page.',
                ]),
                $this->section('features'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'personal-dev-now' => [
                $this->navigation(),
                $this->section('now', [
                    'heading' => 'A now page that shows current focus',
                    'summary' => 'The now surface keeps present priorities visible and human without owning any structured records.',
                ]),
                $this->section('writing-index'),
                $this->section('cta'),
                $this->footer(),
            ],
            default => [],
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function section(string $sectionKey, array $data = []): PersonalDevScreenshotSection
    {
        return new PersonalDevScreenshotSection($sectionKey, $data);
    }

    private function navigation(): PersonalDevScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Alex Rivers',
            'items' => [
                ['label' => 'Writing', 'url' => '#writing'],
                ['label' => 'Projects', 'url' => '#projects'],
                ['label' => 'Now', 'url' => '#now'],
                ['label' => 'Contact', 'url' => '#contact'],
            ],
            'subscribeUrl' => '#subscribe',
        ]);
    }

    private function hero(): PersonalDevScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'A personal site for writing, projects, and a public now page',
            'eyebrow' => 'Personal Dev',
            'summary' => 'A minimal, typography-led homepage for developers and writers who want portable content and quiet, premium output.',
            'actions' => [
                ['label' => 'Read the writing', 'url' => '#writing'],
                ['label' => 'See projects', 'url' => '#projects'],
            ],
        ]);
    }

    private function footer(): PersonalDevScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Alex Rivers',
            'items' => [
                ['label' => 'Writing', 'url' => '#writing'],
                ['label' => 'Projects', 'url' => '#projects'],
                ['label' => 'Now', 'url' => '#now'],
                ['label' => 'Contact', 'url' => '#contact'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'personal-dev-directory' => 'Theme Personal Dev directory',
            'personal-dev-detail' => 'Theme Personal Dev detail',
            'personal-dev-contact' => 'Theme Personal Dev contact',
            'personal-dev-empty' => 'Theme Personal Dev empty state',
            'personal-dev-not-found' => 'Theme Personal Dev 404 state',
            'personal-dev-cta' => 'Theme Personal Dev conversion CTA',
            'personal-dev-writing' => 'Theme Personal Dev writing',
            'personal-dev-projects' => 'Theme Personal Dev projects',
            'personal-dev-now' => 'Theme Personal Dev now page',
            default => 'Theme Personal Dev homepage',
        };
    }
}
