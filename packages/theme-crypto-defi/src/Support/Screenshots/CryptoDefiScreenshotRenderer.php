<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\CryptoDefi\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class CryptoDefiScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-crypto-defi::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (CryptoDefiScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-crypto-defi::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#8b5cf6',
                accentColor: '#22d3ee',
                neutralColor: '#1e1b3a',
                headingFont: 'space-grotesk',
                bodyFont: 'inter',
                spacing: 'airy',
                cardStyle: 'flat',
                navigationStyle: 'minimal',
                layoutPresentation: 'immersive',
                mediaTreatment: 'duotone',
                radius: 'lg',
                surfaceColor: '#0a0118',
                foregroundColor: '#ede9fe',
                headingScale: 'dramatic',
                cardDensity: 'comfortable',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'crypto-defi',
        ])->render();

        return view('capell-theme-crypto-defi::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, CryptoDefiScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'crypto-defi-homepage' => [
                $this->navigation(),
                $this->hero(),
                $this->section('protocol-stats'),
                $this->section('token-metrics'),
                $this->section('features'),
                $this->section('how-it-works'),
                $this->section('audit-badges'),
                $this->section('proof'),
                $this->section('wallet-cta'),
                $this->section('cta'),
                $this->footer(),
            ],
            'crypto-defi-directory' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'Every market in one scannable directory',
                    'summary' => 'Structured listing cards keep the protocol legible without the theme owning on-chain market records.',
                ]),
                $this->section('protocol-stats'),
                $this->section('cta'),
                $this->footer(),
            ],
            'crypto-defi-detail' => [
                $this->navigation(),
                $this->section('token-metrics', [
                    'heading' => 'A market view that reads with on-chain clarity',
                    'summary' => 'A single market pairs live metrics and audit proof so depositors can engage with confidence.',
                ]),
                $this->section('audit-badges'),
                $this->section('proof'),
                $this->section('wallet-cta'),
                $this->footer(),
            ],
            'crypto-defi-contact' => [
                $this->navigation(),
                $this->section('wallet-cta', [
                    'heading' => 'Connect through one confident path',
                    'summary' => 'A non-submitting wallet CTA proves the connect journey feels like part of the protocol experience.',
                ]),
                $this->section('features'),
                $this->footer(),
            ],
            'crypto-defi-empty' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'No markets live here yet',
                    'summary' => 'An empty listing state stays immersive and structured while the protocol prepares its markets.',
                ]),
                $this->footer(),
            ],
            'crypto-defi-not-found' => [
                $this->navigation(),
                $this->section('cta', [
                    'heading' => 'That page could not be found',
                    'summary' => 'A 404 state keeps the protocol on-brand and routes visitors back into the market journey.',
                ]),
                $this->footer(),
            ],
            'crypto-defi-cta' => [
                $this->navigation(),
                $this->section('wallet-cta'),
                $this->section('proof'),
                $this->section('cta', [
                    'heading' => 'Turn intent into a connected wallet',
                    'summary' => 'A conversion-focused CTA stack keeps the path to depositing into the protocol direct and immersive.',
                ]),
                $this->footer(),
            ],
            'crypto-defi-markets' => [
                $this->navigation(),
                $this->section('token-metrics', [
                    'heading' => 'Markets built to be scanned and trusted',
                    'summary' => 'Live token metrics keep each market legible without the theme owning on-chain records.',
                ]),
                $this->section('protocol-stats'),
                $this->section('content-listing'),
                $this->section('cta'),
                $this->footer(),
            ],
            'crypto-defi-how-it-works' => [
                $this->navigation(),
                $this->section('how-it-works', [
                    'heading' => 'How the protocol works, step by step',
                    'summary' => 'A structured walkthrough keeps deposits, yield, and borrowing legible for first-time depositors.',
                ]),
                $this->section('features'),
                $this->section('cta'),
                $this->footer(),
            ],
            'crypto-defi-audit' => [
                $this->navigation(),
                $this->section('audit-badges', [
                    'heading' => 'Audited contracts and transparent risk',
                    'summary' => 'Structured audit proof keeps the protocol trustworthy without the theme owning security records.',
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
    private function section(string $sectionKey, array $data = []): CryptoDefiScreenshotSection
    {
        return new CryptoDefiScreenshotSection($sectionKey, $data);
    }

    private function navigation(): CryptoDefiScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Helix',
            'items' => [
                ['label' => 'Markets', 'url' => '#markets'],
                ['label' => 'How it works', 'url' => '#how-it-works'],
                ['label' => 'Audit', 'url' => '#audit'],
                ['label' => 'Connect wallet', 'url' => '#wallet'],
            ],
            'consultationUrl' => '#wallet',
        ]);
    }

    private function hero(): CryptoDefiScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'A permissionless lending market settled in seconds',
            'eyebrow' => 'Crypto DeFi',
            'summary' => 'An immersive Web3 homepage for protocol stats, token metrics, audited contracts, and wallet-led journeys.',
            'actions' => [
                ['label' => 'Connect wallet', 'url' => '#wallet'],
                ['label' => 'View markets', 'url' => '#markets'],
            ],
        ]);
    }

    private function footer(): CryptoDefiScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Helix',
            'items' => [
                ['label' => 'Markets', 'url' => '#markets'],
                ['label' => 'How it works', 'url' => '#how-it-works'],
                ['label' => 'Audit', 'url' => '#audit'],
                ['label' => 'Connect wallet', 'url' => '#wallet'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'crypto-defi-directory' => 'Theme Crypto DeFi directory',
            'crypto-defi-detail' => 'Theme Crypto DeFi detail',
            'crypto-defi-contact' => 'Theme Crypto DeFi contact',
            'crypto-defi-empty' => 'Theme Crypto DeFi empty state',
            'crypto-defi-not-found' => 'Theme Crypto DeFi 404 state',
            'crypto-defi-cta' => 'Theme Crypto DeFi conversion CTA',
            'crypto-defi-markets' => 'Theme Crypto DeFi markets',
            'crypto-defi-how-it-works' => 'Theme Crypto DeFi how it works',
            'crypto-defi-audit' => 'Theme Crypto DeFi audit & risk',
            default => 'Theme Crypto DeFi homepage',
        };
    }
}
