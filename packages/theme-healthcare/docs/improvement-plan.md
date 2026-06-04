# Theme Healthcare — Improvement & Growth Plan

> Package: capell-app/theme-healthcare · Kind: theme · Tier: premium · Product group: Capell Themes · Bundle: themes · Status: Draft

## 1. Snapshot

Theme Healthcare is a renderer-only Capell theme that registers the `healthcare` theme key (`HealthcareThemeServiceProvider::THEME_KEY`) and an appointment-led clinical direction for clinics. It ships a `BladeThemeRenderer` with the page wrapper `capell-theme-healthcare::page` and 19 declared `includedSections`. Of those, 16 are bespoke Blade views under `resources/views/sections/` (utility-bar, navigation, hero, service-finder, services, care-pathway, clinicians, booking, locations, insurance-trust, events, comparison, proof, blog-teaser, contact, cta, footer); `features` reuses the local `services` view and `content-listing` reuses the `blog-teaser` renderer. Three sections (`content-listing`/`blog-teaser`, `booking`, `events`) use bespoke `SectionRenderer` classes that inject optional `blogAvailable`/`formBuilderAvailable`/`eventsAvailable` flags before Blade renders. It inherits all runtime data contracts, `ThemeSection` data, and the foundation `content-listing` partial (`blog-teaser.blade.php` `@include`s `capell-foundation-theme::theme.sections.content-listing` for gallery/pathways/spotlight variants) from Foundation Theme; it overrides every section's visual treatment plus a 200-line scoped CSS file (`resources/css/theme-healthcare.css`). The demo command `capell:theme-healthcare-demo` exists (`src/Console/Commands/DemoCommand.php`) and delegates to `InstallHealthcareThemeDemoAction` → `ThemeDemoPageInstaller::run(..., 'healthcare', 'Healthcare')`.

Marketplace and Composer copy are buyer-facing, and `capell.json` marketplace media is limited to 3 committed JPG preview assets under `docs/assets/marketplace/`. Separately, `docs/screenshots.json` declares **8** required route-backed PNG capture targets, but **0** PNGs are committed (`docs/screenshots/` does not exist).

## Completed Improvement Slices

- **2026-06-03:** Added buyer-facing marketplace and Composer copy, replaced the stub Diagnostics health check with real theme registration/view/vendor-asset probes, and limited marketplace screenshots to committed preview assets.
- **2026-06-04:** Added a real `main-content` skip-link target, added image dimensions/loading/decoding/fetch-priority attributes, translated visible events carousel button text, and added tests for those public rendering contracts.

## 2. Improvements (existing functionality)

Prioritized. Effort: S = <0.5d, M = ~1–2d, L = >2d.

1. **Image performance attributes added.** — Hero images now include explicit dimensions, async decoding, and `fetchpriority="high"`; clinician, service, and resource-card images include explicit dimensions, lazy loading, and async decoding. — `resources/views/sections/hero.blade.php`, `resources/views/sections/clinicians.blade.php`, `resources/views/sections/services.blade.php`, `resources/views/sections/partials/resource-card-media.blade.php`, `tests/Unit/HealthcareThemeDefinitionTest.php` — **S**

2. **Make the booking panel a real conversion surface, not decorative chrome** — `sections/booking.blade.php` renders fake static chips ("Patient name", "Morning", "Afternoon" from `generic.php`) inside a dark card. Even when `formBuilderAvailable` is `true` the panel only swaps headline copy (`booking_panel_live` vs `booking_panel_static`); it never renders an actual enquiry form or a real booking CTA link. For a HEALTHCARE theme this is the single most valuable element and it currently converts nothing. Wire the live branch to render the hydrated Form Builder form (passed in as render data, not queried) and the static branch to a prominent phone/contact CTA. Files: `resources/views/sections/booking.blade.php`, `src/Rendering/BookingSectionRenderer.php`. — effort M.

3. **Event carousel button text translated.** — `sections/events.blade.php` now uses the existing `capell-theme-healthcare::generic.carousel_previous|next` keys for visible button text and aria labels. — `resources/views/sections/events.blade.php`, `tests/Unit/HealthcareThemeDefinitionTest.php` — **S**

4. **Extract the inline `<script>` out of `events.blade.php`** — The events section ships a ~25-line inline IIFE on public output for carousel scrolling. Other carousels (`clinicians`, `contact`, `blog-teaser`) use `data-carousel`/`data-carousel-track` markers with no shipped JS, implying a foundation/theme carousel script handles them — so events is inconsistent and double-implements scrolling. Inline scripts also complicate CSP. Migrate events to the same `data-carousel` convention the other sections use, or move the JS into a bundled asset. File: `resources/views/sections/events.blade.php`. — effort M.

5. **Add a no-items empty state to item-driven sections** — `services`, `clinicians`, `contact`, `events`, `service-finder` iterate `$section->items`/`features` with no `@forelse`/`@empty` fallback, so an author who leaves a section empty renders a bare heading with whitespace. `care-pathway` already models the right pattern (`@empty` → dashed placeholder card). Bring the other sections in line. Files: `resources/views/sections/services.blade.php`, `clinicians.blade.php`, `contact.blade.php`, `service-finder.blade.php`. — effort M.

6. **Consolidate hard-coded hex colours onto the token layer** — Templates are saturated with literal hex (`text-[#14323a]`, `text-[#0f766e]`, `bg-[#f6fbfd]`, `text-[#2563eb]`, `border-[#d9e8ee]`) repeated across all 16 section views, while `theme-healthcare.css` already defines `--healthcare-ink/-primary/-link/-line/-surface/-accent` mapped to the brand tokens (`--theme-primary` etc.). The hardcoded values mean a brand/preset change in Theme Studio will NOT recolour the body text, links, or borders — only the gradient shell and focus ring react. Replace literals with the CSS custom properties (or Tailwind arbitrary values referencing them, e.g. `text-[var(--healthcare-ink)]`) so presets actually drive the palette. Files: all `resources/views/sections/*.blade.php`, `resources/css/theme-healthcare.css`. — effort L.

7. **Add dark-mode support** — There is zero dark-mode handling: no `dark:` variants in any view, no `prefers-color-scheme`/`@media` block in the CSS, and the shell hard-codes a light gradient (`#f6fbfd → #fff`). Several sibling themes ship dark variants (e.g. agency commits `*-theme-dark.png` screenshots). A premium healthcare theme should at minimum respect `prefers-color-scheme`. — effort L.

8. **Strengthen heading responsive scaling** — `h1` is `clamp(3.25rem, 7vw, 6.75rem)` at `font-weight:850` with `max-width:12ch` in CSS, while the hero Blade caps it at `text-4xl … lg:text-5xl`. The two scaling systems fight each other (utility classes vs the `:where()` clamp), and 6.75rem ultra-bold headings are aggressive for a calm clinical tone. Reconcile the CSS clamp and the Tailwind sizes into one source of truth and soften weight. Files: `resources/css/theme-healthcare.css`, `resources/views/sections/hero.blade.php`. — effort S.

## 3. Missing Features (gaps)

`capabilities[]` currently declares only `theme-healthcare` and `theme-healthcare-frontend` — generic placeholders that say nothing about healthcare capability. Against what a real clinic/medical site needs:

- **Real appointment-booking integration (table-stakes, differentiator if done well)** — The theme's whole pitch is "appointment-led" yet booking is decorative (see §2.2). The strongest cross-sell is a dedicated **bookings/scheduling** package: a sibling `AppointmentRequest` Filament resource + booking services/locations/staff models already appear elsewhere in this monorepo. A `booking` section that renders a live availability/enquiry widget (hydrated, no DB in Blade) when that package is installed would be the marquee feature and the clearest reason to pick this theme over a generic one. — differentiator.
- **Practitioner/clinician profile detail rendering** — `clinicians` only renders a card carousel (image, type, title, summary, link). There is no clinician _detail_ surface despite `screenshots.json` declaring a `/theme-healthcare-detail` "clinician detail" capture. A dedicated clinician/profile section or page renderer (credentials, specialties, languages, accepting-new-patients badge) is table-stakes for healthcare. — table-stakes.
- **Locations with hours, address, phone, and map** — Both `locations` and `contact` sections accept items but the templates only render `type`/`title`/`summary` (locations) and the contact card truncates before showing structured fields. The test seeds `address` and `phone` on contact items but the visible markup discards them. Clinics need opening hours, full address, click-to-call, and ideally an embedded map. — table-stakes.
- **Conditions / treatments / specialties taxonomy** — `service-finder` renders flat filter chips with no linking; `services` renders generic cards. Healthcare buyers expect a conditions A–Z or treatments directory with cross-links to services and clinicians. — differentiator.
- **Insurance / patient information surface** — `insurance-trust` exists but renders the same generic `type/title/summary` card grid. A real "recognised insurers / self-pay / what to expect / patient rights" block (logos, fee transparency, GDPR/medical-records note) is a healthcare-specific expectation. — differentiator.
- **Emergency / urgent-care escalation banner** — `generic.php` defines `escalation_signal`, `safety_review_signal`, and `utility_summary` ("Same-week appointments available"), but there is no dedicated emergency/urgent-care notice component (e.g. "In an emergency call 999"). This is both a UX and a duty-of-care expectation for medical sites. — differentiator vs siblings.
- **Trust/accreditation signals as first-class** — Proof is generic metric cards. Healthcare-specific trust (CQC rating, GMC-registered, accreditations) would justify the premium tier.

Versus siblings: most of the 9 other themes share the same generic section skeleton; healthcare currently differentiates only by colour and copy, not by clinical capability. The booking cross-sell is the single biggest opportunity to make it genuinely vertical.

## 4. Issues / Risks

1. **Dual `extends` meaning documented.** — `ThemeDefinitionData(extends: 'default')` records runtime theme-key inheritance while manifest `"extends": "capell-app/foundation-theme"` records package inheritance; the service provider comment documents this distinction. Add an explicit test if this keeps regressing in future theme work. — `src/HealthcareThemeServiceProvider.php`, `capell.json`.

2. **Health check shipped.** — `ThemeHealthcareHealthCheck` now probes Theme Studio registration, required views, and vendor asset registration; tests cover passing diagnostics and an unregistered theme failure. — `src/Health/ThemeHealthcareHealthCheck.php`, `tests/Unit/ThemeHealthcareHealthCheckTest.php`.

3. **Route-backed screenshot capture gap remains.** — `capell.json marketplace.screenshots` now references 3 committed JPG preview assets, but `docs/screenshots.json` declares 8 required PNG captures under `docs/screenshots/*.png`, and the directory does not exist. Several capture targets (`/theme-healthcare-directory`, `/theme-healthcare-detail`, `/theme-healthcare-contact`) still need route-backed demo coverage or repointing. — `docs/screenshots.json`, `docs/assets/marketplace/`.

4. **Inline `<script>` on public output** — `sections/events.blade.php` emits raw JavaScript to anonymous visitors. It is leak-safe (no IDs/markers), but it complicates Content-Security-Policy, is unminified, and re-implements scrolling the other carousels get from markers. — `resources/views/sections/events.blade.php`.

5. **WCAG / accessibility gaps (compliance-critical for healthcare)** — Positives: a skip-link with a real `#main-content` target, `:focus-visible` outlines, translated carousel button labels, `aria-label`s on carousel buttons, and `aria-hidden` on decorative placeholders are present. Remaining gaps: heavy reliance on `font-weight:800/850` plus muted greys (`--healthcare-muted: #5c7280` on near-white) needs a contrast audit against WCAG AA (4.5:1), and decorative hero/card placeholder blocks built from coloured `<span>`s need a consistency review. Healthcare sites frequently fall under accessibility-regulation scrutiny (e.g. public-sector / WCAG 2.2 AA), so this is a sales blocker, not a nicety. — `resources/views/page.blade.php`, `resources/css/theme-healthcare.css`, `resources/views/sections/events.blade.php`.

6. **Tokens not actually wired (cache/preset risk)** — Because section templates hardcode hex (see §2.6), the Theme Studio preset values in `definition()` (`primaryColor #0f766e`, `accentColor #f59e0b`, etc.) only flow into the gradient/focus ring via CSS vars; switching presets won't recolour most of the page. This undercuts the "premium, brandable" positioning. — all `resources/views/sections/*.blade.php`.

7. **Performance budget plausibility** — `capell.json` `performance.frontendRenderBudgetMs: 20`, `adminQueryBudget: 0`, `cacheSafety.cacheable: false`, `variesBy: ["site","locale"]`. `cacheable:false` for a fully static renderer-only theme is conservative and likely leaves rendering uncached on every request; confirm this is intended vs. a copy-paste from a dynamic package. The 20ms render budget is untested — no Pest assertion guards it. — `capell.json`.

8. **No public DB-query risk found (good)** — `PublicOutputSafetyTest` asserts Blade contains no `DB::`, `::query(`, `loadMissing(`, `Frontend::`, `find(`, etc., and the renderers pass hydrated `toViewData()` only. Section renderers correctly take availability booleans rather than querying. This invariant holds; keep the guard test when adding the booking form (§2.2) so a live form doesn't introduce a query in Blade.

9. **Test gaps** — Covered: theme definition shape, section-renderer key list, install-gating, vendor asset registration, leak-token safety on full-page + individual sections, CSS specificity, skip-link target, image loading attributes, translated event controls, optional Form Builder / Events / Blog availability datasets, no-DB-query assertion, health diagnostics, demo command delegation + idempotency. **Not covered:** contrast/visual accessibility, the `extends` value as a dedicated assertion, the inline events `<script>`, empty-section rendering, and the render-time performance budget. — `tests/Unit/`, `tests/Feature/`.

10. **`composer.json` PHP floor vs platform** — `composer.json` requires `php: ^8.3` while the monorepo/Boost context targets PHP 8.4 and the source uses typed class constants (`public const string THEME_KEY`). Confirm the `^8.3` floor is intentional. — `composer.json`.

## 5. Marketplace & Selling

Marketplace and Composer copy now use the buyer-facing positioning below, and composer keywords include healthcare, clinic, medical, appointments, booking, clinicians, patient acquisition, care pathways, accessibility, and WCAG terms.

**Improved 1-sentence summary:**

> A premium, appointment-led theme for private clinics and healthcare groups — service discovery, clinician profiles, care pathways, locations, and a booking-ready enquiry panel, all WCAG-minded and brand-tunable in Theme Studio.

**Improved 3–4 sentence description:**

> Theme Healthcare turns Capell into a conversion-focused clinical website. It ships nineteen care-oriented sections — an appointment hero, service finder, clinician carousel, care-pathway guidance, insurance/trust signals, locations, events, and a booking panel — that route patients toward the right enquiry with calm, clinical styling. The booking, events, and resource sections light up automatically when Capell Bookings/Form Builder, Events, and Blog are installed, with no theme reconfiguration. Built on Foundation Theme with accessible focus states and a skip link, it activates from the Themes screen and seeds a full demo via `capell:theme-healthcare-demo`.

(Note: the description should only claim "booking-ready" and the Bookings cross-sell once §2.2 ships; until then, soften to "contact-led enquiry panel.")

**Screenshot / media gaps:** This is the biggest selling gap. Marketplace uses 3 committed JPG preview assets, but the deployment manifest still expects 8 route-backed PNG captures (homepage desktop + mobile, services listing, clinician detail, full rendered page, contact, admin list, preview). Either build the `/theme-healthcare-directory`, `/theme-healthcare-detail`, and `/theme-healthcare-contact` demo routes those captures reference or repoint them at `/theme-healthcare-demo`. Add a dark-mode pair once §2.7 lands (siblings already do this).

**Differentiation vs the other 9 Capell themes** (agency, commerce, corporate, education, knowledge, local-services, nonprofit, portfolio, saas): today healthcare differs only by palette (deep teal + amber) and copy. Its defensible niche is **clinical conversion + compliance**: a real booking/enquiry flow, clinician credentials, insurance transparency, emergency escalation, and demonstrable WCAG AA. Lean the marketing into "the only Capell theme built for patient acquisition and accessibility compliance."

**Target buyer:** private clinics, specialist/consultant practices, multi-site healthcare groups, dental/physio/aesthetic practices, and agencies building patient-acquisition sites for medical clients.

**Keywords / tags (8–12):** Composer keywords now include `healthcare`, `clinic`, `medical`, `appointments`, `booking`, `clinicians`, `patient acquisition`, `care pathways`, `accessibility`, `WCAG`, `dental physiotherapy`, and `Capell theme`; `definition()` tags are still only `Healthcare`, `Appointments`, and `Services`.

## 6. Prioritized Roadmap

| Item                                                                                       | Bucket | Effort | Impact | Section ref |
| ------------------------------------------------------------------------------------------ | ------ | ------ | ------ | ----------- |
| Commit the 8 real PNG screenshots + replace SVG placeholders                               | Now    | M      | High   | §4.3, §5    |
| Add an explicit test documenting runtime `extends: default` vs manifest package dependency | Now    | S      | Med    | §4.1, §4.9  |
| Make booking panel a live form / strong CTA (Form Builder + Bookings)                      | Next   | M      | High   | §2.2, §3    |
| Wire hardcoded hex to brand tokens so presets recolour the page                            | Next   | L      | High   | §2.6, §4.6  |
| Add empty-state fallbacks to item-driven sections                                          | Next   | M      | Med    | §2.5        |
| Extract / unify the inline events `<script>` onto carousel markers                         | Next   | M      | Med    | §2.4, §4.4  |
| Render locations with hours/address/click-to-call/map                                      | Next   | M      | High   | §3          |
| WCAG AA contrast audit of muted greys + heavy weights                                      | Next   | M      | High   | §4.5        |
| Add image perf attributes (hero `fetchpriority`+dims; rest `loading=lazy`)                 | Done   | S      | High   | §2.1, §4.5  |
| Render a real `id="main-content"` target so the skip link works                            | Done   | S      | High   | §4.5        |
| Translate events carousel "Previous"/"Next" button text                                    | Done   | S      | Med    | §2.3, §4.5  |
| Rewrite marketplace summary + composer description; expand keywords/tags                   | Done   | S      | High   | §5          |
| Flesh out the critical health check (assert views/renderer/assets)                         | Done   | S      | Med    | §4.2        |
| Add dark-mode support (CSS vars + `prefers-color-scheme`)                                  | Later  | L      | Med    | §2.7        |
| Add clinician detail + conditions/treatments directory surfaces                            | Later  | L      | High   | §3          |
| Add emergency/urgent-care escalation banner component                                      | Later  | S      | Med    | §3          |
| Assert the 20ms render budget + accessibility in tests                                     | Later  | M      | Med    | §4.7, §4.9  |
