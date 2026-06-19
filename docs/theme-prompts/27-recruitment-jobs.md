# Theme: RecruitmentJobs (`theme-recruitment-jobs`)

Read [`_shared-build-contract.md`](./_shared-build-contract.md) first. It owns the
package skeleton, manifest rules, service-provider shape, public-output-safety
test, and the monorepo autoload steps. This prompt only describes what makes
`theme-recruitment-jobs` distinct.

## Build this

Build a structured, two-sided theme for recruitment agencies and job boards that
serve both candidates and employers. The mood is confident and professional:
teal and indigo on cool white, a structured grid, and clear bordered cards. It
leads with a job-listings grid (title, company, location, salary, type), a sectors
strip, side-by-side candidate/employer paths, a "how it works" process, placement
testimonials, and a static submit-CV call to action. It extends Foundation, owns
the seven section views plus six new recruitment sections, ships rich "Beacon
Talent" demo data, and carries no migrations, routes, models, or settings.

## Inspiration

Borrow from modern recruitment-agency and niche job-board sites (specialist tech
recruiters, two-sided talent marketplaces, exec-search firms). Take: the
filterable-looking job grid with salary and location chips, the sector pills, the
clear split between "I'm looking for work" and "I'm hiring", the numbered process,
and the placement success stories. All jobs, companies, salaries, and copy are
invented demo content — do not copy any agency's listings.

## Gap it fills

Against the current set (agency, commerce, corporate, education, estate-agents,
healthcare, inertia-bookings, knowledge, liquid-glass, local-services, nonprofit,
portfolio, restaurant, saas), there is no recruitment / job-board vertical.
`corporate` is generic B2B; `agency` is creative-studio (different "agency"
entirely); `saas` is product-led. None present a job-listings grid, sector pills, a
two-audience split, or placement testimonials. RecruitmentJobs owns the
find-a-job / hire-talent use case.

## Package identity

| Field       | Value                                                                                                     |
| ----------- | --------------------------------------------------------------------------------------------------------- |
| package     | `capell-app/theme-recruitment-jobs`                                                                       |
| slug        | `theme-recruitment-jobs`                                                                                  |
| namespace   | `Capell\ThemeStudio\RecruitmentJobs`                                                                      |
| themeKey    | `recruitment-jobs`                                                                                        |
| displayName | `Recruitment & Jobs`                                                                                      |
| tier        | `premium`                                                                                                 |
| bestFit     | `["Recruitment agencies", "Job boards", "Specialist recruiters", "Exec search", "In-house talent teams"]` |
| tags        | `["Recruitment", "Jobs", "Hiring", "Two-sided", "Structured"]`                                            |

View namespace `capell-theme-recruitment-jobs`; translation namespace
`capell-theme-recruitment-jobs`.

## Design direction

| Token              | Value         | Rationale                                                       |
| ------------------ | ------------- | --------------------------------------------------------------- |
| primaryColor       | `#0f766e`     | Teal — trustworthy, professional primary for nav and CTAs.      |
| accentColor        | `#6366f1`     | Indigo — energetic accent for the employer side and highlights. |
| neutralColor       | `#0f172a`     | Slate ink for crisp text grounding.                             |
| surfaceColor       | `#f8fafc`     | Cool white so bordered cards read cleanly.                      |
| foregroundColor    | `#0f172a`     | Slate foreground; high legibility for listings.                 |
| headingFont        | `sora`        | Modern geometric voice for confident headings.                  |
| bodyFont           | `inter`       | Neutral, dense-friendly companion for job detail.               |
| spacing            | `balanced`    | Content-rich but breathable for grids.                          |
| alignment          | `left`        | Left-aligned for scannable job rows.                            |
| cardStyle          | `bordered`    | Hairline-bordered cards suit a structured listings grid.        |
| navigationStyle    | `prominent`   | Persistent bar with "Find a job" and "Hire talent".             |
| layoutPresentation | `structured`  | Tidy grid layout signals organisation and trust.                |
| motionIntensity    | `subtle`      | Restrained hover and reveal; professional, not flashy.          |
| mediaTreatment     | `framed`      | Framed media for company logos and team imagery.                |
| radius             | `md`          | Soft, modern corners suit the SaaS-adjacent feel.               |
| headingScale       | `balanced`    | Strong but measured; clarity over drama.                        |
| cardDensity        | `comfortable` | Room around job and process cards.                              |

Typography: `sora` for headings and job titles; `inter` for company, location,
salary, and process detail; indigo accents for employer-side elements and teal for
candidate-side. Motion: subtle hover lift on job cards, fade-and-rise on reveal, no
autoplay. Respect `prefers-reduced-motion`.

## Sections

`includedSections`:

```
['navigation', 'hero', 'job-listings', 'sectors', 'candidate-employer-paths',
 'process', 'placements', 'submit-cv-cta', 'features', 'proof',
 'content-listing', 'footer']
```

NEW sections (each a
`ViewSectionRenderer(self::THEME_KEY, '<key>', 'capell-theme-recruitment-jobs::sections.<key>', failLoudly: true)`):

- **job-listings** (NEW) — render data:
  `{ heading, summary, jobs: [{ title, company, location, salary, type, posted }] }`.
- **sectors** (NEW) — render data:
  `{ heading, summary, sectors: [{ name, openings }] }`.
- **candidate-employer-paths** (NEW) — render data:
  `{ heading, paths: [{ audience, heading, points: [string], label }] }` —
  exactly two paths: one `audience: "candidate"`, one `audience: "employer"`.
- **process** (NEW) — render data:
  `{ heading, summary, steps: [{ name, detail }] }`.
- **placements** (NEW) — render data:
  `{ heading, summary, testimonials: [{ quote, name, role }] }`.
- **submit-cv-cta** (NEW) — render data:
  `{ heading, summary, primaryLabel, secondaryLabel, note }` — STATIC prompt; no
  form submission, no file upload, no DB queries.

`features`, `proof`, `content-listing`, `navigation`, `hero`, `footer` reuse the
standard typed Data objects.

## Beta data (demo profile)

Seed via `Install RecruitmentJobs ThemeDemoAction` →
`ThemeDemoPageInstaller::run(...)` plus a profile entry. All copy original.

**Brand:** Beacon Talent — "Specialist recruitment that actually listens."

**Hero:** heading "Find your next role. Or your next hire." Summary: "We're a
specialist recruitment team working across Tech, Finance, and Healthcare. We take
the time to understand the role and the person — so candidates land somewhere they
fit, and employers hire people who stay."

**Features (4 cards — title / summary / type):**

1. Specialist Consultants — "Recruiters who know your field, not generalists
   reading a brief." / `service`.
2. Honest Shortlists — "We send three strong candidates, not thirty maybes." /
   `feature`.
3. Salary Transparency — "Every role we advertise shows a real salary band." /
   `feature`.
4. Aftercare Guarantee — "Free replacement within the first 12 weeks if a hire
   doesn't work out." / `service`.

**Proof (3 metrics — metric / name / quote):**

1. "2,400+" — placements made — "Beacon found me a role I didn't know I wanted." —
   Olivia M.
2. "21 days" — average time-to-hire — "Filled a hard role in three weeks." —
   Head of Eng, fintech scale-up.
3. "94%" — candidates still in role at 12 months — "They place for fit, not just
   speed." — HR Director, NHS trust.

**Job listings (jobs — title / company / location / salary / type / posted):**

- Senior Backend Engineer — Northwind Labs — Remote (UK) — £75k–£95k — Permanent —
  posted "2 days ago".
- Finance Business Partner — Harbor & Co — London (hybrid) — £60k–£72k —
  Permanent — posted "4 days ago".
- Clinical Nurse Specialist — Meadowview NHS Trust — Leeds — £43k–£50k — Permanent
  — posted "1 day ago".
- DevOps Engineer — Cirrus Cloud — Bristol (hybrid) — £65k–£85k — Contract —
  posted "6 days ago".
- Management Accountant — Vale Group — Manchester — £45k–£55k — Permanent —
  posted "3 days ago".
- Frontend Engineer — Brightside — Remote (UK) — £55k–£70k — Permanent — posted
  "Today".

**Sectors (name / openings):**

- Technology — 142 open roles.
- Finance — 88 open roles.
- Healthcare — 64 open roles.
- Operations — 37 open roles.

**Candidate / employer paths (exactly two):**

- audience "candidate" — heading "Looking for your next role?" — points:
  ["Confidential, no-spam search", "Honest salary guidance",
  "Interview prep that actually helps", "Roles that match your skills, not just
  your CV"] — label "Browse jobs".
- audience "employer" — heading "Hiring? Let's get it right." — points:
  ["A consultant who knows your sector", "Three strong candidates, fast",
  "Transparent fees, no surprises", "12-week replacement guarantee"] — label
  "Request a callback".

**Process (steps — name / detail):**

- "Discovery" — "We learn the role, the team, and what 'right' looks like."
- "Search" — "We map the market and approach the best-fit people directly."
- "Shortlist" — "You meet a tight, qualified shortlist — usually within ten days."
- "Offer & onboard" — "We manage the offer and stay close through the first
  weeks."

**Placements (testimonials — quote / name / role):**

- "Beacon understood exactly the kind of team I wanted to join." — Olivia M. —
  Senior Engineer, Northwind Labs.
- "We'd struggled to fill this role for months. Beacon did it in three weeks." —
  James T. — Head of Engineering.
- "The most human recruitment experience I've had on either side." — Priya S. —
  HR Director.

**Submit-CV CTA (STATIC):** heading "Send us your CV — confidentially."
Summary: "Drop us your details and a consultant in your field will be in touch.
We never share your CV without your say-so." primaryLabel "Submit your CV",
secondaryLabel "Browse all jobs", note: "This opens a request — your CV is only
shared with employers you approve."

**Directory sample (content-listing spotlight/gallery/pathways):** 3 entries —
"This week's standout roles", "How to write a CV that gets shortlisted",
"Hiring guide: writing a job spec that attracts the right people". Each a one-line
summary.

**Detail sample:** A job detail page for "Senior Backend Engineer — Northwind
Labs" — overview, responsibilities, requirements, salary band, location/type, and
a "Apply for this role" CTA.

## Build steps

1. Scaffold `packages/theme-recruitment-jobs/` per shared-contract §2 (copy
   `packages/theme-saas` and rename identity).
2. Write `capell.json` (v3, §3): `themeKey: recruitment-jobs`,
   `extends: capell-app/foundation-theme`, `surfaces: ["frontend"]`,
   `requires: ["capell-app/core", "capell-app/frontend"]`, demo command
   `capell:theme-recruitment-jobs-demo` with
   `demoParams: ["url","languages","sites"]`,
   `capabilities: ["theme-recruitment-jobs", "theme-recruitment-jobs-frontend"]`,
   `security.publicOutput` true / `riskTier: low`,
   `cacheTags: ["theme-recruitment-jobs"]`, health check
   `theme-recruitment-jobs.package-health`, the `admin-page` contribution,
   `database` all false.
3. Write `RecruitmentJobsThemeServiceProvider` (§4): package registration in
   `register()`; `boot(ThemeRegistry)` registers the demo command, gates on
   installed, loads translations + views, registers CSS import + Blade source,
   registers the page adapter, then `$registry->register(...)`.
4. `definition(): ThemeDefinitionData` with the `includedSections` above and one
   "Beacon" preset carrying the token table values; `extends: 'default'`,
   `runtime: FrontendRuntime::Blade`.
5. Register a `ViewSectionRenderer` per section key including the six new keys.
6. Build `RecruitmentJobsThemePageAdapter` mapping `meta.theme_demo.render_data`
   into the typed section data and the new `job-listings` / `sectors` /
   `candidate-employer-paths` / `process` / `placements` / `submit-cv-cta` shapes,
   all query-free. The submit-CV section must render static markup only.
7. Add `Install RecruitmentJobs ThemeDemoAction implements InstallsThemeDemo` →
   `ThemeDemoPageInstaller::run($data, 'recruitment-jobs', 'RecruitmentJobs')`, a
   `DemoCommand` for `capell:theme-recruitment-jobs-demo`, and a `profile()` entry
   with all the copy.
8. Write `page.blade.php` (skip link, brand tokens inline, `data-capell-theme`,
   `{!! $content !!}`), thin `livewire/page/page.blade.php`
   (`RenderCurrentThemePageAction::run()`), and one Blade per customised + new
   section. Use `@frontendAsset('css/theme-recruitment-jobs.css')`.
9. Add `resources/css/theme-recruitment-jobs.css` (cool-white surface,
   teal/indigo accents), `resources/lang/en/generic.php`, and the boost guideline
   view.
10. Add `Theme RecruitmentJobs HealthCheck` and the manifest contribution.
11. Mirror autoload in **both** `composer.json` and `composer.local.json`, then
    `COMPOSER=composer.local.json composer dump-autoload --no-scripts`.

## Acceptance criteria

Standard tests: `ManifestRequirementsTest`,
`RecruitmentJobsThemeDefinitionTest`, `RecruitmentJobsThemePageAdapterTest`,
`PublicOutputSafetyTest`, `DemoCommandTest`,
`Theme RecruitmentJobs HealthCheck` feature test.

Theme-specific assertions:

1. The adapter builds a `job-listings` section whose `jobs` carry `company`,
   `location`, `salary`, and `type`; assert "Senior Backend Engineer / Northwind
   Labs / Remote (UK) / £75k–£95k".
2. `candidate-employer-paths` renders exactly two paths with audiences
   "candidate" and "employer".
3. `submit-cv-cta` is static — rendered Blade contains no `<form` submission, no
   file upload input, no `wire:`, and no DB query.
4. `PublicOutputSafetyTest` confirms no recruitment Blade or lang string leaks the
   package name, `authoring`, `Filament`, `signed`, `data-field`, `data-model`,
   or any DB query.

## Verification

```bash
vendor/bin/pest packages/theme-recruitment-jobs/tests --configuration=phpunit.xml
COMPOSER=composer.local.json composer preflight
```
