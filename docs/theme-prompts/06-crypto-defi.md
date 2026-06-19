# Theme: CryptoDefi (`theme-crypto-defi`)

Read [`_shared-build-contract.md`](./_shared-build-contract.md) first; it owns the
engineering rules (skeleton, manifest v3, provider wiring, public-output safety,
tests). This brief only covers what makes `theme-crypto-defi` distinct.

## Build this

Build a premium Capell child theme for a DeFi lending protocol / on-chain market — a
dark, neon, high-energy landing page for an on-chain product. It leads with protocol
stats (TVL, volume, APY, users), token metrics, audit badges, a how-it-works
deposit/borrow flow, and a **static** wallet-style call-to-action. Everything is static
demo content: no live web3, no wallet connection, no trading logic. A prominent
"illustrative figures — not financial advice" disclaimer is MANDATORY. Extend `default`
(Foundation Theme) at runtime and `capell-app/foundation-theme` at the package level.

## Inspiration

Browser tabs: **Loopscale** for the on-chain lending-market framing (protocol stats grid,
deposit/borrow flow, audit proof) and **Tars** for the neon, immersive, motion-rich dark
aesthetic. Borrow the moves — a glowing stats bar, token metrics, an audit-badge wall, a
two-step how-it-works, and a wallet-styled CTA button — and the confident, on-chain tone.
Do **not** copy their copy, numbers, or any live web3 code; invent original demo content
(brand "Helix Protocol") and keep every figure static.

## Gap it fills

The set (agency, commerce, corporate, education, estate-agents, healthcare,
inertia-bookings, knowledge, liquid-glass, local-services, nonprofit, portfolio,
restaurant, saas) has nothing in the web3 / DeFi space and nothing with this neon
immersive treatment. It is the only theme that renders protocol stats, token metrics, and
audit badges — and the only one that pairs a wallet-style CTA with a mandatory financial
disclaimer, all as safe static content.

## Package identity

| Field       | Value                                                                                               |
| ----------- | --------------------------------------------------------------------------------------------------- |
| package     | `capell-app/theme-crypto-defi`                                                                      |
| slug        | `theme-crypto-defi`                                                                                 |
| namespace   | `Capell\ThemeStudio\CryptoDefi`                                                                     |
| themeKey    | `crypto-defi`                                                                                       |
| displayName | `Crypto DeFi`                                                                                       |
| tier        | premium                                                                                             |
| bestFit     | `["DeFi protocols", "On-chain lending markets", "Web3 product launches", "Token / protocol sites"]` |
| tags        | `["Crypto", "DeFi", "Web3", "Neon", "Dark"]`                                                        |

## Design direction

| Token              | Value           | Rationale                                                  |
| ------------------ | --------------- | ---------------------------------------------------------- |
| primaryColor       | `#8b5cf6`       | Neon violet — the protocol's glowing signature color.      |
| accentColor        | `#22d3ee`       | Cyan accent for active stats and links, electric on black. |
| neutralColor       | `#1e1b3a`       | Deep indigo for card surfaces and dividers.                |
| surfaceColor       | `#0a0118`       | Near-black violet-tinted canvas for an on-chain feel.      |
| foregroundColor    | `#ede9fe`       | Soft violet-white body text, high contrast on the canvas.  |
| headingFont        | `space-grotesk` | Technical, crypto-native display headings.                 |
| bodyFont           | `inter`         | Neutral, legible body for stats and copy.                  |
| spacing            | `airy`          | Lets the neon glow and stats breathe.                      |
| alignment          | `center`        | Centered hero/stats for a launch-page energy.              |
| cardStyle          | `flat`          | Borderless glowing surfaces; depth from gradients/glow.    |
| navigationStyle    | `minimal`       | Slim bar with a single wallet-style CTA.                   |
| layoutPresentation | `immersive`     | Full-bleed gradients and edge-to-edge stats.               |
| motionIntensity    | `expressive`    | Glow pulses and gradient drift for launch energy.          |
| mediaTreatment     | `duotone`       | Duotone token/diagram media in the violet/cyan palette.    |
| radius             | `lg`            | 12px+ rounded for a soft, modern web3 look.                |
| headingScale       | `dramatic`      | Oversized hero stat and protocol name.                     |
| cardDensity        | `comfortable`   | Stat and token cards need room for big figures.            |

Typography: headings in **Space Grotesk**, body in **Inter**, all figures in
`tabular-nums`. Motion: expressive but CSS-only — gradient drift, glow pulse, subtle
float; honor `prefers-reduced-motion` by disabling pulses. The wallet CTA is a styled
link/button with no web3 JS, no `window.ethereum`, no wallet SDK — purely presentational.

## Sections

`includedSections`:
`["navigation", "hero", "protocol-stats", "token-metrics", "features", "how-it-works", "audit-badges", "wallet-cta", "proof", "content-listing", "cta", "footer"]`

NEW sections (render-data shapes):

- **protocol-stats** — `heading`, `summary`, `stats[]` each `{ label, value }` (TVL, volume, APY, users).
- **token-metrics** — `heading`, `summary`, `token` `{ symbol, price, marketCap, supply }`.
- **audit-badges** — `heading`, `summary`, `audits[]` each `{ auditor, reportUrl }` (plain public links).
- **wallet-cta** — `heading`, `summary`, `actionLabel`, `actionUrl`, `note` (static; no real web3 — the action is a normal link).
- **how-it-works** — `heading`, `summary`, `steps[]` each `{ name, detail }` (deposit / borrow flow).

`content-listing` uses the `gallery` variant for the docs/changelog feed.

## Beta data (demo profile)

Seeded via `ThemeDemoPageInstaller::profile()`. All copy original; all figures static.

**Brand:** Helix Protocol — "Lend and borrow at the speed of Solana."

**Hero:** heading "Put your assets to work, on-chain." Summary: "Helix is a
permissionless lending market where deposits earn yield and borrowers tap instant
liquidity — settled in seconds, secured by audited contracts."

**Protocol stats (protocol-stats):** heading "Live protocol stats." (static snapshot)

- label "Total value locked" — value "$284M".
- label "30-day volume" — value "$1.9B".
- label "Supply APY (up to)" — value "7.4%".
- label "Active wallets" — value "62,400".

**Token metrics (token-metrics):** heading "HLX token."

- token: symbol "HLX" — price "$3.18" — marketCap "$412M" — supply "129.6M circulating".

**Feature cards (features):** 5 cards `{ title, summary, type }`:

- "Instant settlement" / "Deposits and loans settle in seconds on Solana." / capability.
- "Isolated risk pools" / "Each market is ring-fenced so one asset can't drain another." / safety.
- "Transparent rates" / "Supply and borrow APYs update on-chain, visible to everyone." / capability.
- "Liquidation protection" / "Health-factor alerts and partial liquidations reduce loss." / safety.
- "Open and permissionless" / "No accounts, no gatekeepers — connect and participate." / access.

**How it works (how-it-works):** heading "Two ways to use Helix."

- name "Deposit & earn" — detail "Supply an asset to a market and start earning supply APY immediately."
- name "Borrow against collateral" — detail "Lock collateral, borrow up to your limit, and repay anytime."
- name "Manage your health factor" — detail "Track your collateral ratio and top up to stay safe."

**Audit badges (audit-badges):** heading "Audited and public."

- auditor "OtterSec" — reportUrl "/audits/ottersec-helix.pdf".
- auditor "Trail of Bits" — reportUrl "/audits/trail-of-bits-helix.pdf".
- auditor "Sec3" — reportUrl "/audits/sec3-helix.pdf".

**Wallet CTA (wallet-cta):** heading "Ready to put assets to work?" summary "Open the
app to view live markets." actionLabel "Launch app" actionUrl "/app" note "Connecting a
wallet happens in the app, not on this page." (No web3 code anywhere.)

**Proof metrics (proof):** each `{ metric, name, quote }`:

- "$284M" / "Total value locked" / "Helix is where I park idle stablecoins for real yield." — anon, @degenmaxi.
- "7.4%" / "Top supply APY" / "Transparent on-chain rates beat any CeFi promise." — anon, @yieldhunter.
- "2 audits" / "Independent security reviews" / "Two reputable audits is why I deposited size." — anon, @onchainsam.

**Spotlight / pathways:** spotlight "How Helix isolates market risk" with pathways
"Read the docs", "Review the audits", "Launch the app".

**Directory samples (content-listing, 3+):**

- "Helix lending markets, explained" — type "Docs" — "Supply, borrow, and liquidation mechanics."
- "Understanding your health factor" — type "Guide" — "How to avoid liquidation."
- "Changelog: v2 isolated pools" — type "Changelog" — "Per-market risk isolation shipped."

**Detail/article sample:** "How on-chain lending rates are set" — a 4-paragraph original
article on utilization curves, supply/borrow APY, and why rates move.

**Disclaimer block (MANDATORY vertical block):** a prominent, clearly labeled block
rendered as static copy near the stats and again in the footer:
"All figures on this page are illustrative demo values and do not represent live protocol
data. Nothing here is financial, investment, or legal advice. DeFi carries significant
risk, including total loss of funds. Do your own research and consult a qualified advisor
before participating." The build must include NO live wallet, web3, RPC, or trading code —
the wallet CTA is a plain link only.

## Build steps

Follow the shared contract §2–§9 and these real files:

1. **capell.json** (manifest v3, §3): `kind: theme`, `themeKey: crypto-defi`, `extends: capell-app/foundation-theme`, `surfaces: ["frontend"]`, requires `core` + `frontend`, runtime provider `Capell\ThemeStudio\CryptoDefi\CryptoDefiThemeServiceProvider`, demo command `capell:theme-crypto-defi-demo` with `demoParams: ["url","languages","sites"]`, capabilities `["theme-crypto-defi","theme-crypto-defi-frontend"]`, `security.publicOutput` all-on, `riskTier: low`, render budget 20ms, `cacheTags: ["theme-crypto-defi"]`, health check `theme-crypto-defi.package-health`, marketplace block, categories `["frontend","themes"]`.
2. **CryptoDefiThemeServiceProvider** (§4): empty `register()`; `boot(ThemeRegistry)` registers the demo command, gates on `isPackageInstalled`, loads translations + views (`capell-theme-crypto-defi`), registers CSS via `VendorAssetData::tailwindImport` and Blade sources via `tailwindSource`, registers the page adapter, then `$registry->register(...)`.
3. **definition()** returns `ThemeDefinitionData` with the presets table, `includedSections`, package, preview image, tags, bestFit, assets, `runtime: FrontendRuntime::Blade`, `extends: 'default'`.
4. **ViewSectionRenderer per customised section**: `navigation`, `hero`, `protocol-stats`, `token-metrics`, `features`, `how-it-works`, `audit-badges`, `wallet-cta`, `proof`, `content-listing`, `cta`, `footer`, each `failLoudly: true`. The wallet-cta view emits a plain link only.
5. **CryptoDefiThemePageAdapter** maps demo render data into typed section Data, including the 5 NEW shapes plus the disclaimer block.
6. **InstallCryptoDefiThemeDemoAction** implements `InstallsThemeDemo` → `ThemeDemoPageInstaller::run($data, 'crypto-defi', 'CryptoDefi')`; **DemoCommand** exposes `capell:theme-crypto-defi-demo`; add the **profile()** entry with all copy above, including the disclaimer.
7. **page.blade.php** (§7): skip link, `data-capell-theme`, brand tokens inline, `{!! $content !!}`, `@frontendAsset('css/theme-crypto-defi.css')`. Keep `livewire/page/page.blade.php` thin. No web3 scripts.
8. **resources/css/theme-crypto-defi.css** — near-black violet canvas, neon violet/cyan glow, gradient drift, duotone media filters, `tabular-nums`; reduce/disable pulses under `prefers-reduced-motion`. No package names or markers.
9. **resources/lang/en/generic.php** — all strings via `__('capell-theme-crypto-defi::...')`, including the disclaimer text.
10. **Theme CryptoDefi health check** for `theme-crypto-defi.package-health`.
11. **Autoload** in BOTH `composer.json` and `composer.local.json` (`Capell\ThemeStudio\CryptoDefi\` → `packages/theme-crypto-defi/src`, `...\Tests\` → tests), then `COMPOSER=composer.local.json composer dump-autoload --no-scripts`.

## Acceptance criteria

Standard set (§10): `ManifestRequirementsTest`, `CryptoDefiThemeDefinitionTest`,
`CryptoDefiThemePageAdapterTest`, `PublicOutputSafetyTest`, `DemoCommandTest`,
`ThemeCryptoDefiHealthCheckTest`. Theme-specific assertions:

- `protocol-stats` renders TVL/volume/APY/users; `token-metrics` renders the HLX token
  `symbol`, `price`, `marketCap`, `supply`.
- The rendered public HTML contains the mandatory disclaimer (assert a distinctive phrase
  such as "illustrative demo values" and "not financial, investment, or legal advice").
- **No web3 surface**: rendered HTML, CSS, and JS contain no `window.ethereum`,
  `walletconnect`, `web3`, `ethers`, or live RPC/wallet code; the `wallet-cta` action is a
  plain link (`actionUrl`), with no `wire:`, `signed`, `data-field`, or `model_id`.
- Demo install seeds all 7 surfaces with Helix Protocol copy (assert brand name, the
  "$284M" TVL figure, and the disclaimer appear).

## Verification

```bash
vendor/bin/pest packages/theme-crypto-defi/tests --configuration=phpunit.xml
COMPOSER=composer.local.json composer preflight
```
