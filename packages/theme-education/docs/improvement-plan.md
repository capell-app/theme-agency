# Theme Education — Improvement & Growth Plan

> Package: capell-app/theme-education · Kind: theme · Tier: premium · Product group: Capell Themes · Bundle: themes · Status: Draft

## 1. Snapshot

`theme-education` registers theme key `education` via `EducationThemeServiceProvider::definition()` (a `ThemeDefinitionData` with 16 `includedSections` and one `education` preset) and a `BladeThemeRenderer` whose layout view is `capell-theme-education::page`. Section renderers are built by mapping `includedSections` to `capell-theme-education::sections.<key>` views, skipping `navigation`/`footer` (treated as foundation sections) and any view that does not exist; `events`, `admissions-checklist`, `enrolment-cta`, and `resources` receive optional integration flags for Events / Form Builder / Blog. The demo command is `capell:theme-education-demo {--url=} {--languages=} {--sites=} {--force}`, delegating to `InstallEducationThemeDemoAction` → `ThemeDemoPageInstaller::run($data, 'education', 'Education')`; demo layouts are route-backed pages described in `docs/screenshots.json` (homepage, course catalogue, instructors, events, enrolment, resources, plus admin theme list + preview). The theme **overrides** 14 section views + a `page.blade.php` shell + `theme-education.css`, and **inherits** navigation, footer, and demo plumbing from `capell-app/foundation-theme`. Marketplace and Composer copy are buyer-facing, keywords are education-specific, and `capell.json` marketplace media is limited to 3 committed JPG preview assets. The fuller `docs/screenshots.json` route-backed PNG set still declares 9 captures, and the `docs/screenshots/` directory referenced by that manifest does not exist.

## Completed Improvement Slices

- **2026-06-03:** Added buyer-facing marketplace and Composer copy, replaced the stub Diagnostics health check with real theme registration/view/vendor-asset probes, and limited marketplace screenshots to committed preview assets.
- **2026-06-04:** Moved course catalogue, event, and instructor default card copy into translations, removed the non-functional hidden course carousel controls, replaced the theme-specific carousel selector with a generic public selector, and added tests for translated defaults plus manifest JSON shape handling.

## 2. Improvements (existing functionality)

1. **Course catalogue defaults are translated.** — The fixed course catalogue defaults now come from `capell-theme-education::generic.course_catalog_cards`, so the multi-language demo can localise format, title, and summary copy. Full editor-provided catalogue data is still a separate row. — `resources/views/sections/course-catalog.blade.php`, `resources/lang/en/generic.php`, `tests/Unit/EducationThemeDefinitionTest.php` — **M**
2. **Events defaults are translated.** — Static event card titles, signal labels, and summaries now come from `capell-theme-education::generic.event_cards`; the connected/static availability badge remains package-aware. Real Events package data remains a separate row. — `resources/views/sections/events.blade.php`, `resources/lang/en/generic.php`, `tests/Unit/EducationThemeDefinitionTest.php` — **S**
3. **Instructor role defaults are translated.** — The role list now comes from `capell-theme-education::generic.instructor_roles` instead of an inline Blade array. — `resources/views/sections/instructors.blade.php`, `resources/lang/en/generic.php`, `tests/Unit/EducationThemeDefinitionTest.php` — **S**
4. **Drive section colours from brand tokens, not arbitrary hex** — the shell sets `--site-theme-primary`/`--site-theme-accent` (`page.blade.php` emits `$brand->tokens()`; CSS declares them), yet sections paint with literals like `bg-[#1d4ed8]`, `text-[#4338ca]`, `text-[#0f766e]`. Preset/brand changes from Theme Studio won't propagate to the body, only the shell. Swap arbitrary hex for `bg-[color:var(--site-theme-primary)]` style utilities (or CSS classes). — `resources/views/sections/hero.blade.php`, `course-catalog.blade.php`, `features.blade.php`, `instructors.blade.php`, `events.blade.php`, others — **L**
5. **Make `course-catalog` data-driven** — unlike `features`/`content-listing`/`outcomes`/`pathway-comparison` (which loop `$section->items`), the catalogue renders three fixed `<article>` cards with no loop and no empty state. An editor cannot change the number of courses or their content. — `resources/views/sections/course-catalog.blade.php` — **M**
6. **Make `events` data-driven with an Events integration path** — the section receives `$eventsAvailable` but still renders three static cards regardless; when Events is installed it should iterate real event data passed via the renderer, not just flip a status badge. — `resources/views/sections/events.blade.php` — **M**
7. **Give `navigation` and `footer` real content or remove them from the theme** — both views render only an `@isset($heading)` headline inside a `theme-section`. They are listed in `includedSections` but skipped by `isFoundationSection()`, so these files are effectively dead unless rendered as ordinary sections; either flesh them out (nav links, footer columns) or delete the orphan views. — `resources/views/sections/navigation.blade.php`, `footer.blade.php`, `src/EducationThemeServiceProvider.php:147` — **M**
8. **Add a `prefers-reduced-motion` block** — `features` cards use `transition hover:-translate-y-1`; the CSS has no reduced-motion guard. Add `@media (prefers-reduced-motion: reduce)` to disable transforms/transitions. — `resources/css/theme-education.css` — **S**
9. **Optional: dark-mode variant** — no Capell theme currently ships `dark:` variants, so this is greenfield differentiation rather than a regression. A `dark:` token set on `.education-shell` would set the theme apart in the marketplace. — `resources/css/theme-education.css` + section views — **L**

## 3. Missing Features (gaps)

Declared `capabilities[]`: `theme-education`, `theme-education-frontend` — coarse, frontend-only. Against what an education/schools site actually needs:

**Table-stakes the theme lacks:**

- **Course/program detail layout** — there is a catalogue _teaser_ (3 static cards) but no single-course/single-program section (syllabus, duration, fees, intake dates, prerequisites, outcomes, apply CTA). This is the core education page type.
- **Faculty/staff directory** — `instructors` is a fixed 3-card teaser, not a browsable staff/faculty listing with photos and bios.
- **Events/calendar** — `events` is three static cards even when `capell-app/events` is installed; no dated list, no open-day/term calendar, no iCal/structured data.
- **Admissions/enrolment funnel** — `admissions-checklist` + `enrolment-cta` exist but render fallback copy; no genuine multi-step application flow wired through Form Builder, no fees/financial-aid block.
- **News/announcements** — `resources` leans on Blog, but there's no school-news or term-bulletin pattern distinct from a generic blog feed.
- **Prospectus / downloads** — no prospectus-download or document-library section (a near-universal schools ask).

**Differentiators (would set it apart):**

- **Programme-comparison matrix** — `pathway-comparison` is a start; a true side-by-side compare table (format / duration / level / price) would be a standout for course providers.
- **Student/parent portal links** — a configurable "log in to portal / VLE / LMS" block.
- **Campus/facilities** — gallery + map section for physical schools.
- **Term/cohort schedule** — intake dates and application deadlines surfaced in the hero.

**Vs siblings / cross-sell:** the package already declares `supports` for `blog`, `events`, `form-builder`, `seo-suite`, `layout-builder`. The gap is that the optional sections accept availability flags but render static fallbacks instead of real integrated content — so the **events/form-builder cross-sell is advertised but not realised**. Closing gap #6 and the admissions funnel is where the events/bookings cross-sell becomes real.

## 4. Issues / Risks

1. **Dead carousel controls removed.** `course-catalog.blade.php` keeps native horizontal scroll-snap on mobile, removes the hidden/unwired prev/next buttons, and uses `data-carousel="course-catalog"` instead of the theme-specific `education-course-catalog` selector. — `resources/views/sections/course-catalog.blade.php`, `tests/Unit/EducationThemeDefinitionTest.php`
2. **Health check shipped.** `ThemeEducationHealthCheck` now probes Theme Studio registration, required views, and vendor asset registration; tests cover passing diagnostics and an unregistered theme failure. — `src/Health/ThemeEducationHealthCheck.php`, `tests/Unit/ThemeEducationHealthCheckTest.php`
3. **Stub manifest contribution.** `ThemeManagementPageContribution` also only returns `compatibleCapellApiVersion()`; confirm `ThemeExtensionPage` actually consumes the `themeKey: education` param from `capell.json` and that this empty class is sufficient, or it is dead. — `src/Manifest/ThemeManagementPageContribution.php` — **(S)**
4. **`extends` value mismatch.** `definition()` sets `extends: 'default'` (and `EducationThemeDefinitionTest` asserts `->extends->toBe('default')`), but `capell.json` (`"extends": "capell-app/foundation-theme"`), `README`, and `docs/overview.md` all say it extends Foundation Theme. Reconcile: either the runtime `extends` should be the foundation theme key, or the docs/manifest should stop implying a package-level extends relationship. — `src/EducationThemeServiceProvider.php:64`, `capell.json`, `README.md` — **(S)**
5. **Surfaces mismatch.** `capell.json` declares `"surfaces": ["frontend"]`, but `README` ("Surfaces: frontend, console") and `docs/overview.md` ("Contexts: frontend, console") claim console too (the demo command is a console surface). Align the manifest or the docs. — `capell.json`, `README.md`, `docs/overview.md` — **(S)**
6. **Screenshot capture gap remains.** `capell.json marketplace.screenshots` is now limited to 3 committed JPG preview assets, but `docs/screenshots.json` still lists 9 route-backed PNG captures under `packages/theme-education/docs/screenshots/*.png`, and that directory does not exist. Generate the deployment capture set before calling the Education media story complete. — `docs/screenshots.json`, `docs/assets/marketplace/`, `README.md` — **(M)**
7. **`previewImage`/`assets` paths assume a publish step.** `definition()` references `/vendor/capell/themes/education.jpg` and `vendor/capell/themes/education.css`, but the package commits neither an `education.jpg` nor a published `education.css` (only `resources/css/theme-education.css`, registered via `VendorAssetData::tailwindImport`). Verify the preview image and the `assets['css']` path resolve after install, or the theme card and asset load 404. — `src/EducationThemeServiceProvider.php:32,41,62` — **(M)**
8. **Performance budget plausibility.** `capell.json` sets `frontendRenderBudgetMs: 20`, `adminQueryBudget: 0`, `cacheSafety.cacheable: false`. Pure-Blade sections with no DB access make `adminQueryBudget: 0` correct, but `cacheable: false` for static marketing sections forfeits caching headroom and makes the 20 ms render budget harder to honour under load. Revisit whether the public output can be cached (varies by site/locale only). — `capell.json` performance block — **(S)**
9. **WCAG gaps (high stakes for education/public-sector).** Decorative blueprint blocks correctly use `aria-hidden="true"`, and a skip link + `:focus-visible` outline exist. Remaining risks: (a) heading hierarchy — most sections start at `<h2>` with `@isset($heading)`, so a page can render with no `<h1>`; (b) **colour-contrast** — `text-stone-600`/`text-slate-600` body copy on white and especially `text-[#0f766e]` small-caps eyebrow text need a 4.5:1 check; (c) carousel arrows are `aria-label`led but non-functional (see #1), which is worse than absent for screen-reader/keyboard users. — section views + `resources/css/theme-education.css` — **(M)**
10. **Test coverage gaps.** `tests/` covers: theme definition contract, translated default education layouts, manifest requirements, package-aware rendering (events connected/static), public-output safety, health diagnostics, and demo-command delegation/idempotency — a solid base. **Not covered:** that _every_ `includedSection` has a renderable view (a missing view is silently filtered by `view()->exists()`); full content-listing/resources variant matrices; real Events data; accessibility (heading order, contrast). Add a test iterating `definition()->includedSections` and asserting each non-foundation key resolves to an existing view. — `tests/Unit/` — **(M)**
11. **Changelog started.** Release notes now exist for the marketplace/health-check slice and the translated default-copy slice. Continue adding entries for every package improvement wave. — `CHANGELOG.md`

Public-output safety is otherwise well-handled: `page.blade.php` emits only brand tokens + `{!! $content !!}`, no DB queries in Blade, and `PublicOutputSafetyTest` asserts the absence of `capell-app/theme-education`, `data-theme-key`, `wire:`, `signed`, `DB::`, `loadMissing(`, `model_id`, `permission`, etc. Keep that test green for any new section.

## 5. Marketplace & Selling

Marketplace and Composer copy now use the improved buyer-facing positioning below, and composer keywords include education, courses, enrolment, academy, bootcamp, curriculum, instructors, admissions, and learning-pathway terms.

**Improved 1-sentence summary:**

> A polished, course-first theme for schools, academies, and training providers — turning programme discovery, faculty trust, open days, and enrolment into one coherent learner journey.

**Improved 3–4 sentence description:**

> Theme Education gives schools, course providers, and training teams a complete learning-pathway frontend without commissioning a custom build. Purpose-shaped sections cover course catalogues, instructor and mentor profiles, learning outcomes, open days, resources, FAQs, and a guided enrolment call-to-action. It integrates optionally with Capell Events for open-day calendars, Form Builder for applications and enquiries, and Blog for learning resources — degrading gracefully when those aren't installed. Built on Foundation Theme with brand-token theming, an accessible skip link and focus states, and zero database impact, so editors compose education pages through the normal Layout Builder workflow.

**Screenshot/media gaps:** still the single biggest selling blocker. Marketplace now uses 3 committed JPG preview assets, but `docs/screenshots.json` still asks deployment to produce 9 real PNGs (homepage, course catalogue, instructors, events, enrolment, resources, theme admin list, frontend render, signed preview). Run the screenshot QA playbook against the seeded demo, commit those PNGs, and reconcile README/overview references to that route-backed set.

**Differentiation / target buyer:** sits among 10 sibling themes; closest neighbours are `theme-knowledge` and `theme-corporate`. Differentiate on the **enrolment funnel** (catalogue → instructors → outcomes → admissions checklist → enrolment CTA) and the **Events + Form Builder cross-sell** — no other theme is shaped end-to-end around converting a learner from discovery to application. **Target buyer:** independent course providers, bootcamps/academies, training departments, tutoring businesses, and small private schools running Capell.

**Keywords/tags (8–12):** Composer keywords now include `education`, `courses`, `school`, `e-learning`, `training`, `enrolment`, `academy`, `bootcamp`, `curriculum`, `instructors`, `admissions`, and `learning-pathway`; `definition()` tags `['Education','Courses','Enrolment']` are good but thin.

## 6. Prioritized Roadmap

| Item                                                                                | Bucket | Effort | Impact | Section ref    |
| ----------------------------------------------------------------------------------- | ------ | ------ | ------ | -------------- |
| Capture & commit the 9 real demo screenshots; reconcile both manifests + README     | Now    | M      | High   | §4.6, §5       |
| Reconcile `extends` (`default` vs foundation) and `surfaces` (frontend vs +console) | Now    | S      | Med    | §4.4, §4.5     |
| Verify `previewImage` / `assets` css paths resolve post-install                     | Now    | M      | Med    | §4.7           |
| Translate hard-coded copy in `course-catalog`, `events`, `instructors`              | Done   | M      | High   | §2.1–2.3       |
| Rewrite marketplace `summary` (+ extend composer keywords/description)              | Done   | S      | High   | §5             |
| Fix or remove the dead carousel prev/next controls                                  | Done   | M      | Med    | §4.1           |
| Give `ThemeEducationHealthCheck` real assertions (theme key, views, assets)         | Done   | M      | High   | §4.2           |
| Add test iterating `includedSections` → each view exists & renders                  | Next   | M      | Med    | §4.10          |
| Make `course-catalog` and `events` data-driven (loops + empty states)               | Next   | M      | High   | §2.5, §2.6, §3 |
| Drive section colours from `--site-theme-*` brand tokens, not arbitrary hex         | Next   | L      | Med    | §2.4           |
| Resolve `navigation`/`footer` orphan views (flesh out or delete)                    | Next   | M      | Med    | §2.7           |
| WCAG pass: guarantee an `<h1>`, audit contrast, fix carousel a11y                   | Next   | M      | High   | §4.9           |
| Add `prefers-reduced-motion` guard + start `CHANGELOG.md`                           | Next   | S      | Low    | §2.8, §4.11    |
| Add course-detail + faculty-directory + admissions-funnel sections                  | Later  | L      | High   | §3             |
| Add dark-mode token set as a marketplace differentiator                             | Later  | L      | Med    | §2.9, §3       |
