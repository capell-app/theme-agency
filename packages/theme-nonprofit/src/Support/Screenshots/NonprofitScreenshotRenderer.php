<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\Nonprofit\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class NonprofitScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-nonprofit::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (NonprofitScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-nonprofit::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#166534',
                accentColor: '#eab308',
                neutralColor: '#132016',
                headingFont: 'sora',
                bodyFont: 'inter',
                spacing: 'balanced',
                cardStyle: 'bordered',
                navigationStyle: 'prominent',
                layoutPresentation: 'structured',
                mediaTreatment: 'framed',
                radius: 'md',
                surfaceColor: '#f7fbf5',
                foregroundColor: '#132016',
                headingScale: 'balanced',
                cardDensity: 'compact',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'nonprofit',
        ])->render();

        return view('capell-theme-nonprofit::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, NonprofitScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'nonprofit-homepage-layout',
            'frontend-page-rendered-with-nonprofit-theme',
            'theme-admin-list-showing-nonprofit',
            'theme-preview-url-output' => [
                $this->navigation(),
                $this->hero(),
                $this->section('impact'),
                $this->section('campaigns'),
                $this->section('donation-impact'),
                $this->section('volunteer-donate'),
                $this->section('events'),
                $this->section('stories'),
                $this->section('annual-report-proof'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'nonprofit-campaigns-layout' => [
                $this->navigation(),
                $this->section('campaigns', [
                    'heading' => 'Appeals built to make the next gift specific',
                    'summary' => 'Structured appeal cards with progress and urgency keep campaigns sharper than a broad landing page.',
                ]),
                $this->section('donation-impact'),
                $this->section('cta'),
                $this->footer(),
            ],
            'nonprofit-impact-layout' => [
                $this->navigation(),
                $this->section('impact', [
                    'heading' => 'Outcomes a cause can show with confidence',
                    'summary' => 'Impact metrics and evidence cards prove results without turning the theme into corporate reporting.',
                ]),
                $this->section('annual-report-proof'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'nonprofit-volunteer-donate-layout' => [
                $this->navigation(),
                $this->section('volunteer-donate', [
                    'heading' => 'Two confident support routes, one clear path',
                    'summary' => 'Parallel donation and volunteer pathways keep practical support and giving accessible side by side.',
                ]),
                $this->section('volunteer-shifts'),
                $this->section('cta'),
                $this->footer(),
            ],
            'nonprofit-events-layout' => [
                $this->navigation(),
                $this->section('events', [
                    'heading' => 'Community events in the theme\'s own language',
                    'summary' => 'Event cards promote gatherings and volunteering opportunities with a consistent civic rhythm.',
                ]),
                $this->section('volunteer-shifts'),
                $this->section('cta'),
                $this->footer(),
            ],
            'nonprofit-stories-layout' => [
                $this->navigation(),
                $this->section('stories', [
                    'heading' => 'Human proof that leads back to support',
                    'summary' => 'Supporter and beneficiary stories carry emotion without drifting into case-study presentation.',
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
    private function section(string $sectionKey, array $data = []): NonprofitScreenshotSection
    {
        return new NonprofitScreenshotSection($sectionKey, $data);
    }

    private function navigation(): NonprofitScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Greenfield Trust',
            'items' => [
                ['label' => 'Campaigns', 'url' => '#campaigns'],
                ['label' => 'Impact', 'url' => '#impact'],
                ['label' => 'Volunteer', 'url' => '#volunteer-donate'],
                ['label' => 'Stories', 'url' => '#stories'],
            ],
            'consultationUrl' => '#volunteer-donate',
        ]);
    }

    private function hero(): NonprofitScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'A cause supporters can move behind',
            'eyebrow' => 'Nonprofit',
            'summary' => 'An impact-led civic homepage for campaigns, donations, volunteering, community events, and transparent stories.',
            'actions' => [
                ['label' => 'Donate now', 'url' => '#volunteer-donate'],
                ['label' => 'View campaigns', 'url' => '#campaigns'],
            ],
        ]);
    }

    private function footer(): NonprofitScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Greenfield Trust',
            'items' => [
                ['label' => 'Campaigns', 'url' => '#campaigns'],
                ['label' => 'Impact', 'url' => '#impact'],
                ['label' => 'Volunteer', 'url' => '#volunteer-donate'],
                ['label' => 'Stories', 'url' => '#stories'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'nonprofit-campaigns-layout' => 'Theme Nonprofit campaigns',
            'nonprofit-impact-layout' => 'Theme Nonprofit impact',
            'nonprofit-volunteer-donate-layout' => 'Theme Nonprofit volunteer and donate',
            'nonprofit-events-layout' => 'Theme Nonprofit events',
            'nonprofit-stories-layout' => 'Theme Nonprofit stories',
            'theme-admin-list-showing-nonprofit' => 'Theme Nonprofit admin list',
            'frontend-page-rendered-with-nonprofit-theme' => 'Theme Nonprofit frontend page',
            'theme-preview-url-output' => 'Theme Nonprofit preview',
            default => 'Theme Nonprofit homepage',
        };
    }
}
