# Privacy Center — Improvement & Growth Plan
> Package: capell-app/privacy-center · Kind: package · Tier: premium · Product group: Capell Operations · Bundle: operations · Status: Draft

## 1. Snapshot

Privacy Center is an admin + console package providing a package-owned compliance ledger: consent records, policy versions/acceptances, subject (DSAR) requests, retention rules, plus export/anonymize Actions. It ships 5 models/tables (`privacy_consent_policies`, `privacy_consent_records`, `privacy_policy_acceptances`, `privacy_retention_rules`, `privacy_requests`), 11 Actions (`src/Actions`), 5 Filament resources + 1 stats widget (`src/Filament`), 8 Data objects, 8 enums, and a per-subject SHA-256 hashing helper (`src/Support/PrivacyIdentifier.php`). Surfaces declared: `admin`, `console`. Deps: requires `capell-app/admin` + `capell-app/core`; supports `access-gate`, `contacts`, `insights`, `newsletter`. The only live cross-package integration today is `insights` calling `RecordConsentAction` via `packages/insights/src/Actions/MirrorInsightsConsentToPrivacyCenterAction.php`.

Current marketplace summary (verbatim): *"Privacy Center provides the compliance workflow foundation for consent, retention, privacy requests, exports, deletion, and policy acceptance."* Screenshot count: **0** (`marketplace.screenshots: []`) — a gap for a premium-tier, paid, first-party-certification-requested package. `README.md` and `CHANGELOG.md` are **absent from disk** (only `docs/overview.md` exists), despite `composer.json` advertising a docs URL.

## 2. Improvements (existing functionality)

- **Wire `MarkPrivacyRequestFulfilledAction` to a UI button on the request edit page** — the Action exists and is tested, but `src/Filament/Resources/PrivacyRequests/Pages/EditPrivacyRequest.php` defines **no header actions**. Operators can only change status via a raw `Select` on the form, which `forceFill`s `status` but never stamps `fulfilled_at`/`verified_at`/`rejected_at` or runs the Action. Add Filament `Action`s ("Mark verified", "Mark fulfilled", "Reject" with reason) that delegate to Actions. — `src/Filament/Resources/PrivacyRequests/Pages/EditPrivacyRequest.php`, `src/Filament/Resources/PrivacyRequests/PrivacyRequestResource.php` — M
- **Add a "Run now" / dry-run action to the retention rule UI** — `RetentionRuleResource` lets operators create rules, but there is no way to execute one from the admin and see matched/affected counts (`RetentionExecutionResultData` already carries them). Add a per-row/header action calling `ApplyRetentionRuleAction` and surface the result. — `src/Filament/Resources/RetentionRules/Pages/ListRetentionRules.php` — S
- **Add status/type/category/date filters to the record tables** — `ConsentRecordResource`, `PrivacyRequestResource`, `PolicyAcceptanceResource` tables have columns but **no `filters()`**. For a compliance ledger this is core: filter requests by `status`/overdue `due_at`, consents by `category`/`decision`/`jurisdiction`. — `src/Filament/Resources/*/*Resource.php` — S
- **Index/guard against orphaned `policy_id` after retention anonymize** — `ApplyRetentionRuleAction::anonymize()` and `AnonymizePrivacySubjectAction` null out subject/evidence but leave `policy_id`; fine, but `RegisterConsentPolicyAction` + `ConsentPolicy` deletion uses `nullOnDelete` — confirm anonymized rows remain queryable by `category`/`decided_at` (they do via existing indexes). Low-effort: add an explicit "anonymized" marker in `metadata` so audit reports can distinguish anonymized vs never-linked rows. — `src/Actions/ApplyRetentionRuleAction.php` — S
- **`PrivacyIdentifier::hashSecret()` silently falls back to `app.key`, then to a hard-coded literal** — if neither `CAPELL_PRIVACY_CENTER_HASH_SECRET` nor `app.key` is set, hashes use the constant string `'capell-privacy-center'`, producing globally predictable hashes. Fail loudly (or at minimum log) instead of using a guessable salt for compliance evidence. — `src/Support/PrivacyIdentifier.php` — S
- **Overview widget runs 4 unbounded `COUNT(*)` queries every dashboard load** — `BuildPrivacyCenterOverviewStatsAction` does 4 full-table counts with no caching; on large ledgers this is slow and repeated per dashboard render. Cache via the declared `privacy-center` cache tag with model-event invalidation (already declared in manifest `cacheSafety.invalidationSources`). — `src/Actions/BuildPrivacyCenterOverviewStatsAction.php` — M
- **`KeyValue` editing of `workflow_payload`/`metadata` on the request form is free-text and unvalidated** — operators can corrupt structured workflow payloads. Make these read-only or render via a structured infolist. — `src/Filament/Resources/PrivacyRequests/PrivacyRequestResource.php` — S

## 3. Missing Features (gaps)

Mapped to `capabilities[]`:

- **`privacy-center-cookie-categories` has no public consent surface (table-stakes gap).** The package ships **no Blade view, no Livewire component, no route** (`resources/` contains only `lang/`). There is no cookie consent banner, no preference centre, no JS that gates analytics/marketing cookies. The capability is "declared" only by the existence of the `CookieCategory` enum + a record-writing Action. This is the single biggest gap: GDPR/ePrivacy/CCPA all assume a user-facing banner with granular per-category opt-in and an easy withdraw path. Today consent can only be *recorded* by another package or by code. — differentiator: a cache-safe, theme-aware, geo-aware banner that writes straight to this ledger.
- **`privacy-center-retention-execution` is unreachable in production (false capability).** `ApplyRetentionRulesAction` has **zero callers** outside its own file and tests. `PrivacyRetentionScheduleContribution` implements `RunsScheduledExtensionJob`, but that contract is an empty marker (`vendor/capell-app/core/.../RunsScheduledExtensionJob.php`) and the `scheduled-job` entry in `capell.json` declares **no `command` and no `frequency`** — unlike `packages/search` which declares `"command": "search:purge", "frequency": "monthly"`. There is no `src/Console` directory and `manifest.commands` are all `null`. Retention never runs on a schedule. — table-stakes.
- **No public DSAR intake (subject request) form.** `OpenPrivacyRequestAction` exists, but only `insights`/code can call it. GDPR Art. 12/15-22 + CCPA require a *consumer-facing* way to submit access/export/delete requests. No public route, no verification (email confirmation) flow — `verified_at` exists on the model but nothing populates it. — table-stakes.
- **DSAR export/erasure is single-package only (completeness gap).** `BuildPrivacyExportAction` and `AnonymizePrivacySubjectAction` only touch Privacy Center's own 3 tables. There is **no cross-package erasure/export registry** — no contract a package implements to contribute its data to a subject export or to be erased. `docs/overview.md` explicitly defers this ("operate on Privacy Center records first"), but a "Privacy Center" that can't fulfil a real right-to-erasure across `contacts`/`insights`/`newsletter` is incomplete. — **key differentiator** if built: a `ContributesSubjectData` / `ErasesSubjectData` contract that other packages register against.
- **Insights mirror records no `subject` link.** `MirrorInsightsConsentToPrivacyCenterAction` passes `source: $consent` but never a `subject`. Mirrored consent therefore has null `subject_type`/`subject_id`, so `BuildPrivacyExportAction`/`AnonymizePrivacySubjectAction` (which key on subject) will **never find it**. The "consent mirror" satisfies the ledger-write but breaks the export/erasure promise for that data. — high-value fix; also argues for the package owning the integration contract instead of insights hard-coding class-name strings.
- **No consent proof export / certificate.** Records exist but there's no "produce signed proof of consent at time T" output (CSV/PDF), which is the artefact regulators/auditors actually ask for. — differentiator.
- **No geo/jurisdiction-aware policy selection.** `jurisdiction` is a free string column on consent; nothing maps a visitor region → which policy/categories apply (GDPR vs CCPA vs nothing). — differentiator.
- **No consent expiry/refresh job.** `expires_at` exists on `ConsentRecord` and `ConsentDecision::Expired` exists, but nothing transitions granted→expired or re-prompts. — table-stakes for "valid consent" claims.
- **No policy publication workflow.** `ConsentPolicy` has `published_at`/`effective_at`/`retired_at` but the resource is plain CRUD — no "publish"/"retire" action, no enforcement that acceptances reference a published version.

## 4. Issues / Risks

- **Manifest over-claims capabilities (compliance/trust risk for a paid, first-party package).** `privacy-center-retention-execution` and `privacy-center-cookie-categories` are advertised but not reachable end-to-end (no scheduler wiring; no banner). Selling these as delivered is a certification risk. — `capell.json` `capabilities[]`, `src/Manifest/PrivacyRetentionScheduleContribution.php`.
- **Health check is a no-op stub.** `PrivacyCenterHealthCheck` implements `ChecksExtensionHealth` (empty marker) and only returns `'^4.0'`. Its manifest label claims it verifies "models, migrations, and service provider are discoverable" — it verifies **nothing**. Any real outage (missing table, unbound model) passes the health check green. — `src/Health/PrivacyCenterHealthCheck.php`, `capell.json` `healthChecks[]`.
- **All four `Manifest/*Contribution` classes are behaviourless markers.** `ConsentPolicyResourceContribution`, `PrivacyCenterOverviewWidgetContribution`, `PrivacyCenterModelsContribution`, `PrivacyRetentionScheduleContribution` only return a version string. They pass `ManifestRequirementsTest` (which asserts the marker interface is implemented) but encode no real contribution beyond what `capell.json` strings declare. Acceptable per core's design, but it means the test suite proves *declaration*, not *behaviour*.
- **Test gaps:**
  - No test exercises the **scheduled job actually running** (because nothing wires it). The retention tests call `ApplyRetentionRulesAction::run()` directly — they would pass even though production never triggers it.
  - No Filament interaction tests (`livewire()` create/edit/save, no assertion that fulfilling a request stamps `fulfilled_at`). `PrivacyCenterAdminSurfaceTest` only asserts page keys, model class, and nav labels — never renders or mutates.
  - No test for `PrivacyIdentifier` hash determinism/secret fallback, or that anonymization is irreversible.
  - No public-output cache-safety test, despite the skill mandating one for any package with public surface — currently moot because there *is* no public surface, but the moment a banner ships this becomes required.
  - No assertion that the insights mirror produces records discoverable by export (it doesn't — see §3).
- **Consent-record integrity / proof.** Only `ip_hash`/`user_agent_hash` + free-form `evidence` JSON are stored. There is no immutable/append-only guarantee and no tamper-evidence (e.g. hash chaining), so the ledger's value as legal proof is weak. `evidence` is nulled on anonymize, which is correct, but there's no retained "consent happened" skeleton record after subject erasure.
- **Cache-safety posture is declared but untested.** `performance.cacheSafety` correctly sets `cacheable:false, sensitiveOutput:true, variesBy:[site,subject]`. Good. But the admin tables/widget pull hashed identifiers (`email_hash`, `ip_hash`) into the UI; ensure these never leak to non-admin/log surfaces. `adminQueryBudget: 40` is declared but nothing measures it; the widget's 4 counts + 5 resource list queries could exceed it on heavy pages.
- **`frontendRenderBudgetMs: 0`** correctly signals "no frontend render" — but it will need a real budget the moment a consent banner is added; flag now.
- **i18n.** Enum labels and admin strings are fully translated (`resources/lang/en/privacy.php`) — good. But there is exactly one locale (`en`), and the (future) public banner/DSAR form copy isn't covered at all.
- **Docs.** `README.md` and `CHANGELOG.md` do not exist on disk; `composer.json` advertises `docs: https://docs.capell.app/packages/privacy-center`. Only `docs/overview.md` (which is candid that admin/retention surfaces are deferred). For a paid package this is thin.

## 5. Marketplace & Selling

**Critique.** The current `summary` ("...compliance workflow *foundation* for consent, retention, privacy requests, exports, deletion, and policy acceptance") and composer `description` ("Privacy, consent, retention, and subject request *foundation* for Capell CMS") both lean on the word "foundation," which is honest (it *is* a ledger of Actions) but undersells nothing and oversells the cookie-banner/retention-execution capabilities that aren't reachable. Buyers reading "consent" + "cookie categories" will expect a banner; there is none. Zero screenshots for a premium paid SKU is a conversion killer.

**Improved 1-sentence summary:**
> The compliance backbone for Capell — one auditable ledger for cookie consent, policy acceptances, retention rules, and GDPR/CCPA subject requests, with Actions your other packages plug straight into.

**Improved 3–4 sentence description:**
> Privacy Center gives every Capell site a single, queryable system of record for privacy obligations: granular cookie-category consent, versioned policy acceptances, retention rules, and access/export/delete subject requests. Other Capell packages — Insights, Contacts, Newsletter — record consent and contribute subject data through stable Actions, so exports and erasure stay complete across the stack. Hashed request evidence (IP, user-agent) and immutable consent records give you defensible proof, while scheduled retention keeps data minimised automatically. Admin operators get resources and an at-a-glance compliance dashboard; nothing sensitive ever leaks to public output.

(Note: the second and third sentences describe the *target* state — the cross-package contract and scheduled retention are gaps in §3 and must ship before this copy is truthful.)

**Screenshot/media gaps:** need at minimum (1) the privacy-requests queue with status badges, (2) retention rules list, (3) the overview dashboard widget, (4) a request detail with fulfil/reject actions, and — once built — (5) the public cookie banner / preference centre. Currently `screenshots: []`.

**Pricing/tier/bundle positioning.** Premium tier in the `operations` bundle is right — privacy is a horizontal, regulatory must-have, not a nice-to-have, and bundling with other Operations packages suits agencies/regulated buyers. The strongest commercial wedge is the **Extension Suite cross-sell**: it `supports` `insights`, `contacts`, `newsletter`, `access-gate`. Lead with "buy Insights *and* Privacy Center so analytics consent is captured and erasable," and "Contacts + Privacy Center = real right-to-erasure for your CRM." The inbound `insights` mirror already demonstrates the pattern; make the outbound export/erasure contract the headline suite feature.

**Differentiators / value props / target buyer.** Target buyer: agencies and in-house teams running EU/UK/CA-facing Capell sites who need defensible compliance without bolting on a third-party CMP. Value props: (1) consent + DSAR live *inside* the CMS, not a separate SaaS; (2) one ledger across all Capell packages; (3) auditable, hashed proof; (4) automated retention. Differentiator vs table-stakes: a generic cookie banner is table-stakes (and currently missing); the **cross-package subject-data contract** (one erasure call wipes contacts + analytics + newsletter consent) is the genuine differentiator no standalone CMP offers.

**Keywords/tags (8–12):** `gdpr`, `ccpa`, `cookie-consent`, `consent-management`, `dsar`, `subject-access-request`, `right-to-erasure`, `data-retention`, `privacy`, `compliance`, `audit-log`, `policy-acceptance`.

## 6. Prioritized Roadmap

| Item | Bucket | Effort | Impact | Section ref |
|---|---|---|---|---|
| Wire retention execution: add `privacy:apply-retention` console command + `command`/`frequency` in `capell.json` scheduled-job block | Now | S | High | §3, §4 |
| Make `PrivacyCenterHealthCheck` actually verify tables/models/provider discoverable | Now | S | High | §4 |
| Fix insights mirror to pass a `subject` so mirrored consent is exportable/erasable | Now | S | High | §3 |
| Add fulfil/verify/reject UI actions on request edit page (delegate to Actions, stamp timestamps) | Now | M | High | §2 |
| Reconcile manifest capabilities with reality (or ship the missing pieces before claiming them) | Now | S | High | §4 |
| Add README.md + CHANGELOG.md; expand docs beyond overview | Now | S | Med | §4 |
| Fail-loud (or log) when `PrivacyIdentifier` falls back to the literal hash salt | Now | S | Med | §2, §4 |
| Add table filters (status/overdue/category/decision/jurisdiction) to record resources | Next | S | Med | §2, §3 |
| Add "run now / dry-run" retention action surfacing matched/affected counts | Next | S | Med | §2 |
| Cache the overview widget stats via the declared `privacy-center` tag + model-event invalidation | Next | M | Med | §2, §4 |
| Add Filament interaction tests (render + fulfil stamps `fulfilled_at`) and a scheduled-job-runs test | Next | M | Med | §4 |
| Build public cookie consent banner / preference centre (cache-safe, theme-aware) writing to the ledger + cache-safety test | Next | L | High | §3 |
| Cross-package `ContributesSubjectData` / `ErasesSubjectData` contract + registry (the suite differentiator) | Later | L | High | §3, §5 |
| Public DSAR intake form with email verification populating `verified_at` | Later | L | High | §3 |
| Consent expiry/refresh job (granted→expired) + policy publish/retire workflow + signed consent-proof export | Later | M | Med | §3 |
| Geo/jurisdiction-aware policy & category selection (GDPR vs CCPA) | Later | L | Med | §3, §5 |
