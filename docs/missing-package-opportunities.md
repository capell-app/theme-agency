# Missing Package Opportunities

> Last reviewed: 2026-06-12. Scope: evaluate proposed package ideas against the current package catalogue, manifests, extension suites, and package improvement plans. This is a planning document only; no package has been scaffolded from this review.

## Source Checks

This review uses the catalogue and package boundaries in:

- [Package product groups](product-groups.md)
- [Extension suites](extension-suites.md)
- historical package improvement plan status
- Relevant `packages/*/capell.json` manifests and package-local improvement plans

The current catalogue already has strong package coverage for media, automation, course discovery, CRM, social widgets, comments, SEO, diagnostics, and customer self-service. The best missing opportunities are the ones that add a clear owning model or workflow without absorbing sibling package responsibilities.

## Decision Summary

| Proposed idea                      | Decision                                       | Existing anchors                                                                                                                                      | Likely placement                                                    | Rationale                                                                                                                                                                                                                                                                                                                                         |
| ---------------------------------- | ---------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `capell-app/accessibility-auditor` | **Package candidate**                          | `seo-suite`, `diagnostics`, `site-discovery`, `frontend-optimizer`, themes, `media-library`                                                           | Premium, likely **Capell Operations** with Search & SEO support     | No current package owns a site-wide accessibility issue store, audit command, admin remediation queue, or screenshot-backed accessibility proof. Existing packages ship pieces such as alt-text checks, WCAG theme fixes, PageSpeed audits, and public-output leak scanning, but not an accessibility product.                                    |
| `capell-app/reputation-manager`    | **Wait**                                       | `comments`, `social-feeds`, `contacts`, `insights`                                                                                                    | Future Growth or Engagement depth                                   | The valuable part is third-party review ingestion, review-response workflow, testimonial curation, and source attribution. The catalogue has moderation, cached social feeds, and CRM identity, but no review-provider contracts yet. This should wait until provider scope is clear.                                         |
| `capell-app/course-builder`        | **Wait unless Capell commits beyond LMS Lite** | `events`, `bookings`, `form-builder`, `access-gate`, `customer-portal`, `payments`, `knowledge-base`, `structured-content-library`                     | Future Content or Education package, outside current LMS Lite suite | The current Courses / LMS Lite suite intentionally covers course discovery, enrolment, open days, gated areas, appointments, and paid enrolment. A course builder would add lesson/module models, learner progress, assessments, certificates, and portal learning views, which is a separate product tier and not obvious from the current docs. |
| `capell-app/integration-hub`       | **Do not create as a package**                 | `automation-studio`, `public-actions`, `api`, `agent-bridge`, `deployments`, `email-studio`, `shopify-commerce`, `ga4-reports`                        | Marketplace bundle or docs surface for **Capell Automation**        | The implementation work already belongs to Automation Studio and Public Actions. A separate Integration Hub package would duplicate registries, webhook destinations, event rules, API exposure, and connector surfaces. Treat the name as a buyer-facing bundle, not a Composer package.                                                         |
| `capell-app/media-pro`             | **Best package opportunity**                   | `media-library`, `media-ai`, `seo-suite`, `frontend-optimizer`, themes                                                                                | Premium **Capell Media / Media Pro / DAM**                          | Media Library explicitly defers folders, galleries, generated WebP/AVIF conversions, visual focal/crop editing, and richer where-used UI to Media Pro/DAM. The implementation path is already visible from Media Library seams, but it still needs a package plan before scaffolding.                                                             |

## Recommended Product Decisions

1. **Build `capell-app/media-pro` first, but only from a written package plan.** Media Library already exposes the seams Media Pro needs: focal/crop metadata APIs, responsive metadata reads, rights metadata checks, duplicate detection, missing-alt candidates, and `BuildMediaUsageDrilldownAction`. The package should require `capell-app/media-library` and support `capell-app/media-ai`, `capell-app/seo-suite`, `capell-app/frontend-optimizer`, and theme packages.

2. **Plan `capell-app/accessibility-auditor` as the second standalone package candidate.** It should own audit runs, issue records, admin remediation views, and a command/API boundary. It should not patch public Blade output directly, perform authoring decoration, or duplicate SEO Suite's metadata workflow.

3. **Do not create `integration-hub`.** Use the name for a marketplace suite or docs page that explains Automation Studio + Public Actions + API + Agent Bridge. Any implementation should deepen existing packages: inbound signed webhooks and non-HTTP adapters in Public Actions, richer rule setting UI in Automation Studio, and styled API explorer screenshots in API.

4. **Keep `course-builder` out of scope until the product target is explicit.** If the ask is marketing/enrolment, improve Theme Education and the Courses / LMS Lite suite. If the ask is learning delivery, write a separate LMS product plan with lesson/module/progress models before scaffolding.

5. **Hold `reputation-manager` until provider choices are real.** The current catalogue can show social proof through Comments, Social Feeds, Contacts, and theme proof sections. A new package is only justified once it owns external review providers, response workflow, curation rights, and reputation reporting.

## Audience Fit Pass

This second pass checks the recommendations against the Capell audience positioning. The package decisions should sell outcomes without weakening the core story: Capell is Laravel packages, structure is defined once, the frontend stays owned by the application team, and optional capability arrives through installable extensions.

| Audience           | What they need from these decisions                                                   | Product implication                                                                                                                                                                                                  |
| ------------------ | ------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Site owner         | A clear reason to buy another package instead of seeing another technical add-on.     | Lead with Media Pro reducing asset risk and Accessibility Auditor surfacing fixable site-quality issues. Keep Integration Hub, Course Builder, and Reputation Manager from becoming confusing shelfware.             |
| Admin/editor       | Visible workflows they can use without breaking the site.                             | Media Pro must show folders, crops, conversions, rights, and where-used screens. Accessibility Auditor must show issue queues and remediation state, not just command output.                                        |
| Critical developer | Boundaries that will not turn into core patches or public-output leaks.               | Both package candidates need Actions/Data, manifest contributions, package-owned tables, optional supports, and explicit public-output safety tests before scaffolding.                                              |
| Frontend developer | No takeover of the DOM, no CMS runtime in public pages, and no hidden public queries. | Media Pro and Accessibility Auditor can produce reports and hydrated data, but public Blade must stay query-free and free of admin/editor metadata.                                                                  |
| Package developer  | Stable extension points and Marketplace proof.                                        | Each package candidate needs contribution classes, health checks, screenshot contracts, and documented supports rather than ad hoc cross-package imports.                                                            |
| Agency             | Repeatable delivery and a stronger package bundle story.                              | Media Pro fits high-value media-heavy sites; Accessibility Auditor fits QA/compliance retainers. Integration Hub is better as an Automation bundle story than a separate package.                                    |
| Evaluator          | Honest fit guidance and restraint.                                                    | Course Builder and Reputation Manager should wait because the current catalogue does not yet justify new Composer packages. This is a strength: Capell avoids adding packages where existing boundaries are clearer. |
| Site visitor       | Fast, readable, consistent public output.                                             | Media Pro should improve image delivery and visual consistency; Accessibility Auditor should improve headings, alt text, forms, landmarks, and clean output without shipping audit tooling to visitors.              |

## Issue-Ready Spec: Media Pro

**Working package name:** `capell-app/media-pro`

**Decision:** Create a premium package plan, then scaffold only after the package boundaries and Marketplace contract are approved.

**Product job:** Turn the free Media Library foundation into a DAM-grade workflow for teams that need asset governance, conversion output, and editor-friendly visual controls.

**Audience outcomes:**

- Site owners get a safer media operation: fewer expired rights, fewer duplicate files, clearer usage evidence, and generated image formats that support performance goals.
- Editors get practical admin screens for folders, focal crops, conversion status, rights metadata, and where an asset is used before replacing or deleting it.
- Frontend developers keep control of markup and rendering. Media Pro should produce conversion metadata and hydrated asset data, not inject frontend gallery behavior or query DAM tables from public Blade.
- Package developers get a package-owned DAM layer that builds on Media Library contracts instead of replacing Curator or reaching into sibling internals.
- Agencies get a repeatable premium media workflow they can attach to media-heavy client sites without custom per-project asset tooling.

**Requires:** `capell-app/admin`, `capell-app/core`, `capell-app/media-library`

**Supports:** `capell-app/media-ai`, `capell-app/seo-suite`, `capell-app/frontend-optimizer`, first-party themes, `capell-app/diagnostics`

**MVP surfaces:**

- Admin page for media folders/collections and asset assignment.
- Visual focal-point and crop editor using Media Library focal/crop metadata, without changing public output contracts.
- Conversion job queue for generated WebP/AVIF variants and a structured conversion status panel.
- Rich where-used UI backed by `BuildMediaUsageDrilldownAction`, with package-safe labels and routes when available.
- Rights metadata workflow for missing rights, expiry, license notes, and exportable evidence.
- Package health check for required tables, queue readiness, conversion driver config, media-library binding, and orphaned conversion files.

**Domain boundaries:**

- Media Pro owns DAM workflow tables, conversion job state, folder/collection taxonomy, and admin workflow.
- Media Library remains the Curator backend, shared field factory, media-health baseline, and migration package.
- Media AI owns provider-backed generated metadata or image editing. Media Pro may display generated alt/caption state but should call Media AI Actions or listen to events instead of duplicating AI behavior.
- Public theme output must consume hydrated media data only. Public Blade must not query DAM tables, expose folder IDs, expose admin routes, or leak package names.

**Data and Actions to plan:**

- `CreateMediaCollectionAction`, `AssignMediaToCollectionAction`, `UpdateMediaFocalCropAction`
- `QueueMediaConversionAction`, `RecordMediaConversionResultAction`, `BuildMediaConversionStatusAction`
- `BuildMediaWhereUsedReportAction`, wrapping Media Library drill-down data with optional route labels
- `UpdateMediaRightsMetadataAction`, `ExportMediaRightsEvidenceAction`
- `MediaCollectionData`, `MediaConversionRequestData`, `MediaConversionResultData`, `MediaUsageReportData`, `MediaRightsData`

**Manifest and Marketplace readiness:**

- `capell.json` manifest v3 with product group `Capell Media`, bundle `media`, tier `premium`, surfaces `admin` and `console`.
- Contribution classes for admin resources/pages, models, settings, health checks, commands, scheduled jobs, and assets.
- `docs/overview.md`, `docs/screenshots.json`, and committed marketplace assets before certification.
- Screenshots must show real seeded media: folder/collection admin, focal/crop editor, conversion status, where-used report, and rights metadata workflow. Do not promote empty Media tables.

**Tests:**

- Action tests for folder assignment, focal/crop persistence, conversion queue claims, conversion result recording, where-used report shaping, and rights metadata updates.
- Manifest tests proving every shipped model/resource/page/command/health check is declared.
- Public-output tests proving Media Pro data never leaks admin/editor state into anonymous or non-admin responses.
- Queue/job tests for idempotent conversion claims and failure handling.
- Screenshot contract tests or manifest validation for every promoted marketplace capture.

**Not in MVP:**

- AI image editing, provider routing, or moderation. Keep that in Media AI / AI Orchestrator.
- Replacing Curator or changing Media Library's shared field factory.
- Frontend gallery blocks unless they are hydrated through existing theme/Layout Builder boundaries.

**Implementation prompt:**

> Create a package plan for `capell-app/media-pro` in this repo. Use Media Library as the required foundation, preserve public-output safety, define Actions/Data/manifest contributions for folders, focal/crop editing, conversion jobs, where-used reporting, and rights metadata, and include Marketplace screenshots/tests before scaffolding.

## Issue-Ready Spec: Accessibility Auditor

**Working package name:** `capell-app/accessibility-auditor`

**Decision:** Candidate standalone premium package. Start with a plan and technical spike before scaffolding because the runner strategy matters.

**Product job:** Give operators and editors a Capell-native accessibility queue that audits public pages, records issue evidence, and routes remediation to the right package owner without leaking admin state into public output.

**Audience outcomes:**

- Site owners get an evidence-backed quality report they can review without reading Lighthouse logs or raw HTML.
- Editors get a remediation queue that explains which page needs work and whether the fix belongs in content, media metadata, a form, a theme, or a package.
- Frontend developers get actionable findings for headings, landmarks, labels, focusable controls, contrast-adjacent metadata, and public-output cleanliness while keeping the frontend implementation theirs.
- Package developers get a feedback loop that identifies package-owned rendering issues without adding authoring markers or audit payloads to anonymous responses.
- Agencies get a retainer-friendly QA surface for launch checks, redesigns, and ongoing content governance.
- Visitors benefit indirectly through cleaner structure, better media descriptions, labelled forms, predictable reading order, and no audit runtime shipped to the browser.

**Requires:** `capell-app/admin`, `capell-app/core`, `capell-app/frontend`

**Supports:** `capell-app/site-discovery`, `capell-app/seo-suite`, `capell-app/media-library`, `capell-app/frontend-optimizer`, `capell-app/diagnostics`, `capell-app/publishing-studio`

**MVP surfaces:**

- Admin dashboard for recent audit runs, page scores, issue severity, and open/remediated counts.
- Page-level issue table with WCAG category, selector or structural location, evidence, package owner when known, and remediation guidance.
- Console command for scheduled or CI-friendly audits with JSON/CSV output.
- Optional Site Discovery URL source so the package can audit known public URLs without owning crawling.
- Optional SEO Suite bridge so image-alt, heading, metadata, and public-output checks can be shown together without duplicating SEO Suite storage.
- Diagnostics health check for runner availability, stale audit runs, queue failures, and broken URL sources.

**Runner strategy to decide before implementation:**

- Phase 1 can use deterministic server-side checks for rendered HTML snapshots: H1 count, heading order, landmarks, missing alt text, empty links/buttons, duplicated IDs, form labels, table headers, and admin-marker leaks.
- Phase 2 can add a browser-backed axe/Lighthouse runner when the local/deployment screenshot runner can support it repeatably.
- Do not make browser automation a hard runtime dependency until install and queue behavior are proven in a package workbench or consuming app.

**Domain boundaries:**

- Accessibility Auditor records audit runs and issues. It does not mutate pages directly.
- SEO Suite keeps SEO scoring, schema, PageSpeed, and AI Discovery ownership. Accessibility Auditor can link to SEO issues but should not write SEO Suite tables unless a documented Action exists.
- Media Library and Media AI own media metadata and generation. Accessibility Auditor can identify missing/weak alt text and dispatch package events or recommendations.
- Themes/content packages keep their own render fixes. Accessibility Auditor should identify package-owned view/component evidence and route fixes to the owning package.

**Data and Actions to plan:**

- `RunAccessibilityAuditAction`, `ResolveAccessibilityAuditTargetsAction`
- `AnalyzeRenderedHtmlAccessibilityAction`, `RecordAccessibilityIssueAction`
- `BuildAccessibilityDashboardAction`, `MarkAccessibilityIssueResolvedAction`
- `ExportAccessibilityAuditReportAction`
- `AccessibilityAuditRunData`, `AccessibilityIssueData`, `AccessibilityTargetData`, `AccessibilityFindingData`

**Manifest and Marketplace readiness:**

- Product group should likely be `Capell Operations` unless a future Quality/Compliance group is introduced.
- Bundle key should stay stable before release. Suggested first pass: `operations`.
- Contributions for admin pages/resources, models, console command, health checks, scheduled job, and optional report widgets.
- Marketplace screenshots should show an audit dashboard, page issue list, issue detail/remediation state, command output/report, and a before/after trend. Avoid generic Lighthouse screenshots unless they are wrapped in Capell UI.

**Tests:**

- Action tests for deterministic HTML findings.
- Public-output safety tests for audit reports and any frontend bridge.
- Site Discovery target resolution tests with installed and absent dependencies.
- Manifest tests for all contributed resources, models, commands, health checks, and scheduled jobs.
- Command tests for JSON/CSV output and strict failure when critical issues exceed configured thresholds.

**Not in MVP:**

- Auto-fixing theme Blade.
- Legal compliance claims beyond recorded WCAG-oriented checks.
- Browser-only checks that cannot run in CI or the screenshot runner.

**Implementation prompt:**

> Write the package plan for `capell-app/accessibility-auditor`. Keep phase 1 deterministic with server-side rendered HTML checks, use Site Discovery as an optional target source, bridge to SEO Suite and Media Library through documented Actions/events only, and define manifest, Marketplace, test, and screenshot requirements before scaffolding.

## Existing-Package Depth Specs

### Integration Hub

Use this as bundle language for the Automation product family, not a package.

**Where work belongs:**

- Public Actions: inbound signed webhooks, non-HTTP adapters, Form Builder binding UI, spam adapter breadth.
- Automation Studio: richer rule-setting UI, trigger payload enrichers, first package-local improvement plan, screenshot proof for real rule workflows.
- API: styled endpoint explorer or JSON response renderer for buyer-facing screenshots.
- Agent Bridge: token management and capability preview/confirmation captures when styled runner targets exist.
- Deployments: Marketplace install/update publishing workflows.

**Next prompt:**

> Create a Capell Automation suite doc that positions Automation Studio, Public Actions, API, Agent Bridge, and Deployments as "Integration Hub" without introducing a new Composer package. Include the specific package-local follow-up issues and Marketplace screenshots each package needs.

### Course Builder

Keep this as a future product decision, not a current package.

**If the goal is course marketing/enrolment:** deepen existing packages:

- Theme Education: stronger first viewport, programme comparison, cohort/intake data, differentiated screenshots.
- Events: term/cohort dates and paid ticketing/enrolment support if needed.
- Form Builder: application workflows and package-owned form bindings.
- Access Gate + Customer Portal: gated learner resources and portal links.
- Payments: paid enrolment or subscription entitlements.

**If the goal is learning delivery:** write a separate product plan for:

- Course, module, lesson, assessment, certificate, enrolment, and progress models.
- Draft/publish support via Publishing Studio if lessons are content records.
- Learner dashboard via Customer Portal provider registries.
- Gated lesson access through Access Gate and Payments.
- Search/SEO output through Knowledge Base, Search, Site Discovery, and SEO Suite.

**Next prompt:**

> Decide whether Course Builder means "course marketing/enrolment" or "learning delivery." If it is learning delivery, write a package plan for course/module/lesson/progress models, Customer Portal learner surfaces, Access Gate/Payments entitlements, Publishing Studio draftability, and public-output safety before scaffolding.

### Reputation Manager

Wait until the provider and workflow target are known.

**Potential future scope:**

- Review provider contracts for Google Business Profile, Trustpilot, Facebook, Yelp, or first-party review intake.
- Review response workflow with admin approvals and audit trail.
- Testimonial curation into Content Sections/theme proof blocks.
- Contacts source attribution for reviewers and consent.
- Insights reporting for review-driven conversions.
- Moderation and spam flow reuse from Comments where appropriate.

**Why not now:** without provider choices, it risks becoming either a thin Social Feeds clone or a broad CRM/comments duplicate.

**Next prompt:**

> Triage `reputation-manager` as a provider-selection spike. Pick one source provider and one first-party review workflow, then decide whether the result belongs in Social Feeds, Comments, Contacts, or a new package.

## Follow-Up Catalogue Notes

- `docs/product-groups.md` is narrower than the current manifests and docs index. It omits groups now used by manifests such as Capell Automation, Capell Media, Capell Engagement, Capell Commerce, Capell Content, and Capell Marketing. Do not change package placement from this review alone, but refresh that catalogue before making Marketplace pricing or bundle metadata changes.
- `media-ai` currently uses product group `Capell Media`, while `extension-suites.md` describes Media Pro / DAM as a suite anchored by Media Library. Align the naming before releasing `media-pro`.
- Avoid hard dependencies for suite relationships. Use `dependencies.supports` only when the package has real code, docs, or extension points for that pairing.
