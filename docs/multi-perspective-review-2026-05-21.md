# Multi-Perspective Review — 2026-05-21

Audit of all 45 packages across 12 review lenses. Fix pass was blocked on org usage limit; this doc captures findings and ready-to-dispatch fix batches for resumption.

## Status

- ✅ 12 lens reviews complete (read-only).
- ❌ 6 fix worktree agents blocked (org monthly usage limit). Zero edits applied.
- ⚠️ 2 of 2 P1 migration findings spot-checked were FALSE POSITIVES. Verify every finding before fixing.

## Verified false positives

- `publishing-studio/database/migrations/2026_05_10_190866_10_z_add_workspace_id_to_import_sessions_table.php:33` — already uses `dropForeign`, not `dropForeignKey`. Lens hallucinated.
- `tags/database/migrations/2026_05_10_190872_01_alter_tags_table.php` down() — already has `Schema::hasColumn` guards. Lens hallucinated.

## P1 findings (verify before fixing)

### Multi-tenancy — cross-site data leak via admin

`SiteScope::applyForCurrentActor()` opt-in pattern; these Resources never call it:

- `packages/public-actions/src/Filament/Resources/Submissions/PublicActionSubmissionResource.php` — exposes every site's form submissions (name, email, payload).
- `packages/public-actions/src/Filament/Resources/PublicActions/PublicActionResource.php`
- `packages/content-sections/src/Filament/Resources/Sections/SectionResource.php:70` — `getEloquentQuery()` only strips SoftDeletingScope, no site filter; combined with bulk delete = cross-site delete.
- `packages/layout-builder/src/Filament/Resources/Layouts/LayoutResource.php`
- `packages/events/src/Filament/Resources/Events/EventResource.php` + Occurrences/Registrations/Venues resources.
- `packages/campaign-studio/src/Filament/Resources/{CampaignGroups,CampaignCtaBlocks,CampaignLandingPages,CampaignConversionGoals}` — all 4.
- `packages/access-gate/src/Filament/Resources/AccessAreas/AccessAreaResource.php`

Widget actions feeding null siteId:

- `packages/insights/src/Filament/Widgets/Concerns/BuildsInsightsDashboardWindow.php:14` — `InsightsWindowData` with no siteId.
- `packages/search/src/Actions/{BuildTopSearchesQueryAction,BuildZeroResultSearchesQueryAction,BuildTrendingSearchesQueryAction}.php` — `SearchLog::query()` unfiltered. Callers in TopSearchesWidget, SearchOverviewStatsWidget, AdminServiceProvider.

Suggested architectural fix: register `BelongsToSite` as global Eloquent scope on tenant-bound models + Arch test asserting every Resource whose model has `site_id` either scopes via `SiteScope` or registers global scope.

### Missing Policy classes

These packages ship zero policies — any admin can CRUD regardless of role:

- events (Event, EventOccurrence, EventRegistration, EventVenue)
- campaign-studio (4 models)
- blog (verify Article — may already exist)
- tags
- content-sections
- address

### Security

- **Workspace cookie auth bypass** — `packages/publishing-studio/src/Http/Middleware/ResolveWorkspaceContext.php:130` — `userMayResolve()` returns true for guests with raw UUID cookie. Sign cookie via HMAC bound to ip+session.
- **Beacon CSRF / cross-origin manifest leak** — `packages/frontend-authoring/src/Http/Controllers/BeaconController.php:23` — `withoutMiddleware([VerifyCsrfToken::class])`, no Origin / Sec-Fetch-Site check. Cross-origin page visited by admin can fetch editor manifest + signed edit URLs. NOTE: Frontend-authoring-safety lens called it clean; security lens flagged it. Cross-lens disagreement.
- **Stored XSS via empty payload schema** — `packages/public-actions/src/Actions/SubmitPublicActionAction.php:84` — when `payload_schema.fields` empty, accepts unbounded `$request->except([...])` into payload.
- **OAuth token logging** — `packages/deployments/src/Http/Controllers/OAuth/{GitHub,Bitbucket,GitLab}CallbackController.php` — logs raw `$tokenResponse` on failure (can include access tokens).
- **Mass-assignment risk** — `packages/deployments/src/Models/DeploymentConnection.php:36` — `$guarded = []` on table with `access_token_encrypted` columns.
- **Agent-bridge capability policy** — `packages/agent-bridge/src/Actions/Pages/CreateDraftPageCapabilityAction.php:32` — accepts caller-supplied `site_id` without per-site policy enforcement.
- **Agent-bridge token hashing** — uses `hash('sha256', $plain)` unsalted. Upgrade to `hash_hmac('sha256', $plain, app.key)` with dual-verify migration path.

### API & Integration

- **Kit webhook replay** — `packages/newsletter/src/Support/Providers/KitProviderAdapter.php:84` — static shared-secret compare instead of HMAC over body. Apply HMAC pattern; check Mailchimp + CampaignMonitor adapters too.
- **No webhook idempotency** — `packages/newsletter/src/Actions/HandleProviderWebhookAction.php` — provider retries duplicate consent rows. Add `newsletter_processed_webhook_events` dedupe table.
- **No HTTP timeouts on gitops** — `packages/deployments/src/Services/GitProvider/{BitbucketProvider,GitHubProvider}.php` — no `->timeout()` on Http base config. Stuck upstream hangs deploy workers.
- **GraphQL errors swallowed** — GitHubProvider graphql calls don't check `$json['errors']` (200 status with errors in body).

### Performance

- **Image srcset missing** — `packages/media-library/src/Models/CuratorMedia.php:45` — `getSrcset()` returns `''`. Every public image served at full resolution.
- **Search uses LIKE %query%** — `packages/search/src/Drivers/DatabaseSearch.php:62` — no FULLTEXT, no site/language/status filter (also cross-tenant leak risk).
- **Blog archive uncached on paginated branch** — `packages/blog/src/Support/PageArchiveService.php:34` — `whereHas` + `groupByRaw` + `orderByRaw COALESCE(...)`.
- **html-cache invalidation serial** — `packages/html-cache/src/Actions/MarkAllCachedUrlsStaleAction.php:29` — per-row UPDATE in loop; 100k-URL site times out.
- **Newsletter requeue serial** — `packages/newsletter/src/Actions/RequeueDueProviderSyncAttemptsAction.php:30` — `each()` per row UPDATE + dispatch.
- **Beacon wasted lookup** — `packages/frontend-authoring/src/Http/Controllers/BeaconController.php:27` — does PageUrl lookup for every anon request that returns 1-line CSRF token.

### Database

- **Multi-table sync ALTER risk** — `packages/publishing-studio/database/migrations/2026_05_10_190866_08_z_add_workspace_columns_to_core_tables.php` — synchronous ALTER on 11 core tables + unique-key swap on `translations`. Long blocking op on populated DB.
- **Missing FK constraints** — site_id `->index()` without `->constrained()` across: email-studio tables, search.search_logs, insights.insights_events, insights.insights_visits.
- **XML billion-laughs risk** — `packages/wordpress-importer/src/Services/WxrReader.php:25` and `packages/migration-assistant/src/Services/Import/XmlReader.php` — `simplexml_load_file` without DOCTYPE rejection.
- **Tables likely tenant-scoped but missing site_id** — notes, command_palette_runs, frontend_render_profiles, deployment_connections.

### Settings migration registration

CLAUDE.md mandates settings migrations registered in `InstallCommand`. Audit shows inconsistent strategy:

- seo-suite: canonical correct pattern.
- publishing-studio: InstallCommand exists but doesn't include settings file.
- agent-bridge, password-policy, login-audit, search, welcome-tour: no InstallCommand at all.
- foundation-theme, insights, ga4-reports: use `SettingsMigrationProvider` auto-discovery instead.

Action: standardise on one path; document in CLAUDE.md.

### Frontend a11y

- `foundation-theme/resources/views/components/footer/index.blade.php:70` — `<a href="javascript:void(0)">` scroll-to-top with no handler.
- `foundation-theme/resources/views/components/content.blade.php:140` — `role="button" tabindex=0` media without keyboard handler.
- `public-actions/resources/views/action.blade.php:37` — form errors not wired via `aria-describedby` / `aria-invalid`.
- `access-gate/resources/views/request.blade.php:209` — same form a11y gap.
- `foundation-theme/resources/views/components/header/navigation.blade.php:347` — dark-mode toggle missing accessible name on desktop.
- All 3 brand themes (`theme-agency`, `theme-corporate`, `theme-saas`) — no mobile menu disclosure, no skip link, `<h3>` columns without preceding `<h2>`.
- No `prefers-reduced-motion` anywhere in frontend templates.
- `role="menu"` misuse on `<ul>` of `<a>` links across foundation-theme components.
- `target="_blank"` links missing `rel="noopener"` in footer/social-links + team-members blocks.

### Editor UX (Filament)

- `packages/layout-builder/src/Filament/Configurators/Blocks/Modern*Configurator.php` — 12 files, 76 hardcoded `->label()`, 133 hardcoded `placeholder/helperText/description`. Violates `__('capell-...')` non-negotiable.
- `packages/seo-suite/src/Filament/Actions/AiCreatorAction.php:36` — entire AI Creator wizard English-only.
- Static `$title` (Filament v4 ignores in some contexts):
    - `diagnostics/src/Filament/Pages/SystemHealthPage.php:24`
    - `diagnostics/src/Filament/Pages/PermissionAuditPage.php:28`
    - `diagnostics/src/Filament/Pages/QueueHealthPage.php:30`
    - `publishing-studio/src/Filament/Pages/ActivityTrailPage.php:28`
- 21 fields flat in `events/src/Filament/Resources/Events/Schemas/EventForm.php` — no `Section::make()` grouping. Same in `campaign-studio` CTA block form (27 fields).
- 34 Filament tables ship no `emptyStateHeading/Description/Actions`.

### Senior engineering

- **Duplicated demo-creator** — `packages/demo-kit/src/Support/Creator/{Base,Standard,Modern}DemoBlockCreator.php` vs `packages/layout-builder/src/Support/Creator/` — ~2,600 LOC duplicated and already diverged.
- **God classes** — `packages/layout-builder/src/Livewire/Filament/Concerns/ManagesAssets.php` (1,306 LOC, 55 methods) and `LayoutBuilderActionFactory.php` (1,182 LOC, 39 methods).
- **CopyOnWriteAction violates Action contract** — `packages/publishing-studio/src/Actions/CopyOnWriteAction.php:45` — no `handle()`, exposes `cloneForEdit()/cloneForDelete()/clearShadow()` instead. Split into 3 actions.
- **Cross-plugin imports lack contracts** — `PublishingStudio\Models\Workspace` imported in 28+ call-sites across 7 packages with no `Contracts/Workspace` interface.
- **91 hardcoded English Filament labels** across diagnostics, campaign-studio, layout-builder.
- **$guarded = [] on 21 models** — not a leak in current code paths, but one `Model::create($request->all())` away.

### Test coverage

- ga4-reports: zero `*ActionTest.php`. 8 actions untested.
- html-cache: invalidation actions (Mark/RefreshCachedUrl) untested.
- newsletter: unsubscribe-token, double-opt-in, webhook-handler actions untested.
- layout-builder: 13 of 44 Actions have tests; Livewire surface barely covered.
- campaign-studio: 3 of 15 Actions tested.
- ~30 new `*CoverageTest.php` / `*ResidualCoverageTest.php` files in git status are coverage padding (assert `->not->toBeEmpty()`, `instanceof`, hard-coded option keys). Hits 80% gate, catches no regressions.

### Documentation / release-readiness

- 0/45 packages have `CHANGELOG.md`.
- 0/45 composer.json have `support` block.
- 21+/45 missing `homepage`, `keywords`, `authors`.
- Version constraint inconsistency: `*` vs `^4.0` vs `self.version` for capell-app/\* internal deps.
- `packages/api/README.md` is 7 lines.
- `packages/block-library/composer.json` name is `capell-app/content-blocks` (folder/composer name divergence).
- `packages/media-library/composer.json` has `"type": "path"` (looks like local-dev artifact).
- Cross-package dependency ordering not documented: blog requires layout-builder, themes require foundation-theme + layout-builder, wordpress-importer requires migration-assistant.

### Content / copy

- AI-ish wording in demo seed content: `demo-kit/src/Support/Creator/StandardDemoBlockCreator.php:454` "Empower Your Vision" / "unlock new opportunities for success"; `BaseDemoCreator.php:822` "leverage cutting-edge technology to create innovative solutions"; duplicated across layout-builder Creator copies.
- `blog/resources/lang/en/generic.php:14,18` — public copy "Discover our latest articles..." / "stay updated with new insights" (mild filler).
- Empty lang files: `blog/lang/en/{form,table,messages,navigation}.php` all `return [];`; `content-sections/lang/en/section.php` empty despite 18 section configurators.
- Blog article form: no excerpt field, no status UX (visible_from datetime is the only publish gate, no labelled draft/published/scheduled toggle), no helperText on SEO priority/meta_tags.
- Duplicate `->helperText()` bug in `ModernHeroBannerConfigurator.php:117-120` on `backgroundGradient` TextInput — second call silently overrides first.
- Naming collision: layout-builder ships "Modern Hero / FAQ / CTA / Pricing / Testimonials / Stats" blocks AND content-sections ships same-named section types. Editor sees two "Hero" options with no distinguishing label.

## Suggested fix order

1. **Cross-site Filament leaks** — apply SiteScope to the 13 Resources. P1, fastest blast-radius reduction.
2. **Migration multi-table sync ALTER + settings registration audit** — production rollback risk. Verify the `dropForeignKey` finding (false positive on inspection) and review all flagged migrations in person.
3. **Beacon Origin check + workspace cookie HMAC** — auth surface.
4. **public-actions payload schema enforcement** — stored XSS.
5. **Kit webhook HMAC + webhook idempotency** — consent integrity.
6. **OAuth token redaction in deployments** — secret exposure.
7. **6 missing Policy classes** — close the authorization gap.
8. **media-library srcset implementation** — biggest LCP win.
9. **Modern\* block translations + content-sections naming collision** — editor UX.
10. **Form a11y on public-actions + access-gate** — aria-describedby + aria-invalid.
11. **html-cache + newsletter bulk-action batching** — operational scale.
12. **Per-package CHANGELOGs + composer support metadata** — release readiness.

## Ready-to-dispatch fix batches

Six worktree-isolated agent batches were prepared with detailed prompts. They blocked on org usage limit but can be re-dispatched as-is when limit resets. Each batch is independent and works in its own git worktree under `.claude/worktrees/`.

### WT1 — Security P1s

Scope: migration typo verification, OAuth log redaction, beacon CSRF/Origin check, public-actions payload schema, XML billion-laughs (wordpress-importer + migration-assistant), DeploymentConnection mass-assignment, agent-bridge per-site policy, agent-bridge token HMAC dual-verify.

### WT2 — Multi-tenancy + Policies

Scope: SiteScope on 13 Filament Resources, insights/search widget siteId plumbing, 6 Policy classes (events, campaign-studio, blog, tags, content-sections, address) with permission abilities, workspace cookie HMAC signing.

### WT3 — Performance + API integration

Scope: gitops HTTP timeouts + retries, Kit/Mailchimp/CampaignMonitor HMAC, newsletter webhook idempotency table, beacon anon short-circuit, search FULLTEXT + site filter, blog archive cache, html-cache batching, newsletter requeue chunking, media-library srcset (choose simplest non-invasive option), email-studio/search/insights FK constraints (new migrations).

### WT4 — A11y + frontend UX

Scope: scroll-to-top button, article hero keyboard handler, form aria wiring (public-actions, access-gate), dark-mode toggle aria-label, rel=noopener on external links, events calendar grid semantics, focus-visible across buttons + form-builder, role=menu cleanup, language flag alt="", prefers-reduced-motion global reset, 3 themes mobile nav + skip link, theme footer heading order, redundant tabindex removal.

### WT5 — Copy + translations

Scope: 12 Modern\* configurators routed through `__('capell-layout-builder::blocks.modern.*')` + new `packages/layout-builder/lang/en/blocks.php`, blog SettingsTab helperText, blog RelatedBlockConfigurator labels, seo-suite AiCreatorAction translations, 4 static $title → getTitle(), diagnostics + campaign-studio hardcoded labels, backed enum HasLabel additions, events EventForm + campaign-studio CTA form sectioning, AI-ish demo content rewrites in demo-kit + layout-builder Creator copies + blog generic.php.

### WT6 — Docs + composer metadata

Scope: 45 CHANGELOG.md files, composer.json sweep (support/authors/homepage/keywords/version constraints), block-library composer name decision, media-library composer type fix, README polish for 15 weak packages, cross-package install ordering notes.

## Verification policy

Each batch ends with `git commit`. After all 6 land, consolidate into a single branch (rebase or merge), then run `composer test` + `composer preflight` on the consolidated tree. Failures attribute to the latest batch; bisect by worktree if needed.

## Residual risk / untested areas

- Did not run live beacon flow under cross-origin conditions (recommended browser test).
- Did not enumerate each newsletter adapter's `verifyWebhook` (only Kit confirmed vulnerable).
- `Capell\Frontend\Support\Security\PublicHtmlSafetyInspector` in sibling repo, not audited.
- Did not run EXPLAIN on suspected slow queries.
- Did not exercise every Filament Resource under different role contexts.
- Did not verify each `agent-bridge` capability's `policyAbility` registration.

## Agent dispatch prompts (saved for re-run)

The full prompts for the 6 worktree agents are preserved in the multi-perspective-review conversation transcript at agentIds:

- WT1 (Security): `a45d7151bad3d1d81`
- WT2 (Multi-tenancy): `a92feb1bce0927c0b`
- WT3 (Perf + API): `a9df91949654e9d6d`
- WT4 (A11y): `a1e01206739c5ff68`
- WT5 (Copy): `ae49e08ea364b3154`
- WT6 (Docs): `a062c76d9970309cb`

All exited with: "You've hit your org's monthly usage limit". Re-dispatch the same prompts when usage resets.
