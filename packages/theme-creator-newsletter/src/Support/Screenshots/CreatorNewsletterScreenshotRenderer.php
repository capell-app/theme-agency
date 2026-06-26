<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\CreatorNewsletter\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class CreatorNewsletterScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-creator-newsletter::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (CreatorNewsletterScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-creator-newsletter::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#e11d48',
                accentColor: '#7c3aed',
                neutralColor: '#2a1620',
                headingFont: 'sora',
                bodyFont: 'inter',
                spacing: 'balanced',
                cardStyle: 'elevated',
                navigationStyle: 'prominent',
                layoutPresentation: 'structured',
                mediaTreatment: 'flat',
                radius: 'xl',
                surfaceColor: '#fff8f6',
                foregroundColor: '#2a1620',
                headingScale: 'balanced',
                cardDensity: 'comfortable',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'creator-newsletter',
        ])->render();

        return view('capell-theme-creator-newsletter::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, CreatorNewsletterScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'creator-newsletter-homepage' => [
                $this->navigation(),
                $this->hero(),
                $this->section('subscribe-hero'),
                $this->section('features'),
                $this->section('archive'),
                $this->section('testimonials'),
                $this->section('sponsors'),
                $this->section('about-author'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'creator-newsletter-directory' => [
                $this->navigation(),
                $this->section('archive', [
                    'heading' => 'Every issue, organised to be browsed',
                    'summary' => 'A structured archive keeps past newsletters legible and inviting without the theme owning issue records.',
                ]),
                $this->section('content-listing'),
                $this->section('cta'),
                $this->footer(),
            ],
            'creator-newsletter-detail' => [
                $this->navigation(),
                $this->section('archive', [
                    'heading' => 'A single issue that reads beautifully',
                    'summary' => 'An individual issue view pairs the writing with proof so readers feel the value before they subscribe.',
                ]),
                $this->section('about-author'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'creator-newsletter-contact' => [
                $this->navigation(),
                $this->section('subscribe-hero', [
                    'heading' => 'Reach the creator through one warm path',
                    'summary' => 'A non-submitting subscribe CTA proves the contact journey feels like part of the newsletter experience.',
                ]),
                $this->section('features'),
                $this->footer(),
            ],
            'creator-newsletter-empty' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'Nothing published here yet',
                    'summary' => 'An empty archive state stays warm and structured while the creator prepares the next issue.',
                ]),
                $this->footer(),
            ],
            'creator-newsletter-not-found' => [
                $this->navigation(),
                $this->section('cta', [
                    'heading' => 'That page could not be found',
                    'summary' => 'A 404 state keeps the newsletter inviting and routes readers back into the subscribe journey.',
                ]),
                $this->footer(),
            ],
            'creator-newsletter-cta' => [
                $this->navigation(),
                $this->section('subscribe-hero'),
                $this->section('proof'),
                $this->section('cta', [
                    'heading' => 'Turn a reader into a subscriber',
                    'summary' => 'A conversion-focused CTA stack keeps the path to joining the newsletter direct and warm.',
                ]),
                $this->footer(),
            ],
            'creator-newsletter-archive' => [
                $this->navigation(),
                $this->section('archive', [
                    'heading' => 'Browse the full back catalogue of issues',
                    'summary' => 'A structured archive keeps every past newsletter legible and inviting without owning issue records.',
                ]),
                $this->section('content-listing'),
                $this->section('cta'),
                $this->footer(),
            ],
            'creator-newsletter-author' => [
                $this->navigation(),
                $this->section('about-author', [
                    'heading' => 'Meet the creator behind the newsletter',
                    'summary' => 'An editorial author profile keeps the voice personal and trusted without the theme owning people records.',
                ]),
                $this->section('testimonials'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'creator-newsletter-subscribe' => [
                $this->navigation(),
                $this->section('subscribe-hero', [
                    'heading' => 'Subscribe in one warm, confident path',
                    'summary' => 'A non-submitting subscribe CTA proves the signup journey feels like part of the newsletter experience.',
                ]),
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
    private function section(string $sectionKey, array $data = []): CreatorNewsletterScreenshotSection
    {
        return new CreatorNewsletterScreenshotSection($sectionKey, $data);
    }

    private function navigation(): CreatorNewsletterScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'The Slow Dispatch',
            'items' => [
                ['label' => 'Latest issue', 'url' => '#latest'],
                ['label' => 'Archive', 'url' => '#archive'],
                ['label' => 'About', 'url' => '#about'],
                ['label' => 'Subscribe', 'url' => '#subscribe'],
            ],
            'consultationUrl' => '#subscribe',
        ]);
    }

    private function hero(): CreatorNewsletterScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'A newsletter readers look forward to',
            'eyebrow' => 'Creator Newsletter',
            'summary' => 'A warm, conversion-led homepage for issues, archives, author voice, sponsors, and subscribe-first journeys.',
            'actions' => [
                ['label' => 'Subscribe free', 'url' => '#subscribe'],
                ['label' => 'Read the archive', 'url' => '#archive'],
            ],
        ]);
    }

    private function footer(): CreatorNewsletterScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'The Slow Dispatch',
            'items' => [
                ['label' => 'Latest issue', 'url' => '#latest'],
                ['label' => 'Archive', 'url' => '#archive'],
                ['label' => 'About', 'url' => '#about'],
                ['label' => 'Subscribe', 'url' => '#subscribe'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'creator-newsletter-directory' => 'Theme Creator Newsletter directory',
            'creator-newsletter-detail' => 'Theme Creator Newsletter detail',
            'creator-newsletter-contact' => 'Theme Creator Newsletter contact',
            'creator-newsletter-empty' => 'Theme Creator Newsletter empty state',
            'creator-newsletter-not-found' => 'Theme Creator Newsletter 404 state',
            'creator-newsletter-cta' => 'Theme Creator Newsletter conversion CTA',
            'creator-newsletter-archive' => 'Theme Creator Newsletter archive',
            'creator-newsletter-author' => 'Theme Creator Newsletter author',
            'creator-newsletter-subscribe' => 'Theme Creator Newsletter subscribe',
            default => 'Theme Creator Newsletter homepage',
        };
    }
}
