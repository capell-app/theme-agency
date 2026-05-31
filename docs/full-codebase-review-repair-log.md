# Full Codebase Review And Repair Log

This log tracks the multi-expert review loop for Capell core and all extension/theme packages.

## Expert Loop

Each extension/theme batch is reviewed through these lenses before being marked complete:

- Security/Auth: permissions, policies, signed URLs, validation, unsafe defaults, external calls.
- Laravel/Filament Architecture: Actions/Data boundaries, service providers, resources, pages, forms, tables, bulk actions.
- Public Output/Performance: anonymous-safe Blade output, no editor leakage, no public-view queries/lazy loading, caching and N+1 risks.
- Data/Operations: migrations, indexes, transactions, queues, jobs, schedulers, imports/exports, failure paths.
- QA/Tests: meaningful direct Action tests, edge cases, regression coverage, focused package validation.

## Coverage Checklist

- [x] Repository and sibling core package inventory mapped.
- [x] Dirty tree checked. Existing untracked marketplace images and sibling core hotfix edits preserved.
- [ ] Integration/security-heavy extensions reviewed.
- [ ] Content/admin/domain extensions reviewed.
- [ ] Frontend/public-output packages reviewed.
- [ ] Theme packages reviewed.
- [ ] Core Capell packages reviewed without overwriting existing user changes.
- [ ] Full changed-area validation rerun after repairs.
- [ ] Broad validation rerun or blockers documented.

## Confirmed Issues And Fixes

### Integration HTTP Timeouts

Status: fixed, focused tests passing.

Affected packages:

- deployments
- ga4-reports
- seo-suite
- shopify-commerce

Issue:
External OAuth/API/download calls relied on framework defaults instead of package-level explicit timeouts, making failure paths less predictable and harder to tune operationally.

Fix:
Added package timeout config and applied it to OAuth token/user requests, GA4 token/report calls, Search Console token/query calls, Shopify OAuth/GraphQL/bulk download calls. Added focused assertions that configured timeouts are sent.

Additional improvement found during validation:
GA4 and Search Console service-account clients were requesting a fresh OAuth token for each API query in the same client instance. Added per-instance token caching with an expiry buffer.

Validation:

- `vendor/bin/pest packages/deployments/tests/Feature/OAuth/OAuthControllersTest.php --configuration=phpunit.xml` passed.
- `vendor/bin/pest packages/shopify-commerce/tests/Unit/Actions/SyncShopifyProductsActionTest.php packages/shopify-commerce/tests/Unit/Actions/ExchangeShopifyAuthorizationCodeActionTest.php --configuration=phpunit.xml` passed.
- `vendor/bin/pest packages/ga4-reports/tests/Unit/Support/GA4ReportsDataClientTest.php --configuration=phpunit.xml` passed.
- `vendor/bin/pest packages/seo-suite/tests/Unit/SearchConsole/GoogleSearchConsoleClientTest.php --configuration=phpunit.xml` passed.

### Hero Public Render Lazy Loading

Status: fixed, focused tests passing.

Affected package:

- hero

Issue:
`HeroAssetSlideData` could lazy-load optional `linkedPage`, `pageUrl`, and media relations when called outside the normal layout preloader path. That is fragile for public rendering and conflicts with the public Blade no-query/no-lazy-load rule.

Fix:
Restricted the data object to already-loaded relations for optional public render data and added a regression test with lazy loading prevention enabled.

Validation:

- `vendor/bin/pest packages/hero/tests/Feature/HeroAssetSlideDataTest.php packages/hero/tests/Feature/HeroSetupCommandTest.php --configuration=phpunit.xml` passed.

### Hero Test Static Analysis

Status: fixed.

Affected package:

- hero

Issue:
PHPStan baseline run failed on `packages/hero/tests/Feature/HeroSetupCommandTest.php:91` because a plain `expect()` call could not resolve a template type.

Fix:
Matched surrounding test style by using `capell_expect()`.

Validation:

- Focused hero test file passed.

### Layout Builder Action Boundary

Status: fixed, focused tests passing.

Affected package:

- layout-builder

Issue:
`LayoutBuilderActionFactory` lived under a `Livewire\Filament\Actions` namespace even though it is a UI factory, not a domain Action or Filament Action class. The architecture test correctly flagged it as a boundary violation.

Fix:
Moved the class to `Livewire\Filament\Support` and updated imports/tests.

Validation:

- `vendor/bin/pest tests/Packages/Arch/PageAndActionCoverageTest.php packages/layout-builder/tests/Unit/Livewire/LayoutBuilderActionFactoryCoverageTest.php --configuration=phpunit.xml` passed.

### Workspace Publish Authorization

Status: fixed, focused tests passing.

Affected packages:

- content-sections
- publishing-studio

Issue:
Content section workspace publishing and scheduled-publishing retry/cancel actions used status checks without an action-level publish authorization gate.

Fix:
Added `Gate::authorize('publish', $workspace)` to the content section publish callback, hid the action when the actor cannot publish the workspace, guarded scheduled retry/cancel visibility and callbacks, and allowed release managers to manage scheduled workspaces through the workspace publish policy.

Validation:

- `vendor/bin/pest packages/content-sections/tests/Feature/Filament/Resources/Section/EditSectionPublishingActionsTest.php --configuration=phpunit.xml` passed.
- `vendor/bin/pest packages/publishing-studio/tests/Admin/Feature/Filament/Pages/ScheduledPublishingPageTest.php packages/publishing-studio/tests/Feature/Filament/Resources/PublishingStudio/WorkspacePolicyTest.php --configuration=phpunit.xml` passed.

### Shopify Site Selection Tampering

Status: fixed, focused tests passing.

Affected package:

- shopify-commerce

Issue:
The connection page used a public Livewire `selectedSiteId` directly for sync/search/disconnect connection lookup. A site-scoped actor could tamper that property and target another site's Shopify connection.

Fix:
Normalized and rejected unauthorized site selections before connection lookup. Added a regression test using a site-scoped actor.

Validation:

- `vendor/bin/pest packages/shopify-commerce/tests/Feature/Filament/ShopifyConnectionPageTest.php --configuration=phpunit.xml` passed.

### Deployment OAuth Connection Failures

Status: fixed, focused tests passing.

Affected package:

- deployments

Issue:
OAuth callback controllers handled provider error responses but not HTTP connection/timeout exceptions, which could turn upstream outages into 500s.

Fix:
Caught `ConnectionException` around token and user requests, logged redacted provider/stage context, and returned the existing safe OAuth failure paths.

Validation:

- `vendor/bin/pest packages/deployments/tests/Feature/OAuth/OAuthControllersTest.php --configuration=phpunit.xml` passed.

### Login Audit Resource Policy

Status: fixed, focused tests passing.

Affected package:

- login-audit

Issue:
The login audit Filament resource extended the vendor authentication-log resource without an explicit policy, exposing sensitive audit records under Filament's no-policy fallback in non-strict environments.

Fix:
Added and registered `LoginAuditPolicy` requiring Shield `ViewAny:LoginAudit`/`View:LoginAudit` permission or a global admin actor.

Validation:

- `vendor/bin/pest packages/login-audit/tests/Feature/Filament/LoginAuditsTableTest.php --configuration=phpunit.xml` passed.

### Frontend Authoring Beacon Resilience

Status: fixed, focused tests passing.

Affected packages:

- foundation-theme
- frontend-authoring

Issue:
The all-packages frontend smoke test exposed two public-rendering failures: Foundation Theme did not provide a complete `capell::app` override/body after component namespace precedence changed, and the authoring beacon assumed the optional html-cache table existed.

Fix:
Registered the class-backed Foundation layout components, added a Foundation `capell::app` view override that renders the body and beacon payload, and guarded authoring banner cache lookup with `Schema::hasTable()`. Updated the all-packages beacon test to use a same-origin request and a test admin access checker.

Validation:

- `vendor/bin/pest tests/Packages/AllPackages/FrontendWebsiteAccessTest.php --configuration=phpunit.xml` passed.
- `vendor/bin/pest packages/frontend-authoring/tests/Feature/BeaconControllerTest.php --configuration=phpunit.xml` passed.

### Newsletter Admin Authorization And Import Safety

Status: fixed, package tests passing.

Affected package:

- newsletter

Issues:
Newsletter Filament resources did not have explicit policies, provider credentials could be exposed back into edit form state, import/export header actions needed stronger site authorization, provider sync retry requeues could double-dispatch the same attempt under concurrent runners, and CSV import/export paths had unbounded row/result materialization.

Fix:
Added explicit resource policies and registered them, added site authorization guards for import/export action payloads, cleared credential state before fill while preserving existing encrypted credentials on blank save, made retry requeue claim attempts with a conditional update before dispatch, changed subscriber exports to lazy cursors, and added configurable import file/row limits.

Validation:

- `vendor/bin/pest packages/newsletter/tests --configuration=phpunit.xml` passed.

### Password Policy User Action Authorization

Status: fixed, package tests passing.

Affected package:

- password-policy

Issue:
The force-password-change table action could be invoked directly without checking record-level user update authorization.

Fix:
Guarded action visibility and callback execution through the Capell user resource policy and any registered user model policy, while preserving no-policy harness behavior.

Validation:

- `vendor/bin/pest packages/password-policy/tests --configuration=phpunit.xml` passed.

### Insights Event Sequencing

Status: fixed, focused tests passing.

Affected package:

- insights

Issue:
Event sequence assignment read the current visit event count and inserted the next sequence outside a transaction/lock, allowing duplicate sequence numbers under concurrent beacons.

Fix:
Wrapped visit resolution, consent checks, sequence allocation, insert, and `last_seen_at` update in a transaction and locked the visit row before counting events.

Validation:

- `vendor/bin/pest packages/insights/tests/Feature/Events/InsightsBeaconControllerTest.php packages/insights/tests/Unit/Actions/RecordInsightsEventActionTest.php --configuration=phpunit.xml` passed.

### GA4 Sync Concurrency

Status: fixed, package tests passing.

Affected package:

- ga4-reports

Issue:
Manual/scheduled GA4 syncs could overlap for the same property/window, and the scheduler was not single-server guarded.

Fix:
Added a cache lock around sync execution, returned an `already_running` result when the lock is held, and added `withoutOverlapping(...)->onOneServer()` to the scheduled command.

Validation:

- `vendor/bin/pest packages/ga4-reports/tests --configuration=phpunit.xml` passed.

### Shopify Bulk Product Import Streaming

Status: fixed, focused tests passing.

Affected package:

- shopify-commerce

Issue:
The Shopify bulk product importer downloaded and decoded the full bulk JSONL file into memory and tracked all seen product IDs for stale pruning.

Fix:
Streamed the bulk download to a temporary file, yielded JSONL products line by line, stamped imported rows with a sync timestamp, and pruned stale products by timestamp instead of a full in-memory ID list.

Validation:

- `vendor/bin/pest packages/shopify-commerce/tests/Unit/Actions/SyncShopifyProductsActionTest.php --configuration=phpunit.xml` passed.

### Search Dashboard Query Bounds

Status: fixed, package tests passing.

Affected package:

- search

Issue:
Trending search insights loaded all grouped current terms and all previous-window counts before applying the dashboard limit.

Fix:
Added non-positive limit guards, bounded the current candidate set with `dashboard.trending_candidate_limit`, and restricted previous-window count lookup to those candidates.

Validation:

- `vendor/bin/pest packages/search/tests --configuration=phpunit.xml` passed.

### SEO Suite Scheduled PageSpeed Guard

Status: fixed, focused tests passing.

Affected package:

- seo-suite

Issue:
The scheduled PageSpeed digest audit used overlap protection on a single scheduler process but was not guarded for multi-server schedulers.

Fix:
Added `onOneServer()` to the scheduled audit event and covered the registered event with a provider test.

Validation:

- `vendor/bin/pest packages/seo-suite/tests/Unit/Providers/SeoSuiteAutoloadTest.php --configuration=phpunit.xml` passed.

### SEO Suite Website Schema Public Blade Query

Status: fixed, package tests passing.

Affected package:

- seo-suite

Issue:
The public website schema Blade component looked up the search results page directly from the template, putting a cache-miss database query path in public Blade.

Fix:
Moved website schema preparation into a view composer, kept the Blade component as JSON-LD output only, and added a regression test forbidding data loading in that template.

Validation:

- `vendor/bin/pest packages/seo-suite/tests/Feature/Site/SiteMetaSchemaTest.php --configuration=phpunit.xml` passed.
- `vendor/bin/pest packages/seo-suite/tests --configuration=phpunit.xml` passed.

### Migration Assistant Fingerprint Scan

Status: fixed, focused tests passing.

Affected package:

- migration-assistant

Issue:
Fingerprint relation matching selected and hydrated all candidate layouts/blueprints before comparing fingerprints.

Fix:
Selected only key/schema columns and iterated with a cursor ordered by key, allowing the resolver to stop once the first match is found without materializing the full candidate set.

Validation:

- `vendor/bin/pest packages/migration-assistant/tests/Integration/MigrationAssistant/FingerprintMatchResolverTest.php --configuration=phpunit.xml` passed.

### Hero Public Output Namespace Leak

Status: fixed, package tests passing.

Affected package:

- hero

Issue:
Anonymous public hero markup exposed package-specific hooks (`capell-hero-*`, `data-capell-hero-video`, and `--capell-hero-*` CSS variables).

Fix:
Renamed public presentation hooks to neutral hero selectors/data attributes/CSS variables and added public view assertions that rendered output does not expose the package namespace.

Validation:

- `vendor/bin/pest packages/hero/tests --configuration=phpunit.xml` passed.

### Hero Public Blade Relation Boundary

Status: fixed, package tests passing.

Affected package:

- hero

Issue:
Hero public Blade dereferenced slide asset/page relations directly (`translation`, `related`, `pageUrl`, `image`, and asset metadata). If render data was incomplete, public rendering could lazy-load or execute relationship queries from the template.

Fix:
Moved slide-facing values into `HeroAssetSlideData`, made slide data resolve only loaded relations, changed hero and related Blade views to consume prepared scalar/relation data, and guarded the component mount path against lazy-loading the current page translation.

Validation:

- `vendor/bin/pest packages/hero/tests/Feature/HeroAssetSlideDataTest.php --configuration=phpunit.xml` passed.
- `vendor/bin/pest packages/hero/tests --configuration=phpunit.xml` passed.

### Core Marketplace Operation Authorization

Status: fixed in sibling core package, focused tests passing.

Affected sibling package:

- `../capell-4/packages/marketplace`

Issue:
Marketplace package operation mutations in Livewire pages/widgets were reachable with extension page view access and did not require the manage-extensions permission.

Fix:
Added action-level manage permission guards to retry/cancel/resolve/diagnostics page methods and dashboard alert retry/dismiss methods, and hid mutating page actions for users without manage permission.

Validation:

- In `../capell-4`: `vendor/bin/pest packages/marketplace/tests/Feature/Filament/MarketplacePackageOperationsPageTest.php packages/marketplace/tests/Feature/Filament/MarketplaceInstallOperationsWidgetTest.php --configuration=phpunit.xml` passed.

### Foundation/Theme Public Output Safety

Status: fixed, package tests passing.

Affected packages:

- foundation-theme
- theme packages using foundation public wrappers

Issue:
Anonymous public theme output exposed package/admin-identifying markers such as `data-theme-key`, `capell-theme-*`, `capell-foundation-theme-*`, and `capell-app-body`.

Fix:
Removed package-identifying selectors/data attributes from public theme markup and CSS while preserving presentation behavior.

Follow-up during public Blade review:
Removed public Blade relation resolution from Foundation pages and modern asset blocks. The views now read only already-loaded relation arrays instead of calling parent loaders or touching `WidgetAsset` asset/translation relations directly, preserving anonymous-safe cached output and avoiding public-view lazy queries.

Second follow-up from the theme sidecar loop:
Moved related-site loading and navigation block rendering preparation out of public Blade into component classes. The Blade safety test now also forbids public-template loader calls such as `NavigationLoader::`, `PageLoader::`, and `SiteLoader::`.

Validation:

- `vendor/bin/pest packages/foundation-theme/tests/Feature/SafeOutputTest.php --configuration=phpunit.xml` passed.
- `vendor/bin/pest packages/foundation-theme/tests/Feature/SafeOutputTest.php packages/foundation-theme/tests/Unit/FoundationThemeBoundaryTest.php packages/foundation-theme/tests/Unit/SidebarPageBlockStyleTest.php --configuration=phpunit.xml --filter='data-loading|preloaded relations|asset page block view'` passed.
- `vendor/bin/pest packages/foundation-theme/tests/Feature/SafeOutputTest.php packages/foundation-theme/tests/Unit/FoundationThemeFinalCoverageTest.php --configuration=phpunit.xml --filter='data loading|navigation|related-sites|chrome'` passed.
- `vendor/bin/pest packages/foundation-theme/tests --configuration=phpunit.xml` passed.

### Layout Builder Optional Package Boundaries

Status: fixed by layout-builder worker, package tests passing.

Affected package:

- layout-builder

Issue:
Layout Builder support classes had hard imports to optional companion packages, making package discovery and isolated installs fragile.

Fix:
Replaced optional package imports with class-string checks and boundary-safe lookups, and added architecture regression coverage.

Validation:

- Worker validation: `vendor/bin/pest packages/layout-builder/tests --configuration=phpunit.xml` passed.

### Document Lifecycle Resource Policy

Status: fixed, focused tests passing.

Affected package:

- document-lifecycle

Issue:
The `DocumentResource` model had no registered policy, so resource authorization depended on broad panel access instead of document lifecycle permissions.

Fix:
Added `DocumentPolicy`, registered it in the package service provider, and covered denied ordinary users plus permitted resource capability checks.

Validation:

- `vendor/bin/pest packages/document-lifecycle/tests/Feature/DocumentLifecycleAdminTest.php --configuration=phpunit.xml` passed.
- `vendor/bin/pest packages/document-lifecycle/tests --configuration=phpunit.xml` passed.

### Public Actions Site-Scoped Admin Surfaces

Status: fixed, package tests passing.

Affected package:

- public-actions

Issue:
Integration tokens, destinations, and dispatch attempts were not consistently scoped to the current actor's assigned sites; token creation also accepted arbitrary site IDs.

Fix:
Scoped resource queries through `SiteScope`, enforced create authorization and site access in the token creation action, and tightened policy site extraction across related records.

Validation:

- `vendor/bin/pest packages/public-actions/tests --configuration=phpunit.xml` passed.

### Comments Author Moderation Authorization

Status: fixed, focused tests passing.

Affected package:

- comments

Issue:
Comment author moderation actions mutated author trust/verification state without checking `CommentAuthorPolicy::update`.

Fix:
Added visible and runtime `Gate::authorize('update', $record)` guards to author moderation actions and covered denied/allowed mutation paths.

Validation:

- `vendor/bin/pest packages/comments/tests/Integration/Filament/CommentsAdminSurfaceTest.php --configuration=phpunit.xml` passed.
- `vendor/bin/pest packages/comments/tests --configuration=phpunit.xml` passed.

### HTML Cache Maintenance Site Scope

Status: fixed, package tests passing.

Affected package:

- html-cache

Issue:
The maintenance cache page listed all sites and direct Livewire methods accepted arbitrary site IDs for toggle/generate operations.

Fix:
Scoped listed sites to the current actor, authorized direct site mutations against `SiteScope::actorCanUseSite`, made bulk maintenance generation operate only on accessible enabled sites, and restricted global down/up maintenance actions to global actors.

Validation:

- `vendor/bin/pest packages/html-cache/tests/Unit/HtmlCacheStaticAndNotificationCoverageTest.php --configuration=phpunit.xml --filter='maintenance cache page|toggles and generates site maintenance cache state|scopes maintenance cache page site operations'` passed.
- `vendor/bin/pest packages/html-cache/tests --configuration=phpunit.xml` passed.

### Access Gate Admin Site Scope

Status: fixed, package tests passing.

Affected package:

- access-gate

Issue:
Access Gate child admin resources listed and mutated registrations, grants, claim tokens, browser tokens, and events across all sites; the shared policy checked only broad Shield permissions, not the record's access area site.

Fix:
Added an Access Gate site-scope helper, scoped child resource queries and area filter options through the related area site, restricted area creation site options to assigned sites, and made record-level policy methods require the actor to be able to use the record's area site.

Validation:

- `vendor/bin/pest packages/access-gate/tests/Unit/Policies/AccessGateResourcePolicyTest.php --configuration=phpunit.xml` passed.
- `vendor/bin/pest packages/access-gate/tests --configuration=phpunit.xml` passed.

### Access Gate Public Area Resolution

Status: fixed, package tests passing.

Affected package:

- access-gate

Issue:
Public request and logout controllers resolved access areas globally by key, bypassing the request-site filtering used by the middleware.

Fix:
Added a request-aware area resolver that applies the current site-domain context and allows only global areas or areas matching the request site; wired show, submit, and logout controllers through it.

Validation:

- `vendor/bin/pest packages/access-gate/tests/Feature/AccessGateMiddlewareTest.php --configuration=phpunit.xml --filter='current site context|another site access gate browser token'` passed.
- `vendor/bin/pest packages/access-gate/tests --configuration=phpunit.xml` passed.

### Agent Bridge Page Capabilities And Confirmation Replay

Status: fixed, package tests passing.

Affected package:

- agent-bridge

Issues:
Page update/disable/readiness capabilities accepted raw `page_id` values without checking the target page's site, and confirmation tokens were marked used only after execution, allowing concurrent reuse.

Fix:
Extracted shared Agent Bridge page/site authorization, applied it to preview and execute paths for page-ID capabilities, and atomically claimed confirmation tokens with a conditional `used_at` update before executing the capability.

Validation:

- `vendor/bin/pest packages/agent-bridge/tests/Unit/PageCapabilityActionsTest.php packages/agent-bridge/tests/Feature/PreviewConfirmWorkflowTest.php --configuration=phpunit.xml` passed.
- `vendor/bin/pest packages/agent-bridge/tests --configuration=phpunit.xml` passed.

### Navigation Raw ID Public Lookup Scope

Status: fixed, package tests passing.

Affected packages:

- navigation
- foundation-theme

Issue:
Public navigation blocks could resolve a stored `navigation_id` without current site or language constraints, allowing one site's published navigation to render on another site.

Fix:
Required site context for `NavigationLoader::getNavigationById()`, constrained the lookup to current-site or global navigations with compatible language, updated foundation navigation block callers, and added a cross-site loader regression test.

Validation:

- `vendor/bin/pest packages/navigation/tests/Integration/Loader/NavigationLoaderTest.php --configuration=phpunit.xml` passed.
- `vendor/bin/pest packages/foundation-theme/tests/Unit/FoundationThemeFinalCoverageTest.php --configuration=phpunit.xml --filter='navigation'` passed.
- `vendor/bin/pest packages/navigation/tests --configuration=phpunit.xml` passed.

### Address Admin Site Scope

Status: fixed, package tests passing.

Affected package:

- address

Issue:
The Site form address selector listed, searched, selected, and inline-edited all addresses, allowing site-scoped editors to see or mutate addresses tied to other sites.

Fix:
Added address site-scope support based on assigned sites, scoped address picker options/search/selected records and the Address resource query, and routed inline edit through the `AddressPolicy`.

Validation:

- `vendor/bin/pest packages/address/tests/Feature/Filament/Components/Forms/AddressSelectTest.php --configuration=phpunit.xml` passed.
- `vendor/bin/pest packages/address/tests --configuration=phpunit.xml` passed.

### Tags Site-Scoped Creation

Status: fixed, package tests passing.

Affected package:

- tags

Issue:
Tags created through taggable records were created as global tags, making site-scoped editor-created tags visible across sites.

Fix:
Added site-aware tag lookup/creation APIs and updated the Capell taggable trait to pass the owning record's `site_id`, while still reusing intentionally global tags when they already exist.

Validation:

- `vendor/bin/pest packages/tags/tests/Integration/Models/TagTest.php --configuration=phpunit.xml` passed.
- `vendor/bin/pest packages/tags/tests --configuration=phpunit.xml` passed.

### Campaign Studio Site-Scoped Dashboards, Forms, And CTA Blocks

Status: fixed, package tests passing.

Affected package:

- campaign-studio

Issues:
Dashboard stats/top lists aggregated across all sites, campaign relationship selects exposed cross-site records, and public CTA block hydration loaded raw CTA IDs without current-site or active-state constraints.

Fix:
Applied `SiteScope` to dashboard group/conversion/landing page queries and relationship option queries, changed CTA block configuration to a scoped active select, and constrained public CTA hydration to active current-site or global CTA blocks.

Validation:

- `vendor/bin/pest packages/campaign-studio/tests/Unit/Actions/CampaignDashboardActionsTest.php packages/campaign-studio/tests/Unit/View/Components/CampaignCtaBlockTest.php --configuration=phpunit.xml` passed.
- `vendor/bin/pest packages/campaign-studio/tests --configuration=phpunit.xml` passed.

### Events Notification Idempotency

Status: fixed, package tests passing.

Affected package:

- events

Issue:
Registration notification logs used check-then-insert idempotency without a database uniqueness guarantee, so concurrent send/schedule workers could create duplicate queued logs and duplicate notifications.

Fix:
Added a unique registration/type/email identity index for notification logs, changed send/schedule paths to `createOrFirst()`, included reminder recipient email in the identity, and made the migration dedupe existing duplicate identities before adding the unique key.

Validation:

- `vendor/bin/pest packages/events/tests/Integration/EventReviewFindingsTest.php --configuration=phpunit.xml` passed.
- `vendor/bin/pest packages/events/tests --configuration=phpunit.xml` passed.

### Migration Assistant Import Execution Claims

Status: fixed, package tests passing.

Affected package:

- migration-assistant

Issue:
Page-import dispatch, failed-session retry, and queued import jobs could all rely on non-atomic status checks before queueing or executing work. Concurrent submits or stale queued jobs could therefore enqueue or run the same import more than once.

Fix:
Added a shared atomic session-claim action for status transitions, used it for `validated -> queued`, `failed -> queued`, and `queued -> running`, and added `WithoutOverlapping` job middleware for the same import session. Added focused regressions for duplicate wizard dispatch, stale retry models, and jobs waking after the session has already left the queued state.

Validation:

- `vendor/bin/pest packages/migration-assistant/tests/Admin/Feature/Actions/Imports/PageImportWorkflowActionsTest.php --configuration=phpunit.xml --filter='duplicate import jobs'` passed.
- `vendor/bin/pest packages/migration-assistant/tests/Feature/Admin/ImportSessions/Pages/ViewImportSessionTest.php --configuration=phpunit.xml --filter='duplicate retry jobs'` passed.
- `vendor/bin/pest packages/migration-assistant/tests/Integration/MigrationAssistant/ExecuteImportPlanJobTest.php --configuration=phpunit.xml --filter='no longer queued'` passed.
- `vendor/bin/pest packages/migration-assistant/tests --configuration=phpunit.xml` passed.

### Diagnostics Queue Operation Authorization

Status: fixed, package tests passing.

Affected package:

- diagnostics

Issue:
Queue-health retry, bulk retry, pending-job delete, and queue-monitor prune actions were gated by configuration but not by an operation-level permission, so read-only diagnostics viewers could reach mutating queue controls.

Fix:
Added a dedicated `Manage:QueueHealthPage` permission, kept super admins and `accessDiagnostics` as queue-operation managers, allowed the manage permission to access the page, and hid/authorized all mutating queue actions behind the manage check. Stabilized the scoped cache-health widget test fixture uncovered during full package validation.

Validation:

- `vendor/bin/pest packages/diagnostics/tests/Feature/Filament/Pages/QueueHealthPageTest.php --configuration=phpunit.xml --filter='queue management access|read-only diagnostics viewers|bulk retry'` passed.
- `vendor/bin/pest packages/diagnostics/tests/Feature/Actions/EnsureDiagnosticsPermissionsActionTest.php --configuration=phpunit.xml` passed.
- `vendor/bin/pest packages/diagnostics/tests/Feature/Filament/Widgets/CacheHealthWidgetTest.php --configuration=phpunit.xml --filter='unassigned sites|assigned sites|refreshes when'` passed.
- `vendor/bin/pest packages/diagnostics/tests --configuration=phpunit.xml` passed.

### Hero Outer Block Public Render Boundary

Status: fixed, package tests passing.

Affected package:

- hero

Issue:
The inner hero slide data had been hardened, but the outer public hero block view still prepared slide/background/media data from block relations in Blade. That kept public rendering coupled to hydrated Eloquent models and made the anonymous-safe public-output rule fragile.

Fix:
Moved outer hero render preparation into `Hero` and `HeroBlockRenderData`, made the public Blade consume hydrated scalar/data-object values, and normalized related hero items to prepared arrays before rendering. The public views no longer access raw block assets, block translations, block asset records, frontend state, or hero resolve actions.

Validation:

- `rg -n "\\$block->assets|\\$block->translation|\\$blockAsset|Frontend::|HeroAssetSlideData::|ResolveHero|loadMissing\\(|relationLoaded\\(|::query\\(|DB::" packages/hero/resources/views -S` returned no matches.
- `php -l packages/hero/src/Data/HeroBlockRenderData.php && php -l packages/hero/src/Data/HeroAssetSlideData.php && php -l packages/hero/src/View/Components/Block/Hero.php` passed.
- `vendor/bin/pest packages/hero/tests --configuration=phpunit.xml` passed.

### Deployments Connection Mutation Authorization

Status: fixed, package tests passing.

Affected package:

- deployments

Issue:
The deployment connection page used the same view permission for read access and mutating operations, so a user with `View:DeploymentConnectionPage` could disconnect active repository connections and complete OAuth connection callbacks.

Fix:
Added `Manage:DeploymentConnectionPage`, kept page visibility available to view-only users, hid connect/disconnect controls unless the actor can manage connections, and required the manage permission for OAuth callbacks and disconnect execution. Cleaned stale test imports that were producing validation warnings.

Validation:

- `vendor/bin/pest packages/deployments/tests/Feature/Filament/DeploymentConnectionPageTest.php --configuration=phpunit.xml --filter='mutation controls|disconnect active|limits access|oauth urls'` passed.
- `vendor/bin/pest packages/deployments/tests/Feature/OAuth/OAuthControllersTest.php --configuration=phpunit.xml --filter='forbids oauth|connects github|rejects github'` passed.
- `vendor/bin/pest packages/deployments/tests/Unit/Models/DeploymentConnectionTest.php packages/deployments/tests/Feature/Filament/DeploymentConnectionPageTest.php packages/deployments/tests/Feature/OAuth/OAuthControllersTest.php --configuration=phpunit.xml` passed.
- `vendor/bin/pest packages/deployments/tests --configuration=phpunit.xml` passed.

### Public Actions Idempotent Submission Race

Status: fixed, package tests passing.

Affected package:

- public-actions

Issue:
Public action submissions had a unique `(public_action_id, idempotency_key)` index, but submission handling still used a check-then-insert flow. Concurrent requests with the same idempotency key could therefore hit a duplicate-key exception instead of replaying the original submission.

Fix:
Changed keyed submissions to use Eloquent `createOrFirst()` so the database uniqueness constraint is the source of truth, while unkeyed submissions keep normal create semantics. Existing keyed submissions now return the stored result path without re-running the handler or creating another row.

Validation:

- `php -l packages/public-actions/src/Actions/SubmitPublicActionAction.php` passed.
- `vendor/bin/pest packages/public-actions/tests/Feature/PublicActionSubmitTest.php --configuration=phpunit.xml --filter='idempotent|duplicate submissions|already inserted'` passed.
- `vendor/bin/pest packages/public-actions/tests --configuration=phpunit.xml` passed.

### Content Sections Admin Site Scope And Inline Mutation Authorization

Status: fixed, package tests passing.

Affected package:

- content-sections

Issues:
Content Sections admin surfaces leaked cross-site records through resource/global search, selection tables, filters, parent options, and inline selects. Inline create/update and related-section clone flows also lacked enough site-scope and policy checks to prevent cross-site mutation/replication.

Fix:
Added `SectionSiteScope`, applied it to resource queries, global search, selection tables, site/parent filter options, `ContentSelect`, and `RelatedRepeater`, and enforced site/parent validity plus `Gate::authorize()` for inline update/replicate operations. `SectionPolicy` now delegates site checks through the same package-local scope helper.

Validation:

- `php -l` on changed Content Sections PHP files passed.
- `vendor/bin/pest packages/content-sections/tests/Unit/Filament/ContentSectionsAdminSurfaceCoverageTest.php --configuration=phpunit.xml` passed.
- `vendor/bin/pest packages/content-sections/tests/Feature/Filament/Resources/Section/Pages/EditSectionTest.php --configuration=phpunit.xml --filter='can save'` passed.
- `vendor/bin/pest packages/content-sections/tests --configuration=phpunit.xml` passed.

### Site Discovery Sitemap Generation Overlap

Status: fixed, package tests passing.

Affected package:

- site-discovery

Issue:
Sitemap regeneration could be triggered through admin tooling, lifecycle listeners, and queued action jobs without a per-site mutex. Overlapping runs for the same site could delete/rewrite sitemap files and state files at the same time, and a failed/blocked run could leave the admin generating counter stale.

Fix:
Added a per-site cache lock around `GenerateSitemapAction::handle()`, queued `WithoutOverlapping` middleware keyed by site, and moved the generating-counter cleanup into a `finally` path. Added coverage for middleware registration and held-lock cleanup, and removed stale global test imports that were polluting package validation with PHP warnings.

Validation:

- `php -l packages/site-discovery/src/Actions/GenerateSitemapAction.php && php -l packages/site-discovery/tests/Integration/Actions/GenerateSitemapActionTest.php` passed.
- `vendor/bin/pest packages/site-discovery/tests/Integration/Actions/GenerateSitemapActionTest.php --configuration=phpunit.xml` passed.
- `vendor/bin/pest packages/site-discovery/tests/Integration/Discovery/DiscoverPublicPagesActionTest.php packages/site-discovery/tests/Integration/Sitemap/SitemapGeneratorTest.php packages/site-discovery/tests/Unit/Sitemap/SitemapChainBuilderTest.php --configuration=phpunit.xml` passed.
- `vendor/bin/pest packages/site-discovery/tests --configuration=phpunit.xml` passed.

### Frontend Optimizer Critical CSS Idempotency

Status: fixed, package tests passing.

Affected package:

- frontend-optimizer

Issue:
Render profile persistence used `firstOrNew()->save()` around a unique hash, and public rendering could dispatch the same critical CSS generation job repeatedly while a profile had no generated CSS yet. Duplicate jobs could create redundant optimization runs and compete to update the same profile.

Fix:
Changed render profile creation to `createOrFirst()` while preserving existing generated critical CSS state, added a `queued` profile status claimed atomically before dispatch, avoided duplicate dispatches for queued/running profiles, and added `WithoutOverlapping` plus an already-generated no-op guard to the critical CSS job.

Validation:

- `php -l packages/frontend-optimizer/src/Actions/PersistRenderProfileAction.php && php -l packages/frontend-optimizer/src/Actions/PrepareRenderProfileAction.php && php -l packages/frontend-optimizer/src/Jobs/GenerateCriticalCssJob.php` passed.
- `vendor/bin/pest packages/frontend-optimizer/tests/Unit/Actions/PrepareRenderProfileActionTest.php packages/frontend-optimizer/tests/Unit/Actions/GenerateCriticalCssActionTest.php packages/frontend-optimizer/tests/Unit/Actions/PersistRenderProfileActionTest.php --configuration=phpunit.xml` passed.
- `vendor/bin/pest packages/frontend-optimizer/tests --configuration=phpunit.xml` passed.

### Publishing Studio Workspace Tamper Guards

Status: fixed by expert sidecar, package tests passing.

Affected package:

- publishing-studio

Issues:
Workspace publish/retry/cancel/admin Livewire flows had several direct invocation paths that needed stricter publish/manage authorization and current-site checks, especially where public Livewire state could be tampered before action execution.

Fix:
Tightened publishing workflow authorization and site-scope checks around mutation paths, including direct action execution, so visibility and runtime enforcement match.

Validation:

- Sidecar validation: focused publishing auth/Livewire files passed 35 tests.
- `vendor/bin/pest packages/publishing-studio/tests --configuration=phpunit.xml` passed.

### Theme Public Output Safety Batch

Status: fixed by expert sidecar, scoped package tests passing.

Affected packages:

- theme-agency
- theme-commerce
- theme-corporate
- theme-education
- theme-healthcare
- theme-knowledge
- theme-local-services
- theme-nonprofit
- theme-portfolio
- theme-saas

Issue:
The scoped theme pass found public wording/output safety gaps in Healthcare and Knowledge and insufficient regression coverage to prove anonymous output stays free of admin/editor/package markers.

Fix:
Adjusted public output copy/markup and strengthened `PublicOutputSafetyTest` coverage across the scoped theme packages.

Validation:

- Sidecar validation: PHP syntax checks on changed theme PHP files passed.
- Static scan for public authoring/admin tokens and public Blade data-loading patterns returned no matches in the scoped themes.
- Focused `PublicOutputSafetyTest` passed 20 tests / 250 assertions.
- Scoped theme package tests passed 131 tests / 948 assertions.

### Small Admin/Integration Package Batch

Status: fixed by expert sidecar, package tests passing.

Affected packages:

- media-ai
- newsletter
- notes
- search

Issues:
The small-package expert pass found provider sync retry race potential in Newsletter, loose purge-day validation in Search, and missing record-level update checks for Media AI and Notes header actions.

Fix:
Newsletter sync attempts now atomically claim pending work before running, `search:purge --days` rejects non-positive/non-integer values, Media AI doctor image actions require update authorization for the media record, and Notes create-note header actions require update authorization for the page.

Validation:

- `vendor/bin/pest packages/newsletter/tests --configuration=phpunit.xml` passed.
- `vendor/bin/pest packages/search/tests --configuration=phpunit.xml` passed.
- `vendor/bin/pest packages/media-ai/tests --configuration=phpunit.xml` passed.
- `vendor/bin/pest packages/notes/tests --configuration=phpunit.xml` passed.

### Blog Article Public Render Data Boundary

Status: fixed, package tests passing.

Affected package:

- blog

Issues:
The Blog public-render review found article/listing views drifting into model-derived render preparation and an unsafe `ArticleBlockRenderData::empty()` factory name. Because `ArticleBlockRenderData` extends Spatie Data, that method conflicts with Spatie's inherited `empty()` signature and hard-fataled when the article block class loaded.

Fix:
Kept article/listing render preparation in component/data classes, reverted the unstable tag-link DTO rewrite on article meta/footer tags, and renamed the article render-data empty factory to `blank()` to avoid the Spatie Data method collision.

Validation:

- `php -l` on changed Blog PHP files passed.
- Static scan for public-view query/lazy-load/loader calls in Blog and Demo Kit views returned no matches for the reviewed patterns.
- `vendor/bin/pest packages/blog/tests/Feature/Pages --configuration=phpunit.xml --colors=never` passed.
- `vendor/bin/pest packages/blog/tests/Unit packages/blog/tests/Arch --configuration=phpunit.xml --colors=never` passed.
- `vendor/bin/pest packages/blog/tests/Integration --configuration=phpunit.xml --colors=never` passed.
- `vendor/bin/pest packages/blog/tests/Feature/Filament --configuration=phpunit.xml --colors=never` passed.
- `vendor/bin/pest packages/blog/tests/Feature/MediaAttachmentTest.php packages/blog/tests/Feature/MediaListIntegrationTest.php packages/blog/tests/Feature/BlogStaticSiteExtensionTest.php packages/blog/tests/Feature/Actions --configuration=phpunit.xml --colors=never` passed.

### Demo Kit Public Render Data Boundary

Status: fixed, focused tests passing.

Affected package:

- demo-kit

Issue:
The Demo Kit page-content public Blade was reviewed alongside Blog because it prepared current-page render context directly in the template.

Fix:
Moved page-content view preparation behind `BuildDemoPageContentViewDataAction` / `DemoPageContentViewData`, keeping the public Blade focused on already-prepared output data.

Validation:

- `vendor/bin/pest packages/demo-kit/tests/Unit/Support/DemoCreatorTest.php --filter='demo page content|asset sections|content asset|page content' --configuration=phpunit.xml --colors=never` passed.

### Frontend Authoring Action Boundary

Status: fixed, package tests passing.

Affected package:

- frontend-authoring

Issues:
`UpdateEditableRegionAction` accepted a nullable user and skipped signed-region/manifest authorization when called directly without an actor. The public page-data Blade also touched frontend context variables it did not use.

Fix:
Made editable-region updates require an authenticated actor and always revalidate the signed region payload against the current manifest before saving. Updated direct Action tests to pass an actor and use registered editable fields. Removed unused `Frontend::*` calls from the public page-data view.

Validation:

- `php -l packages/frontend-authoring/src/Actions/UpdateEditableRegionAction.php && php -l packages/frontend-authoring/tests/Feature/EditableRegionEditingTest.php` passed.
- Static scan for public page-data authoring metadata and public-view data-loading patterns returned no matches.
- `vendor/bin/pest packages/frontend-authoring/tests/Feature/BeaconControllerTest.php packages/frontend-authoring/tests/Feature/EditableRegionEditingTest.php --configuration=phpunit.xml --colors=never` passed.
- `vendor/bin/pest packages/frontend-authoring/tests --configuration=phpunit.xml --colors=never` passed.

### Block Library Registry Safety Review

Status: reviewed, package tests passing.

Affected package:

- block-library

Review result:
Block Library already separates public and admin preview view references, rejects admin/Filament public views, validates provider contracts, and has focused registry/discovery tests. No high-confidence code changes were needed in this local pass.

Validation:

- `vendor/bin/pest packages/block-library/tests --configuration=phpunit.xml --colors=never` passed.

### API, Media Library, And WordPress Importer Batch

Status: fixed by expert sidecar, package tests passing.

Affected packages:

- api
- media-library
- wordpress-importer

Issues:
Signed API language resolution could reject another valid language on the resolved site when the site model had a partial `siteDomains` relation. Curator media dimensions could return null through an integer contract for non-image files. WordPress import registration prepended WXR handling ahead of the generic `.xml` reader, causing non-WXR XML files to fail instead of falling through to the Migration Assistant XML reader.

Fix:
API language checks now query the site's site-domain language IDs directly. Curator media width/height accessors return `0` for missing dimensions. WXR reader detects a real WordPress export before handling the file and delegates non-WXR XML to the generic XML reader.

Validation:

- `php -l` on changed API, Media Library, and WordPress Importer PHP files passed.
- `vendor/bin/pest packages/api/tests --configuration=phpunit.xml` passed.
- `vendor/bin/pest packages/media-library/tests --configuration=phpunit.xml` passed.
- `vendor/bin/pest packages/wordpress-importer/tests --configuration=phpunit.xml` passed.
- `vendor/bin/pest packages/api/tests packages/media-library/tests packages/wordpress-importer/tests --configuration=phpunit.xml --colors=never` passed.

### Package Architecture Allow-List Drift

Status: fixed, focused arch tests passing.

Affected tests:

- package architecture tests

Issue:
The Layout Builder interior boundary test still required a Foundation Theme Blade adapter file that no longer exists, so the guard failed before checking actual cross-package violations.

Fix:
Removed the stale allow-list entry while keeping the remaining explicit Foundation adapter exception.

Validation:

- `php -l tests/Packages/Arch/PackageInteriorBoundaryTest.php` passed.
- `vendor/bin/pest tests/Packages/Arch/PackageInteriorBoundaryTest.php tests/Packages/Arch/PageAndActionCoverageTest.php --configuration=phpunit.xml --colors=never` passed.

### Agent Delivery, AI Orchestrator, And Dashboard Reports Batch

Status: fixed by expert sidecar, package tests passing.

Affected packages:

- agent-delivery
- ai-orchestrator
- dashboard-reports

Issues:
Agent Delivery did not consider path-only wildcard site domains when an exact host site existed, and canonical/alternate URLs for those path-only domains could fall back to `localhost`/app URL instead of preserving the request host. Dashboard Reports publishing trend buckets used inclusive boundaries that could count a page twice at bucket edges.

Fix:
Agent Delivery now includes path-only wildcard candidates and carries the resolved host/scheme into generated canonical/alternate URLs. Dashboard publishing trend buckets now use half-open ranges, with only the final bucket including the range end.

AI Orchestrator review result:
Registry/action/integration surfaces had no high-confidence issue to fix in this pass.

Validation:

- `php -l` on changed Agent Delivery and Dashboard Reports PHP files passed.
- `vendor/bin/pest packages/agent-delivery/tests --configuration=phpunit.xml` passed.
- `vendor/bin/pest packages/ai-orchestrator/tests --configuration=phpunit.xml` passed.
- `vendor/bin/pest packages/dashboard-reports/tests --configuration=phpunit.xml` passed.
- `vendor/bin/pest packages/agent-delivery/tests packages/ai-orchestrator/tests packages/dashboard-reports/tests --configuration=phpunit.xml --colors=never` passed.

### Translation Manager, Form Builder, Email Studio, Filament Peek, And Welcome Tour Batch

Status: fixed by expert sidecar, package tests passing.

Affected packages:

- translation-manager
- form-builder
- email-studio
- filament-peek
- welcome-tour

Issues:
Translation Manager trusted client-side Livewire `entries` and `editable` flags when saving, so tampered state could add arbitrary translation keys. Some touched Translation Manager and Form Builder user-facing strings were still inline.

Fix:
Translation Manager now rebuilds editable keys server-side before writing files. Touched Translation Manager UI/status/AI labels and Form Builder empty submitted-value display now go through package translations. Added focused tamper regression coverage.

Review result for unchanged packages:
Email Studio, Filament Peek, and Welcome Tour had no high-confidence issue to fix in this pass.

Validation:

- `php -l` on changed Translation Manager and Form Builder PHP files passed.
- `vendor/bin/pest packages/translation-manager/tests --configuration=phpunit.xml` passed.
- `vendor/bin/pest packages/form-builder/tests --configuration=phpunit.xml` passed.
- `vendor/bin/pest packages/email-studio/tests packages/form-builder/tests packages/filament-peek/tests packages/translation-manager/tests packages/welcome-tour/tests --configuration=phpunit.xml --colors=never` passed.

### Public View Unused Frontend Context Cleanup

Status: fixed, focused tests passing.

Affected packages:

- content-sections
- site-discovery

Issue:
A public section view and public sitemap view resolved frontend site/language context in Blade without using it, adding unnecessary public-render work.

Fix:
Removed the unused `Frontend::*` calls from the public templates.

Validation:

- `vendor/bin/pest packages/content-sections/tests/Feature/SectionRenderingTest.php packages/content-sections/tests/Feature/PublicLayoutGraphSectionPayloadTest.php --configuration=phpunit.xml --colors=never` passed.
- `vendor/bin/pest packages/site-discovery/tests/Feature/Page/PageSitemapTest.php --configuration=phpunit.xml --colors=never` passed.

### Layout Builder Expert Review

Status: fixed by expert sidecar, package tests passing.

Affected package:

- layout-builder

Issues:
Scalar-mounted builders could accept mismatched `siteId` state for the mounted layout/page, and direct `editLayoutBlock()` calls missed the layout-edit authorization path for content-only block meta mutation.

Fix:
Layout Builder now rejects mismatched scalar site state, authorizes direct block edit calls, and guards invalid block targets. Added regression coverage for mismatched site mount and direct content-only block meta mutation.

Validation:

- `vendor/bin/pest packages/layout-builder/tests/Feature/Livewire/LayoutBuilderContentFirstTest.php --configuration=phpunit.xml` passed.
- `vendor/bin/pest packages/layout-builder/tests/Arch/LayoutBuilderPackageBoundaryTest.php --configuration=phpunit.xml` passed.
- `vendor/bin/pest packages/layout-builder/tests --configuration=phpunit.xml` passed.
- `git diff --check -- packages/layout-builder` passed.
- `vendor/bin/pest packages/layout-builder/tests/Unit/Support/Creator packages/layout-builder/tests/Unit/LayoutBuilderThirdRoundResidualCoverageTest.php --configuration=phpunit.xml --colors=never` passed after integration.

### Manifest, Boost, And Health Metadata Batch

Status: fixed, focused metadata tests passing.

Affected packages:

- access-gate
- agent-delivery
- api
- block-library
- comments
- dashboard-reports
- demo-kit
- document-lifecycle
- email-studio
- events
- filament-peek
- frontend-optimizer
- ga4-reports
- hero
- html-cache
- layout-builder
- media-ai
- newsletter
- notes
- password-policy
- public-actions
- search
- seo-suite
- shopify-commerce
- site-discovery
- theme-education
- theme-knowledge
- theme-local-services
- theme-nonprofit
- theme-portfolio
- translation-manager
- welcome-tour
- wordpress-importer

Issues:
Manifest v3 contribution traceability was incomplete for several deferred package contributions, product group expectations had drifted from current theme grouping, several packages lacked Boost resource stubs, and three packages declared health checks without package-owned health classes.

Fix:
Added missing contribution traceability entries, brought the manifest audit script and product-group arch expectations back in line with current package boundaries, added missing Boost guideline resources, and introduced health check classes for Agent Delivery, Comments, and Filament Peek.

Validation:

- `vendor/bin/pest tests/Feature/ManifestContributionTraceabilityTest.php tests/Feature/ManifestV3CoverageTest.php tests/Packages/Arch/ProductGroupManifestTest.php tests/Packages/BoostResourcesTest.php --configuration=phpunit.xml --colors=never` passed.

### Package Architecture And Forbidden Helper Batch

Status: fixed, package arch suite passing.

Affected packages:

- access-gate
- blog
- demo-kit
- html-cache
- insights
- layout-builder
- newsletter
- publishing-studio
- seo-suite
- shopify-commerce

Issues:
The package arch suite found two real boundary/convention failures. Blog and Layout Builder reached into Demo Kit internals/resources despite Demo Kit isolation rules. Multiple production files used forbidden or fragile global helpers: disabled `assert()` runtime guards, process-global `mt_rand()`, race-prone `tempnam()`, and direct `md5()`/`sha1()` calls.

Fix:
Removed Blog's Demo Kit media lookup and Layout Builder's hard Demo Kit class/package references, replacing Layout Builder demo media lookup with an explicit package config path. Replaced `assert()` calls with runtime exceptions, replaced Demo Kit's seeded `mt_rand()` planner with an internal deterministic generator, switched non-seeded demo randomness to `random_int()`, replaced direct hashes with `hash()`, and replaced `tempnam()` with UUID-based temporary paths.

Validation:

- `php -l` passed for the touched Blog, Demo Kit, Html Cache, Layout Builder, and Shopify PHP files checked locally.
- `vendor/bin/pest packages/demo-kit/tests/Unit/Actions/BuildDemoGenerationPlanActionTest.php packages/demo-kit/tests/Unit/Actions/DummyContentGeneratorActionTest.php --configuration=phpunit.xml --colors=never` passed.
- `vendor/bin/pest packages/layout-builder/tests/Unit/Support/Creator packages/layout-builder/tests/Unit/LayoutBuilderThirdRoundResidualCoverageTest.php --configuration=phpunit.xml --colors=never` passed.
- `vendor/bin/pest packages/blog/tests/Feature/Actions packages/blog/tests/Feature/BlogStaticSiteExtensionTest.php --configuration=phpunit.xml --colors=never` passed.
- `vendor/bin/pest packages/html-cache/tests/Feature/HtmlCacheMiddlewareTest.php packages/html-cache/tests/Feature/CachedModelUrlRecordingTest.php --configuration=phpunit.xml --colors=never` passed.
- `vendor/bin/pest packages/access-gate/tests/Unit/AccessGateCoverageTest.php --configuration=phpunit.xml --colors=never` passed.
- `vendor/bin/pest packages/newsletter/tests/Integration/Actions/ProviderAdaptersTest.php --configuration=phpunit.xml --colors=never` passed.
- `vendor/bin/pest packages/publishing-studio/tests/Integration/SchedulerOperationalActionsTest.php packages/publishing-studio/tests/Unit/PublishingStudioDataBoundaryCoverageTest.php --configuration=phpunit.xml --colors=never` passed.
- `vendor/bin/pest packages/shopify-commerce/tests/Unit/Actions/SearchShopifyProductsActionTest.php packages/shopify-commerce/tests/Unit/Actions/SyncShopifyProductsActionTest.php --configuration=phpunit.xml --colors=never` passed.
- `vendor/bin/pest packages/insights/tests/Feature/Database/InsightsMigrationsTest.php --configuration=phpunit.xml --colors=never` passed.
- `vendor/bin/pest packages/seo-suite/tests/Feature/Filament/SeoSuiteDashboardWidgetsTest.php --configuration=phpunit.xml --colors=never` passed.
- `vendor/bin/pest tests/Packages/Arch --configuration=phpunit.xml --colors=never` passed.
- `git diff --check` passed.

### Newsletter Retry Scheduler Concurrency

Status: fixed, focused tests passing.

Affected package:

- newsletter

Issue:
Newsletter retry requeueing used an atomic conditional claim at the action level, but the scheduled command still ran every five minutes without overlap or one-server guards. In multi-server scheduler deployments, that could dispatch duplicate scheduler runners and increase contention around due retry rows.

Fix:
Added `withoutOverlapping()` and `onOneServer()` to the retry requeue schedule, with coverage asserting the scheduled command is registered with both guards.

Validation:

- `vendor/bin/pest packages/newsletter/tests/Unit/NewsletterProviderSyncCoverageTest.php packages/newsletter/tests/Integration/Actions/ProviderSyncActionTest.php --configuration=phpunit.xml --colors=never` passed.

### Site Discovery Incremental Sitemap Locking

Status: fixed, focused tests passing.

Affected package:

- site-discovery

Issue:
Queued page/site sitemap listeners and the incremental XML sitemap command could call `XmlSitemapGenerator::processIncremental()` directly. Unlike the queued full generation action, the incremental path did not acquire the per-site sitemap lock before rebuilding and writing shared domain sitemap files/state.

Fix:
Wrapped incremental sitemap processing in the same per-site cache lock key used by full sitemap generation, with configurable wait time and a clear sitemap-generation exception on contention. Added regression coverage proving incremental generation refuses to run while the site lock is held.

Validation:

- `php -l packages/site-discovery/src/Support/Sitemap/XmlSitemapGenerator.php` passed.
- `php -l packages/site-discovery/tests/Integration/Sitemap/SitemapGeneratorIncrementalTest.php` passed.
- `vendor/bin/pest packages/site-discovery/tests/Integration/Sitemap/SitemapGeneratorIncrementalTest.php packages/site-discovery/tests/Integration/Sitemap/SitemapLifecycleListenerTest.php --configuration=phpunit.xml --colors=never` passed.

### Blog Public Tag Render Context

Status: fixed, focused tests passing.

Affected package:

- blog

Issue:
The public `page.tags` Blade partial resolved `Frontend::language()` directly while rendering tag links. That kept public-render context lookup inside Blade and made the partial fragile outside the normal frontend request context.

Fix:
Moved language and tag-page preparation into Blog render data/components. Article meta, article blocks, before-content tags, and after-title tags now pass prepared `language`/`tagPage` values into the public tag partial.

Validation:

- `php -l` passed for the touched Blog data/action/component PHP files.
- `vendor/bin/pest packages/blog/tests/Feature/Pages/ArticlesPageTest.php packages/blog/tests/Feature/Pages/TagPageTest.php packages/blog/tests/Feature/Pages/TagsPageTest.php packages/blog/tests/Feature/Actions packages/blog/tests/Feature/BlogStaticSiteExtensionTest.php --configuration=phpunit.xml --colors=never` passed.

### SEO Suite Schema Component Render Context

Status: fixed, focused tests passing.

Affected package:

- seo-suite

Issue:
Several public schema Blade components resolved frontend site/page/language context and ran structured-data Actions inside the template. That made public JSON-LD output depend on Blade-side data loading instead of prepared render context.

Fix:
Added a package view composer for schema components and moved breadcrumb, image, organization, and webpage schema preparation into PHP. The Blade components now only encode prepared `schemaJson`.

Validation:

- `php -l packages/seo-suite/src/View/Composers/SchemaComponentComposer.php` passed.
- `php -l packages/seo-suite/src/Providers/SeoSuiteServiceProvider.php` passed.
- `rg -n 'Frontend::|::run\\(|use Capell\\\\|use Illuminate\\\\' packages/seo-suite/resources/views/components/schema -g '*.blade.php' -S` returned no matches.
- `vendor/bin/pest packages/seo-suite/tests/Unit/Enums/MetaSchemaEnumTest.php packages/seo-suite/tests/Integration/Actions/BreadcrumbsSchemaActionTest.php packages/seo-suite/tests/Integration/Actions/PageMetaSchemaActionTest.php packages/seo-suite/tests/Integration/Actions/ResolvePageStructuredDataActionTest.php packages/seo-suite/tests/Feature/Site/SiteMetaSchemaTest.php --configuration=phpunit.xml --colors=never` passed.

### Agent Bridge, Campaign Studio, Block Library, Media Library, Foundation Theme, And Hero Batch

Status: fixed by expert sidecar, package tests passing.

Affected packages:

- agent-bridge
- campaign-studio
- block-library
- media-library
- foundation-theme
- hero

Issues:
Agent Bridge policy-protected capability previews could skip policy checks without an authenticated user. Campaign Studio CTA/goals were not fully scoped by campaign group site, and public CTA tracking leaked numeric campaign IDs. Media Library's Spatie-to-Curator migration was brittle when optional tables were absent and could instantiate non-Eloquent owner classes. Foundation Theme/Hero public views used unstable or forbidden ID helpers.

Fix:
Agent Bridge now requires an authenticated user before policy-protected capability previews. Campaign Studio CTA/goals are site-scoped and public CTA tracking avoids numeric campaign IDs. Media Library migration guards missing optional tables and validates owner model classes. Foundation Theme/Hero use deterministic hashes for public IDs, with Hero background IDs stable across renders.

Validation:

- `vendor/bin/pest packages/agent-bridge/tests --configuration=phpunit.xml` passed.
- `vendor/bin/pest packages/campaign-studio/tests --configuration=phpunit.xml` passed.
- `vendor/bin/pest packages/block-library/tests --configuration=phpunit.xml` passed.
- `vendor/bin/pest packages/media-library/tests --configuration=phpunit.xml` passed.
- `vendor/bin/pest packages/foundation-theme/tests --configuration=phpunit.xml` passed.
- `vendor/bin/pest packages/hero/tests --configuration=phpunit.xml` passed.
- `git diff --check` passed for scoped packages.

### Address, Login Audit, Password Policy, Public Actions, And Access Gate Batch

Status: fixed by expert sidecar, package tests passing.

Affected packages:

- address
- login-audit
- password-policy
- public-actions
- access-gate

Issues:
Public redirect handling accepted unsafe same-host non-HTTP(S) schemes. Login Audit trusted malformed/spoofed CDN IP header values. Public Actions and Access Gate Filament create/edit flows had site-scope persistence tampering gaps.

Fix:
Public Actions and Access Gate now reject same-host `javascript:` and other non-HTTP(S) redirects. Login Audit sanitizes CDN IP headers and falls back to the direct request IP for malformed values. Public Actions and Access Gate persistence now enforces site scope for limited actors. The existing Access Gate production `assert()` repair remains in place.

Validation:

- `vendor/bin/pest packages/address/tests packages/login-audit/tests packages/password-policy/tests packages/public-actions/tests packages/access-gate/tests --configuration=phpunit.xml --colors=never` passed.
- Targeted Pint check passed on touched files.
- `php -l` passed on changed PHP files.
- `git diff --check` passed for scoped packages.

### Comments, Events, Document Lifecycle, Deployments, And Diagnostics Batch

Status: fixed by expert sidecar, package tests passing.

Affected packages:

- comments
- events
- document-lifecycle
- deployments
- diagnostics

Issues:
Comments admin access allowed generic authenticated users without global or site scope to reach moderation resources. Events public calendar trusted tampered Livewire `month` state and did not tolerate missing `Site::timezone`. Some Deployments and Diagnostics admin strings bypassed translations. Diagnostics tests still used forbidden helpers.

Fix:
Comments moderation resources now require appropriate global/site scope. Events calendar validates mutable month state and falls back safely when a site timezone attribute is missing. Deployments navigation group and Diagnostics command palette/cache-health/admin strings now use translations. Scoped untyped closures and forbidden Diagnostics test helpers were cleaned up.

Validation:

- `vendor/bin/pest packages/comments/tests packages/events/tests packages/document-lifecycle/tests packages/deployments/tests packages/diagnostics/tests --configuration=phpunit.xml --colors=never` passed.
- Focused regression files passed individually.
- `git diff --check -- packages/comments packages/events packages/document-lifecycle packages/deployments packages/diagnostics` passed.

### Migration Assistant, Frontend Optimizer, GA4 Reports, Tags, And Navigation Batch

Status: fixed locally, package tests passing.

Affected packages:

- migration-assistant
- frontend-optimizer
- ga4-reports
- tags
- navigation

Issue:
GA4 metric persistence still used a read-then-write pattern behind package Actions. The sync action now has a per-window cache lock, but the persistence Actions are reusable package boundaries and the metric tables already enforce unique natural keys, so concurrent direct callers could still race into duplicate-key failures. A first attempted `updateOrCreate()` repair exposed a test-harness date-cast mismatch: Eloquent date casts store SQLite date values as midnight timestamps while exact attribute matching used a raw date string.

Fix:
Changed daily and page metric persistence to use database-level `upsert()` with the existing unique keys, then reload rows via `whereDate()`. Added direct persistence regression coverage proving repeated writes update the same natural-key rows. Reviewed the already-present Migration Assistant import-session claim/queue guards, Frontend Optimizer render-profile claim/job overlap guards, Tags site-scoped admin/resource paths, and Navigation site/language-aware loader/resource paths.

Validation:

- `php -l packages/ga4-reports/src/Actions/PersistGA4ReportsDailyMetricAction.php` passed.
- `php -l packages/ga4-reports/src/Actions/PersistGA4ReportsPageMetricAction.php` passed.
- `php -l packages/ga4-reports/tests/Feature/Actions/GA4ReportsActionsTest.php` passed.
- `find packages/tags/src -type f | sort | xargs -n 1 php -l` passed.
- `find packages/navigation/src -type f | sort | xargs -n 1 php -l` passed.
- `vendor/bin/pest packages/frontend-optimizer/tests --configuration=phpunit.xml --colors=never` passed.
- `vendor/bin/pest packages/ga4-reports/tests --configuration=phpunit.xml --colors=never` passed.
- `vendor/bin/pest packages/tags/tests --configuration=phpunit.xml --colors=never` passed.
- `vendor/bin/pest packages/navigation/tests --configuration=phpunit.xml --colors=never` passed.
- `vendor/bin/pest packages/migration-assistant/tests --configuration=phpunit.xml --colors=never` passed when run alone.

Validation caveat:
Running the full Migration Assistant package suite concurrently with other package suites produced two transient Livewire wizard state failures that did not reproduce in isolated test files or the serialized package run. Stateful package suites should stay serialized during final validation.

### Blade Forbidden Helper Cleanup

Status: fixed, focused tests passing.

Affected packages:

- content-sections
- layout-builder

Issue:
The local Blade static pass found direct `md5()` calls in view-generated DOM keys. They were not security hashes, but they bypassed the repo-wide convention already enforced for PHP sources during the package architecture cleanup.

Fix:
Replaced remaining Blade `md5()` DOM-key generation with `hash('xxh128', ...)` while preserving deterministic IDs/keys.

Validation:

- `rg -n "md5\\(|sha1\\(|tempnam\\(|mt_rand\\(|assert\\(" packages/*/resources/views -g '*.blade.php' -S` returned no matches.
- `vendor/bin/pest packages/content-sections/tests/Feature/SectionRenderingTest.php packages/content-sections/tests/Feature/PublicLayoutGraphSectionPayloadTest.php --configuration=phpunit.xml --colors=never` passed.
- `vendor/bin/pest packages/layout-builder/tests/Feature/Livewire/LayoutBuilderContentFirstTest.php --configuration=phpunit.xml --colors=never` passed.
- `git diff --check` passed.

### Public Actions Security And Retry Batch

Status: fixed locally, package tests passing.

Affected packages:

- public-actions

Issues:
Site-scoped admins could create a global Public Actions integration token through the token Action/page flow. Webhook dispatch attempts recorded retryable provider failures, but the queued job never released itself for another attempt.

Fix:
`CreatePublicActionIntegrationTokenAction` now accepts an actor and enforces global-token and site-token authorization at the domain boundary. The Filament token page requires a site for non-global actors and passes the current actor into the Action. Webhook dispatch results now carry the recorded dispatch status, and retryable job results release back to the queue with a configured delay.

Validation:

- `vendor/bin/pest packages/public-actions/tests/Feature/Filament/PublicActionResourceAuthorizationTest.php packages/public-actions/tests/Unit/Support/HttpWebhookPublicActionAdapterTest.php --configuration=phpunit.xml --colors=never` passed.
- `vendor/bin/pest packages/public-actions/tests --configuration=phpunit.xml --colors=never` passed.

### Migration Assistant Worker Failure Batch

Status: fixed locally, package tests passing.

Affected packages:

- migration-assistant

Issue:
If a queued import worker timed out or was killed after a session moved to `running`, Laravel could call the job failure hook without the import session being marked failed.

Fix:
`ExecuteImportPlanJob::failed()` now reloads the session and marks queued/running sessions failed through the same failure path used by handled exceptions.

Validation:

- `vendor/bin/pest packages/migration-assistant/tests/Integration/MigrationAssistant/ExecuteImportPlanJobTest.php --configuration=phpunit.xml --colors=never` passed.
- `vendor/bin/pest packages/migration-assistant/tests --configuration=phpunit.xml --colors=never` passed serialized.

### Events Notifications And Public Schema Batch

Status: fixed locally, package tests passing.

Affected packages:

- events

Issues:
Reminder notification logs were scheduled but never processed. Public event occurrence queries did not eager-load the singular translation used by Blade. Event JSON-LD rendering built schema from an under-hydrated occurrence, risking lazy loads in public render paths.

Fix:
Added `ProcessDueEventNotificationLogsAction` with atomic queued-to-sending claims, due reminder processing, failure marking, and scheduler registration guarded with overlap and one-server locks. Public event occurrence queries now eager-load `event.translation`. JSON-LD hooks resolve a hydrated public occurrence through an Action before calling the schema builder.

Validation:

- `vendor/bin/pest packages/events/tests/Integration/EventReviewFindingsTest.php --configuration=phpunit.xml --colors=never` passed.
- `vendor/bin/pest packages/events/tests --configuration=phpunit.xml --colors=never` passed.

### Newsletter Import Durability Batch

Status: fixed locally, package tests passing.

Affected packages:

- newsletter

Issues:
Non-dry-run subscriber imports created their batch as `completed` before applying subscriber writes, tags, and provider sync queueing. A failure could leave partially applied work behind a completed batch.

Fix:
Added an `processing` import batch status. Non-dry-run imports now start as processing, wrap subscriber/tag mutations in a transaction, queue provider sync after the transaction, mark completed only after sync queueing succeeds, and mark failed before rethrowing any import/sync failure. Also repaired a newsletter segment unit fixture that violated site foreign keys.

Validation:

- `vendor/bin/pest packages/newsletter/tests/Integration/Actions/NewsletterReviewFixTest.php packages/newsletter/tests/Unit/NewsletterDataBoundaryCoverageTest.php --configuration=phpunit.xml --colors=never` passed.
- `vendor/bin/pest packages/newsletter/tests --configuration=phpunit.xml --colors=never` passed.

### Layout Builder Public Fragment Marker Batch

Status: fixed locally, package tests passing.

Affected packages:

- layout-builder
- demo-kit
- sibling `../capell-4/packages/frontend`

Issues:
Public lazy fragment placeholders and URLs exposed Capell/Layout Builder identifiers through `data-capell-fragment`, `/_capell/fragments/...`, and `capell-layout-builder-*` public classes.

Fix:
Public placeholders now use generic `data-deferred-fragment` attributes, the route moved to `/_fragments/{reference}`, and public wrapper classes were renamed to generic layout/deferred names. The sibling frontend runtime and interaction render Action now use the generic route and dataset names. Demo Kit public rendering tests assert the old markers are absent. A Demo Kit unit fixture that deleted referenced default widget blueprints now removes dependent widgets first so FK enforcement remains enabled.

Validation:

- `vendor/bin/pest packages/layout-builder/tests/Feature/Fragments/PublicFragmentRenderingTest.php packages/demo-kit/tests/Feature/KitchenSinkDemoPageTest.php --configuration=phpunit.xml --colors=never` passed.
- `vendor/bin/pest packages/layout-builder/tests --configuration=phpunit.xml --colors=never` passed.
- `vendor/bin/pest packages/demo-kit/tests --configuration=phpunit.xml --colors=never` passed.

### Full-Suite Foreign-Key Fixture Hardening Batch

Status: fixed locally, affected package tests passing.

Affected packages:

- comments
- demo-kit
- email-studio
- navigation
- newsletter
- publishing-studio

Issue:
The broad package suite exposed several tests that inserted literal foreign-key ids for sites, languages, blueprints, pages, URLs, or scheduler sources. Those fixtures only worked when FK checks were effectively absent and masked production-integrity failures.

Fix:
Reworked the failing fixtures to create real related records through factories, use nullable source references where the production code intentionally models missing sources, and delete dependent demo widgets before force-deleting referenced default widget blueprints.

Validation:

- Focused failing files passed after repair.
- `vendor/bin/pest packages/navigation/tests --configuration=phpunit.xml --colors=never` passed.
- `vendor/bin/pest packages/email-studio/tests --configuration=phpunit.xml --colors=never` passed.
- `vendor/bin/pest packages/comments/tests --configuration=phpunit.xml --colors=never` passed.
- `vendor/bin/pest packages/publishing-studio/tests --configuration=phpunit.xml --colors=never` passed.
- `vendor/bin/pest packages/demo-kit/tests --configuration=phpunit.xml --colors=never` passed.
- `vendor/bin/pest packages/newsletter/tests --configuration=phpunit.xml --colors=never` passed.

### Admin Route And Manifest Metadata Batch

Status: fixed locally, affected package tests passing.

Affected packages:

- content-sections
- document-lifecycle
- events
- frontend-authoring
- navigation
- publishing-studio
- sibling `../capell-4/packages/core`

Issues:
The Publishing Studio workspace switcher rendered in Filament's global header and generated a WorkspaceResource URL even when the resource route was not registered in the current panel/test context, causing unrelated admin pages to 500. Manifest package registration also discarded plain Laravel service providers from v3 manifests because core only accepted providers extending Capell's abstract package provider, leaving package install metadata incomplete for first-party companion packages. A Publishing Studio dashboard test still expected the old scalar dashboard column contract after the admin dashboard moved to responsive columns.

Fix:
The workspace switcher now hides the manage link unless the WorkspaceResource index route is registered. Core manifest registration now preserves any Laravel `ServiceProvider` class from manifest provider declarations, matching the runtime provider contract used by companion packages. The dashboard test now asserts the current responsive dashboard column shape.

Validation:

- `vendor/bin/pest packages/content-sections/tests/Feature/Filament/Resources/Section/SectionResourceTest.php packages/content-sections/tests/Feature/Filament/Resources/Section/Widgets/EditSectionAlertWidgetTest.php packages/document-lifecycle/tests/Feature/DocumentLifecycleAdminTest.php packages/events/tests/Integration/EventsNavigationGroupTest.php tests/Packages/Integration/FilamentPackageNavigationTest.php packages/publishing-studio/tests/Admin/Unit/Widgets/CapellDashboardTest.php packages/frontend-authoring/tests/Unit/Providers/FrontendAuthoringServiceProviderTest.php packages/navigation/tests/Unit/Providers/NavigationServiceProviderTest.php --configuration=phpunit.xml --colors=never` passed.
- From `../capell-4`: `vendor/bin/pest packages/core/tests/Unit/Concerns/HasPackagesTest.php packages/core/tests/Integration/Actions/InstallPackageActionTest.php packages/core/tests/Integration/PluginCacheTest.php --configuration=phpunit.xml --colors=never` passed.
- `vendor/bin/pest packages/content-sections/tests packages/document-lifecycle/tests packages/events/tests packages/frontend-authoring/tests packages/navigation/tests packages/publishing-studio/tests --configuration=phpunit.xml --colors=never` passed with 956 passed / 1 skipped.

### Static Analysis And Generated Output Type-Safety Batch

Status: fixed locally, full tests and PHPStan passing.

Affected packages:

- blog
- campaign-studio
- comments
- dashboard-reports
- demo-kit
- events
- html-cache
- navigation
- newsletter
- seo-suite
- site-discovery
- tags
- translation-manager
- test harness

Issues:
PHPStan exposed a set of high-confidence type and failure-path defects after the broad test fixes: nullable page URL fixture assumptions, generated public URL contributors passing nullable languages, schedule tests reading nullable events, URL helpers passing `false` into URL matchers/parsers, sitemap/generated-output reports returning non-list arrays, redundant permission guards against methods guaranteed by the Capell actor contract, and the package database guard reading config through array access on the application contract.

Fix:
Tightened nullable fixture assertions into explicit failure paths, normalized generated-output URL indexes and sitemap XML parsing through typed helpers, replaced fragile `mb_*` URL slash trimming with native ASCII slash trimming, made Site Discovery page/report APIs return concrete list shapes, kept generated output coverage collections typed to the public contract, simplified guaranteed actor site-scope checks, and resolved package database guard config through the `ConfigRepository` contract. Dashboard trend tests now sum typed point arrays without assuming a collection.

Validation:

- `composer analyze --no-interaction --no-ansi` passed with 4,641 analysed paths and no errors.
- `vendor/bin/pest packages/dashboard-reports/tests/Feature/Actions/Dashboard/BuildPublishingTrendActionTest.php --configuration=phpunit.xml --colors=never` passed.
- Affected package sweep passed except the dashboard test issue above, then the focused dashboard rerun passed after repair.

### Final Validation Snapshot

Status: passing after all local repairs in this pass.

Validation:

- `vendor/bin/pest --configuration=phpunit.xml --colors=never` passed serialized with 4,079 passed / 1 skipped / 30,123 assertions.
- `vendor/bin/pest tests/Packages/Arch --configuration=phpunit.xml --colors=never` passed with 17 tests / 25 assertions.
- `composer validate --no-check-publish --no-interaction --no-ansi` passed.
- `composer analyze --no-interaction --no-ansi` passed.
- `composer test --no-interaction --no-ansi` passed in parallel with 4,097 passed / 1 skipped / 30,207 assertions.
- `git diff --check` passed for this repo.
- `git -C ../capell-4 diff --check` passed for the touched sibling core/frontend files.

## Blockers

None currently.
