<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\CryptoDefi\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Crypto DeFi (Helix) theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the page
 * adapter emits the theme's signature Web3 renderers (protocol-stats / token-metrics
 * / how-it-works / audit-badges / wallet-cta) alongside the standard hero / proof /
 * cta — giving every surface a full, individual protocol site rather than the shared
 * five-section skeleton.
 */
final class CryptoDefiDemoContent implements ProvidesThemeDemoContent
{
    private const BRAND = 'Helix';

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
            title: self::BRAND . ' — Permissionless Lending Market',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'A permissionless lending market settled in seconds',
                'Helix lets depositors earn yield and borrowers tap instant liquidity, secured by audited contracts and settled on-chain in seconds.',
            ),
            renderData: [
                'summary' => 'Helix is a permissionless lending market where deposits earn yield and borrowers tap instant liquidity — settled in seconds, secured by audited contracts.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        heading: 'A permissionless lending market settled in seconds',
                        summary: 'Deposit to earn protocol yield, or borrow against your collateral with instant on-chain liquidity. No intermediaries, no waiting — just audited contracts doing the work.',
                        mediaUrl: $media['hero'][0],
                        mediaAlt: 'Helix protocol dashboard',
                    ),
                    $this->protocolStatsSection(),
                    $this->tokenMetricsSection(
                        heading: 'Live market metrics, on-chain and transparent',
                        summary: 'Every market exposes its supply APY, borrow APY, and utilisation in real time — read straight from the protocol.',
                    ),
                    $this->featuresSection(),
                    $this->howItWorksSection(),
                    $this->auditBadgesSection(),
                    $this->proofSection(
                        heading: 'Trusted by on-chain depositors and DAOs',
                        summary: 'What the numbers look like after two years of continuous, exploit-free operation.',
                    ),
                    $this->walletCtaSection(
                        heading: 'Connect your wallet to start earning',
                        summary: 'Supply USDC, ETH, or wstETH in a single transaction and start accruing yield the moment the block confirms.',
                    ),
                    $this->ctaSection(
                        heading: 'Ready to put your assets to work?',
                        summary: 'Open the app, connect a wallet, and supply your first market in under a minute.',
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
            name: self::BRAND . ' Markets',
            title: 'Markets — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'Every market in one scannable directory',
                'Browse every supply and borrow market on Helix, with live APY and utilisation read straight from the chain.',
            ),
            renderData: [
                'summary' => 'Every supply and borrow market on Helix in one scannable directory, with live APY and utilisation.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        heading: 'Every market in one scannable directory',
                        summary: 'Structured listing cards keep the protocol legible — sort by supply APY, borrow APY, or total liquidity without leaving the page.',
                        mediaUrl: $media['listing'][0] ?? $media['hero'][0],
                        mediaAlt: 'Helix markets directory',
                    ),
                    $this->marketsListingSection(),
                    $this->protocolStatsSection(),
                    $this->ctaSection(
                        heading: 'Found a market that fits your strategy?',
                        summary: 'Connect a wallet and supply or borrow directly from the market page.',
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
            name: self::BRAND . ' Market',
            title: 'USDC Market — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'USDC market — a view that reads with on-chain clarity',
                'The Helix USDC market pairs live metrics with audit proof so depositors can supply liquidity with confidence.',
            ),
            renderData: [
                'summary' => 'The USDC market pairs live metrics and audit proof so depositors can supply with confidence.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        heading: 'USDC market — a view that reads with on-chain clarity',
                        summary: 'Supply APY, borrow APY, utilisation, and reserve factor for the USDC pool, paired with the audit trail behind it.',
                        mediaUrl: $media['detail'][0],
                        mediaAlt: 'Helix USDC market detail',
                    ),
                    $this->tokenMetricsSection(
                        heading: 'A market view that reads with on-chain clarity',
                        summary: 'A single market pairs live metrics and audit proof so depositors can engage with confidence.',
                    ),
                    $this->auditBadgesSection(),
                    $this->proofSection(
                        heading: 'Why depositors trust this market',
                        summary: 'The track record behind the USDC pool since it went live.',
                    ),
                    $this->walletCtaSection(
                        heading: 'Supply USDC and start earning',
                        summary: 'Approve once, supply in the next transaction, and withdraw any time — your position stays fully liquid.',
                    ),
                    $this->ctaSection(
                        heading: 'Supply this market in under a minute',
                        summary: 'Connect a wallet and deposit into the USDC pool directly from this page.',
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
            name: self::BRAND . ' Connect',
            title: 'Connect a wallet — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Connect through one confident path',
                'Connect a wallet to supply, borrow, and manage positions on Helix — the journey feels like part of the protocol.',
            ),
            renderData: [
                'summary' => 'Connect a wallet to supply, borrow, and manage positions on Helix.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        heading: 'Connect through one confident path',
                        summary: 'Helix supports MetaMask, Coinbase Wallet, WalletConnect, and Ledger. Pick a wallet, sign once, and you are in the protocol.',
                        mediaUrl: $media['contact'][0],
                        mediaAlt: 'Connect a wallet to Helix',
                    ),
                    $this->walletCtaSection(
                        heading: 'Connect through one confident path',
                        summary: 'A guided connect flow proves the wallet journey feels like part of the protocol experience — no detours, no dead ends.',
                    ),
                    $this->featuresSection(),
                    $this->ctaSection(
                        heading: 'New to on-chain lending?',
                        summary: 'Read the docs, join the Discord, or jump straight into a testnet market before committing real assets.',
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
            name: self::BRAND . ' No Markets',
            title: 'No markets yet — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'No markets live here yet',
                'A graceful empty state for a filtered market directory with no matching pools.',
            ),
            renderData: [
                'summary' => 'No markets match that filter yet — but the protocol can still point you somewhere useful.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        heading: 'No markets live here yet',
                        summary: 'Nothing matches that filter right now. Clear it to see every live pool, or check the deployment roadmap for what is coming next.',
                        mediaUrl: $media['hero'][0],
                        mediaAlt: 'Empty Helix markets state',
                    ),
                    [
                        'type' => 'content-listing',
                        'heading' => 'No markets live here yet',
                        'summary' => 'An empty listing state stays immersive and structured while the protocol prepares its markets.',
                        'items' => [],
                    ],
                    $this->featuresSection(),
                    $this->ctaSection(
                        heading: 'Want to be first into a new market?',
                        summary: 'Follow the protocol on-chain and get notified the moment a new pool goes live.',
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
                'That page could not be found',
                'A not-found page that routes visitors back into the market and wallet journeys.',
            ),
            renderData: [
                'summary' => 'That block never confirmed — here is the way back into the protocol.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        heading: 'That page could not be found',
                        summary: 'The link is broken or the page has moved. Head back to the markets, or connect a wallet to pick up where you left off.',
                        mediaUrl: $media['hero'][0],
                        mediaAlt: 'Helix 404 state',
                    ),
                    $this->walletCtaSection(
                        heading: 'Pick up where you left off',
                        summary: 'Reconnect your wallet to return to your supply and borrow positions.',
                    ),
                    $this->ctaSection(
                        heading: 'That page could not be found',
                        summary: 'A 404 state keeps the protocol on-brand and routes visitors back into the market journey.',
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
            name: self::BRAND . ' Launch',
            title: 'Start earning on-chain — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Turn intent into a connected wallet',
                'A focused conversion page that takes depositors from interest to a supplied market.',
            ),
            renderData: [
                'summary' => 'Turn intent into a connected wallet and a supplied market.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        heading: 'Turn intent into a connected wallet',
                        summary: 'You have seen the markets and the audits. The next step is one signature away — connect, supply, and start earning protocol yield.',
                        mediaUrl: $media['cta'][0],
                        mediaAlt: 'Start earning on Helix',
                    ),
                    $this->walletCtaSection(
                        heading: 'Connect and supply in one flow',
                        summary: 'A conversion-focused wallet path keeps the journey to depositing into the protocol direct and immersive.',
                    ),
                    $this->proofSection(
                        heading: 'Numbers that back the decision',
                        summary: 'What depositors are earning across the protocol right now.',
                    ),
                    $this->ctaSection(
                        heading: 'Turn intent into a connected wallet',
                        summary: 'A conversion-focused CTA stack keeps the path to depositing into the protocol direct and immersive.',
                    ),
                ],
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function heroSection(string $heading, string $summary, ?string $mediaUrl = null, string $mediaAlt = ''): array
    {
        return [
            'type' => 'hero',
            'eyebrow' => 'Crypto DeFi',
            'heading' => $heading,
            'summary' => $summary,
            'actions' => [
                ['label' => 'Connect wallet', 'url' => '#wallet', 'style' => 'primary'],
                ['label' => 'View markets', 'url' => '#markets', 'style' => 'secondary'],
            ],
            'mediaUrl' => $mediaUrl,
            'mediaAlt' => $mediaAlt,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function protocolStatsSection(): array
    {
        return [
            'type' => 'protocol-stats',
            'heading' => 'The protocol in numbers',
            'summary' => 'Aggregate liquidity and activity across every Helix market, read straight from the chain.',
            'items' => [
                ['name' => '$1.84B', 'quote' => 'Total value locked across all supply markets.'],
                ['name' => '$612M', 'quote' => 'Active borrows drawn against supplied collateral.'],
                ['name' => '67%', 'quote' => 'Average protocol utilisation across live pools.'],
                ['name' => '~2.1s', 'quote' => 'Median settlement time for supply and borrow transactions.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function tokenMetricsSection(string $heading, string $summary): array
    {
        return [
            'type' => 'token-metrics',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'USDC', 'summary' => 'Supply 5.4% APY · Borrow 7.1% APY · 74% utilisation · $620M supplied.'],
                ['title' => 'ETH', 'summary' => 'Supply 2.8% APY · Borrow 4.3% APY · 58% utilisation · $480M supplied.'],
                ['title' => 'wstETH', 'summary' => 'Supply 3.1% APY · Borrow 4.9% APY · 61% utilisation · $310M supplied.'],
                ['title' => 'WBTC', 'summary' => 'Supply 1.9% APY · Borrow 3.4% APY · 49% utilisation · $430M supplied.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function featuresSection(): array
    {
        return [
            'type' => 'features',
            'heading' => 'Built for depositors who read the contract',
            'summary' => 'Everything the protocol does is on-chain, non-custodial, and verifiable.',
            'items' => [
                ['title' => 'Non-custodial by design', 'summary' => 'Your keys, your assets. Helix never takes custody — positions live in audited smart contracts you can exit any time.'],
                ['title' => 'Isolated risk markets', 'summary' => 'Each collateral type sits in its own market so a single asset shock can never cascade across the protocol.'],
                ['title' => 'Instant, fully liquid positions', 'summary' => 'Supply or withdraw in a single transaction. There is no lock-up and no withdrawal queue.'],
                ['title' => 'Transparent on-chain governance', 'summary' => 'Rate models and risk parameters change only through public, time-locked governance votes.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function howItWorksSection(): array
    {
        return [
            'type' => 'how-it-works',
            'heading' => 'How lending works on Helix',
            'summary' => 'Four steps from a connected wallet to live yield — or to borrowed liquidity.',
            'items' => [
                ['title' => '1 · Connect a wallet', 'summary' => 'Link MetaMask, Coinbase Wallet, or any WalletConnect signer. No account, no email, no KYC.'],
                ['title' => '2 · Supply an asset', 'summary' => 'Deposit USDC, ETH, or another supported asset into a market and start accruing yield immediately.'],
                ['title' => '3 · Borrow against it', 'summary' => 'Draw instant liquidity against your supplied collateral, up to that market\'s loan-to-value limit.'],
                ['title' => '4 · Manage and exit', 'summary' => 'Repay, withdraw, or rebalance any time. Every position stays fully liquid and under your control.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function auditBadgesSection(): array
    {
        return [
            'type' => 'audit-badges',
            'heading' => 'Audited contracts and transparent risk',
            'summary' => 'Every contract that holds funds has been reviewed by independent security firms, with reports published in full.',
            'items' => [
                ['title' => 'Trail of Bits', 'summary' => 'Full review of the core lending and liquidation contracts — no critical findings outstanding.'],
                ['title' => 'OpenZeppelin', 'summary' => 'Audit of the rate model and governance time-lock, with all recommendations resolved.'],
                ['title' => 'Spearbit', 'summary' => 'Competitive review of the oracle integration and price-feed safeguards.'],
                ['title' => 'Immunefi bug bounty', 'summary' => 'A live $2M bounty keeps independent researchers scrutinising the protocol around the clock.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function walletCtaSection(string $heading, string $summary): array
    {
        return [
            'type' => 'wallet-cta',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'MetaMask', 'summary' => 'Connect the most widely used browser wallet in two clicks.'],
                ['title' => 'WalletConnect', 'summary' => 'Pair any mobile wallet by scanning a single QR code.'],
                ['title' => 'Ledger', 'summary' => 'Sign every transaction on a hardware device for cold-storage security.'],
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
                ['name' => '2 yrs', 'quote' => 'Continuous, exploit-free operation since mainnet launch.'],
                ['name' => '140k+', 'quote' => 'Unique wallets that have supplied or borrowed on the protocol.'],
                ['name' => '$9.4B', 'quote' => 'Cumulative borrow volume settled on-chain to date.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function marketsListingSection(): array
    {
        return [
            'type' => 'content-listing',
            'heading' => 'Live markets',
            'summary' => 'Every supply and borrow pool currently active on Helix, newest collateral first.',
            'items' => [
                ['title' => 'USDC pool', 'summary' => 'Stablecoin supply at 5.4% APY with deep liquidity and 74% utilisation.'],
                ['title' => 'ETH pool', 'summary' => 'Supply ETH at 2.8% APY or borrow against it up to an 82% loan-to-value.'],
                ['title' => 'wstETH pool', 'summary' => 'Earn staking yield plus 3.1% supply APY on Lido wrapped staked ETH.'],
                ['title' => 'WBTC pool', 'summary' => 'Bring Bitcoin on-chain as collateral and borrow stablecoins instantly.'],
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
                ['label' => 'Connect wallet', 'url' => '#wallet', 'style' => 'primary'],
                ['label' => 'View markets', 'url' => '#markets', 'style' => 'secondary'],
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
                ['label' => 'Markets', 'url' => '#markets'],
                ['label' => 'How it works', 'url' => '#how-it-works'],
                ['label' => 'Audit', 'url' => '#audit'],
                ['label' => 'Docs', 'url' => '#docs'],
                ['label' => 'Connect wallet', 'url' => '#wallet'],
            ],
            'ctaLabel' => 'Connect wallet',
            'ctaUrl' => '#wallet',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        return [
            'brandName' => self::BRAND,
            'summary' => 'A permissionless, non-custodial lending market. Audited contracts, settled on-chain in seconds.',
            'columns' => [
                [
                    'heading' => 'Protocol',
                    'links' => [
                        ['label' => 'Markets', 'url' => '#markets'],
                        ['label' => 'How it works', 'url' => '#how-it-works'],
                        ['label' => 'Governance', 'url' => '#governance'],
                        ['label' => 'Roadmap', 'url' => '#roadmap'],
                    ],
                ],
                [
                    'heading' => 'Security',
                    'links' => [
                        ['label' => 'Audits', 'url' => '#audit'],
                        ['label' => 'Bug bounty', 'url' => '#audit'],
                        ['label' => 'Risk parameters', 'url' => '#audit'],
                    ],
                ],
                [
                    'heading' => 'Connect',
                    'links' => [
                        ['label' => 'Connect wallet', 'url' => '#wallet'],
                        ['label' => 'Docs', 'url' => '#docs'],
                        ['label' => 'Discord', 'url' => '#community'],
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
