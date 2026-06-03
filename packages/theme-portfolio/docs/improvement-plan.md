# Theme Portfolio — Improvement & Growth Plan

> Package: capell-app/theme-portfolio · Kind: theme · Tier: premium · Product group: Capell Themes · Bundle: themes · Status: Draft

## 1. Snapshot

Theme key `portfolio` is registered in `src/PortfolioThemeServiceProvider.php` via `ThemeRegistry::register()` with a single `BladeThemeRenderer` (layout `capell-theme-portfolio::page`) and one preset (`portfolio`, warm-clay `#7c2d12` / rose `#f43f5e`). The definition declares 16 `includedSections`; 15 ship as real `resources/views/sections/*.blade.php` templates (navigation is delegated to foundation and its local template is a near-empty stub) — namely hero, features, proof, content-listing, work-grid, case-studies, case-study-detail, process, services, testimonials, speaking-media-kit, availability, newsletter, cta, footer. The demo command `capell:theme-portfolio-demo` (`src/Console/Commands/DemoCommand.php`) delegates to `InstallPortfolioThemeDemoAction`, which calls foundation's shared `ThemeDemoPageInstaller` (seeds ≥7 route-backed pages with Unsplash media); demo "layouts" are driven by foundation, not declared per-layout in this package. The theme overrides every standard section with portfolio-specific Blade and inherits foundation behaviour (definition `extends: 'default'`). Current marketplace summary verbatim: **"Premium studio and case-study theme screenshots from route-backed demo layouts."** Screenshots: `capell.json` declares 6 (all 6 committed: 3 JPG + 3 SVG under `docs/assets/marketplace/`); `docs/screenshots.json` declares 9 deployment captures targeting `docs/screenshots/` — **0 committed** (that directory does not exist).

## 2. Improvements (existing functionality)

1. **Make testimonials data-driven** — the entire section is three hardcoded English quotes ("Great clarity…", "Client Snapshot", etc.) and never reads `$section->items`; a buyer who connects real testimonials sees nothing change — `resources/views/sections/testimonials.blade.php` — M
2. **Make speaking/media-kit data-driven** — identical problem: "Deck design / Media kit PDF / Interview prep" plus all body copy are hardcoded and `$section->items` is ignored, so the section is a fixed brochure — `resources/views/sections/speaking-media-kit.blade.php` — M
3. **Translate hardcoded copy in services** — "What we build", the modular-services paragraph, and fallback labels `SERVICE` / `DISCOVERY` / `DESIGN` / `LAUNCH` plus the three fallback cards are literal English bypassing the `capell-theme-portfolio::generic` namespace used everywhere else — `resources/views/sections/services.blade.php` — S
4. **Translate newsletter chrome** — "Subscribe", `placeholder="you@company.com"`, and `aria-label="Newsletter signup"` are hardcoded; `email_label` is referenced with `?? 'Email address'` but no `email_label` key exists in `generic.php` so the fallback always fires — `resources/views/sections/newsletter.blade.php` + `resources/lang/en/generic.php` — S
5. **Externalise hero/work-grid vanity stats** — `+42%`, `120+`, `6h` (hero) and `30+ Projects` / `12+ Industries` / `97% Retention` (work-grid) are baked into markup; every demo and every buyer who forgets to override ships identical fake metrics — `resources/views/sections/hero.blade.php`, `resources/views/sections/work-grid.blade.php` — M
6. **Unify the empty-state strategy** — three incompatible behaviours coexist: case-studies fabricates rich fake records, availability/process render dashed "…ready" boxes, work-grid hardcodes 3 cards, testimonials/services/speaking always show static content. Pick one (prefer the dashed "add content" placeholder) so an un-populated site looks intentional — `resources/views/sections/{case-studies,work-grid,services,testimonials,speaking-media-kit}.blade.php` — M
7. **Add a dark preset/variant** — CSS has no `dark` styling and the preset ships no dark values; siblings position dark as a selling point. A premium portfolio theme needs a dark mode for the work-led `#070b1a` aesthetic it already leans on — `resources/css/theme-portfolio.css`, `src/PortfolioThemeServiceProvider.php` (`presets`) — L
8. **Guard motion for reduced-motion users** — carousels (`data-carousel` work-grid/case-studies/testimonials) and `content-listing` hover transforms (`hover:-translate-y-1`, `group-hover:scale-[1.025]`) have no `motion-reduce:` variants or `@media (prefers-reduced-motion)` block, despite the preset declaring `motionIntensity: subtle` — `resources/css/theme-portfolio.css` + section blades — S
9. **Promote raw hex to design tokens** — templates hardcode `#070b1a`, `#1f3173`, `#fb923c`, `#9a3412`, `#f8fafc` directly via Tailwind arbitrary values instead of the `--portfolio-*` / `--theme-*` custom properties defined in CSS, so theme-editor colour changes only partially propagate — `resources/css/theme-portfolio.css` + all section blades — M
10. **Flesh out or drop the navigation section template** — `sections/navigation.blade.php` renders only an optional `<h2>` heading; it adds nothing over foundation's nav and risks emitting an empty `<section>` — confirm foundation supplies the real nav and either enrich (logo/links) or remove from `includedSections` — `resources/views/sections/navigation.blade.php` — S

## 3. Missing Features (gaps)

Manifest `capabilities[]` = `["theme-portfolio", "theme-portfolio-frontend"]` (frontend rendering only — no editor/authoring capability declared).

Table-stakes for a portfolio vertical, mapped to current state:

- **Work/project showcase grid** — present (work-grid, content-listing) but work-grid renders only title/type/summary text cards with placeholder bars, no real project imagery binding beyond the optional hero image. Gap: a genuinely media-led grid.
- **Case-study detail** — present (case-study-detail + case-studies) — strongest area; keep as the differentiator.
- **About / bio** — **missing.** No about, founder, or studio-story section. Core to a personal/creator portfolio.
- **Skills / capabilities matrix** — partially covered by services/process, but no dedicated skills or tools/stack section.
- **Gallery / lightbox** — **missing.** No image gallery or lightbox; foundation's `content-listing` has a `gallery` variant this theme does not surface. Image-heavy buyers expect this.
- **Contact / enquiry** — **weak.** Availability + footer reference contact, but there is no real contact form; newsletter is the only form and it is inert (`action="#"`).
- **Resume / CV** — **missing.** No timeline, experience, or downloadable-CV section — a common portfolio ask.
- **Client logos / logo wall** — **missing** as a distinct section (proof handles metric quotes, not a logo strip).
- **Functional newsletter capture** — declared via the `newsletter` package integration but the form posts nowhere; the package-aware flag only swaps a caption string.

Differentiator vs table-stakes: case-study depth (outcome ledger, scope/role/timeline) is the genuine differentiator and should be doubled down on. About/bio, gallery/lightbox, and resume/CV are table-stakes a creator buyer will expect and are currently absent.

**Sibling overlap (theme-agency):** README ("Product Direction") and `docs/overview.md` both explicitly flag the agency overlap and say to merge if it persists. Today both themes share the same standard sections (hero/features/proof/cta/footer/content-listing) and a services concept. Portfolio must own the **personal/creator** lane: first-person bio, single-operator availability, media kit, audience/newsletter growth — versus agency's team/campaign/services positioning. Without a bio/about + creator-voice copy, the two are hard to tell apart.

## 4. Issues / Risks

- **Doc/manifest product-group drift** — `capell.json` declares `product.group: "Capell Themes"`, `tier: "premium"`; `docs/overview.md` states "Product group: **Capell Foundation** · Commercial proposal: **paid first-party theme**". Pick one source of truth — `capell.json` vs `docs/overview.md` (lines ~3–4) — and align the README too.
- **`extends` has two meanings, easy to confuse** — runtime definition uses `extends: 'default'` (asserted by `tests/Unit/PortfolioThemeDefinitionTest.php`), while `capell.json.extends = "capell-app/foundation-theme"` (asserted by `tests/Unit/ManifestRequirementsTest.php`). Both pass, but the divergence is undocumented and a future edit will "fix" one to match the other and break a test — `src/PortfolioThemeServiceProvider.php:64`, `capell.json`.
- **Stub health check declared `critical`** — `src/Health/ThemePortfolioHealthCheck.php` implements only `compatibleCapellApiVersion()`; it performs no real probe, yet `capell.json.healthChecks[].severity = "critical"`. Diagnostics will report green regardless of whether views/preset/assets resolve. Either add real checks (views exist, preset present, vendor asset registered) or lower the declared severity — `src/Health/ThemePortfolioHealthCheck.php`.
- **Stub management contribution** — `src/Manifest/ThemeManagementPageContribution.php` is a bare contract shell (`compatibleCapellApiVersion()` only); confirm `ThemeExtensionPage` needs nothing more, or it is dead surface area — `src/Manifest/ThemeManagementPageContribution.php`.
- **Inert newsletter form** — `action="#"` submits to the current URL; a visitor entering an email gets a no-op page reload and no capture. Risk: looks broken on a "premium" theme — `resources/views/sections/newsletter.blade.php:17`.
- **Public Blade DB-query safety: good** — `page.blade.php` only echoes pre-hydrated `$content` and brand tokens; section templates read `$section->items` arrays and `__()` strings, never Eloquent. `tests/Unit/PublicOutputSafetyTest.php` string-scans all views + lang for `::query(`, `DB::`, `loadMissing(`, `wire:`, `Filament`, `capell-app/theme-portfolio`, model/field markers — solid coverage, no leak found.
- **WCAG gaps** — placeholder visual bars use `aria-hidden="true"` (good), and a skip link + `:focus-visible` outline exist (good). But: empty `alt=""` on the hero image when an author _does_ supply `mediaUrl` (`hero.blade.php:93` uses `$imageAlt` which can be blank); `content-listing` work images hardcode `alt=""` (`:40`) even for real media; carousel buttons are visually `hidden` by default with no keyboard/visible affordance described. Decorative-vs-informative alt handling needs review — `resources/views/sections/hero.blade.php`, `resources/views/sections/content-listing.blade.php`.
- **LCP / image risk (image-heavy theme)** — no `loading`/`fetchpriority`/`width`/`height`/`srcset` on any `<img>` (hero, content-listing); demo seeds remote `images.unsplash.com` URLs. On a portfolio (image-led by definition) this risks poor LCP and layout shift. Add intrinsic dimensions and lazy/eager hints — `resources/views/sections/{hero,content-listing}.blade.php`.
- **Cache safety** — `capell.json.performance.cacheSafety.cacheable = false`, `variesBy: ["site","locale"]`, `sensitiveOutput: false`. Consistent with locale-dependent `__()` output and no per-user data. The inline `style="…tokens…"` in `page.blade.php` is brand-derived (site-scoped), matching `variesBy`. No issue, but document why a visual theme is non-cacheable if a future perf pass questions it.
- **Performance budget** — manifest sets `frontendRenderBudgetMs: 20`, `adminQueryBudget: 0`. No render benchmark test exists to enforce the 20ms budget; the many nested grids/carousels per section make this worth a guardrail test — `capell.json`, `tests/`.
- **Test gaps** — no render test for footer, navigation, services, testimonials, speaking-media-kit, or newsletter (only hero, features, proof, content-listing, cta, case-studies, case-study-detail, process, availability, work-grid are exercised). The hardcoded-content sections (testimonials/speaking) are exactly the ones with no test, so their data-binding regressions would be invisible — `tests/Unit/`.

## 5. Marketplace & Selling

**Current `summary` critique:** "Premium studio and case-study theme screenshots from route-backed demo layouts." This describes the _screenshot process_, not the product — "screenshots from route-backed demo layouts" is internal tooling language that means nothing to a buyer. It buries the value (work/case-study storytelling) behind build-pipeline jargon.

**Current composer `description` critique:** "Creator and consultant portfolio theme for work, case studies, services, media kits, and newsletters." — accurate and clear; far better than the marketplace summary. It reads as a feature list rather than a benefit, but it is honest and on-vertical. Reuse its substance for the summary.

**Improved 1-sentence summary:**

> A premium portfolio theme for creators and consultants — turn selected work into outcome-driven case studies, sell your services and media kit, and grow your audience from one polished site.

**Improved 3–4 sentence description:**

> Theme Portfolio gives independent creators, consultants, and studios a credibility-first website built around proof, not just pretty pictures. Lead with a work-led hero, walk visitors through outcome-rich case studies (scope, role, timeline, measurable results), and present your services, process, and media kit as things people can actually buy. Connect Media Library for real project imagery, Content Sections for deep case studies, and Newsletter for audience capture — every section degrades gracefully when an integration is not installed. Where an agency theme sells a team and campaigns, Theme Portfolio sells _you_: your work, your authority, and your availability.

**Screenshot / media gaps:** the live capture set is the biggest gap — `docs/screenshots.json` declares 9 required screenshots (admin theme list, frontend render, homepage, work-grid, case-study, services, media-kit, newsletter, signed preview) and **none are committed** to `docs/screenshots/`. The 6 marketplace images that exist are 3 hand-made SVG mockups + 3 JPGs (extension-card, hero-desktop, hero-mobile); there is no real rendered screenshot of the actual theme. Premium themes sell on visuals — running the deployment screenshot runner to produce the 9 declared PNGs (and a dark-mode pair, see §2.7) is the single highest-leverage marketing fix. Also: `previewImage: '/vendor/capell/themes/portfolio.jpg'` points at an app-published asset not shipped in this package — verify it exists in the install pipeline.

**Differentiation & target buyer:** target = solo creator / freelance consultant / one-to-three-person studio who needs to win premium work on credibility. Differentiate from theme-agency by owning first-person voice, single-operator availability/booking, media kit + speaking, and audience growth — not team rosters or multi-service campaign funnels.

**8–12 keywords/tags:** `portfolio`, `case studies`, `personal brand`, `creator`, `consultant`, `freelancer`, `work showcase`, `media kit`, `studio`, `outcomes`, `newsletter`, `premium theme`.

## 6. Prioritized Roadmap

| Item                                                                                 | Bucket | Effort | Impact | Section ref      |
| ------------------------------------------------------------------------------------ | ------ | ------ | ------ | ---------------- |
| Generate the 9 declared `screenshots.json` captures (+ dark pair)                    | Now    | M      | High   | §5               |
| Rewrite marketplace `summary` + description for the creator vertical                 | Now    | S      | High   | §5               |
| Fix inert newsletter form (`action="#"`) → real capture when Newsletter installed    | Now    | M      | High   | §2.4, §4         |
| Reconcile product-group drift (overview.md vs capell.json) + document dual `extends` | Now    | S      | Med    | §4               |
| Make testimonials + speaking/media-kit data-driven                                   | Now    | M      | High   | §2.1, §2.2       |
| Translate hardcoded copy (services, newsletter, stats) + add missing lang keys       | Now    | M      | Med    | §2.3, §2.4, §2.5 |
| Add About/Bio section (creator lane differentiator vs agency)                        | Next   | M      | High   | §3               |
| Implement real health-check probes or lower declared `critical` severity             | Next   | S      | Med    | §4               |
| Add image LCP hints (dimensions, lazy/eager, srcset) + fix alt handling              | Next   | M      | Med    | §4               |
| Unify empty-state strategy across sections                                           | Next   | M      | Med    | §2.6             |
| Add render tests for footer/nav/services/testimonials/speaking/newsletter            | Next   | M      | Med    | §4               |
| Ship a dark preset + dark CSS                                                        | Next   | L      | High   | §2.7, §5         |
| Add reduced-motion guards for carousels + hover transforms                           | Next   | S      | Med    | §2.8             |
| Add gallery/lightbox + resume/CV + client-logo sections                              | Later  | L      | Med    | §3               |
| Promote raw hex to design tokens for full theme-editor propagation                   | Later  | M      | Med    | §2.9             |
| Add a frontend render-budget guardrail test (20ms)                                   | Later  | M      | Low    | §4               |
