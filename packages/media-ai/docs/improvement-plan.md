# Media AI — Improvement & Growth Plan

> Package: capell-app/media-ai · Kind: package · Tier: premium · Product group: Capell Media · Bundle: media · Status: Draft

## 1. Snapshot

Media AI is a thin admin seam package that adds one Filament header action — `doctor-image` — to the Capell Admin Media edit page via the `MediaEditActionExtender` tag (`src/Filament/MediaAIEditActionExtender.php`, `src/Providers/MediaAIServiceProvider.php`). It defines an `ImageDoctor` contract (`src/Contracts/ImageDoctor.php`) with `doctor(Media, ImageDoctorRequest): ImageDoctorResult`, ships a safe `NullImageDoctor` bound by default (`src/Support/NullImageDoctor.php`), and two readonly Data objects (`ImageDoctorRequest` = validated `operation` + `instructions` strings; `ImageDoctorResult` = `successful` bool + nullable `message`). It owns no models, migrations, settings, routes, console commands, or public output; deps are `capell-app/admin`, `capell-app/core`, plus a soft `suggest` on `capell-app/ai-orchestrator`. Marketplace metadata now includes the extension card plus the existing light/dark Doctor image captures (`docs/assets/marketplace/extension-card.jpg`, `docs/images/screenshots/media-ai-doctor-image.png`, and `docs/images/screenshots/media-ai-doctor-image-dark.png`).

## 2. Improvements (existing functionality)

- **Bind a real `ImageDoctor` somewhere — today there is no production provider** — The whole package is a contract seam with only `NullImageDoctor` bound; grep across the monorepo shows zero non-test implementations, and `ai-orchestrator/src` exposes only `AIOrchestratorModule` / `AIOrchestratorProviderConnector` (no image/vision/doctor contract). The advertised "AI-assisted media actions" therefore never run for any installer. — `src/Providers/MediaAIServiceProvider.php:33`, `packages/ai-orchestrator/src/Contracts/` — L
- **Shipped: Reconcile `ImageDoctorResult` with documented behaviour** — `docs/overview.md` now states that `ImageDoctorResult` carries only `successful` + optional `message`, that notifications are text-only success/warning notifications, and that the original media record is never mutated by this package. — `docs/overview.md`, `src/Data/ImageDoctorResult.php`, `src/Filament/MediaAIEditActionExtender.php` — Done
- **Shipped: Fix the Boost guideline so it matches the code** — `resources/boost/guidelines/core.blade.php` now describes the actual flow: resolve `ImageDoctor`, send an `ImageDoctorRequest`, surface the `ImageDoctorResult` message, and write no media metadata. — Done
- **Shipped: Validate `operation` against the known set server-side** — `ImageDoctorRequest::OPERATIONS` is the canonical allow-list, the constructor rejects unsupported operations, the Filament Select applies `->in(ImageDoctorRequest::OPERATIONS)`, and tests cover both direct Data validation and crafted Livewire payload rejection before the provider is called. — `src/Data/ImageDoctorRequest.php`, `src/Filament/MediaAIEditActionExtender.php`, `tests/Feature/ImageDoctorRequestTest.php`, `tests/Feature/MediaAIEditActionTest.php` — Done
- **Run provider calls on a queue, not in the request** — `doctor()` is invoked synchronously inside the Filament action; a real image model (upscale/restore) will be multi-second and block the admin request/worker. Dispatch a job and notify on completion (Filament database notifications). — `src/Filament/MediaAIEditActionExtender.php:43-62` — M
- **Make `MediaEditActionExtender` interface-presence a hard signal, not silent** — registration is skipped when `interface_exists(MediaEditActionExtender::class)` is false (`MediaAIServiceProvider.php:37`); if admin is absent the package installs and does nothing with no diagnostic. Surface this in the health check (see §4). — `src/Providers/MediaAIServiceProvider.php:35-40` — S
- **Localise the `RecordingImageDoctor`/result `message` strings through lang** — the test fixture returns a hardcoded English `'Doctor finished'`; the contract should document that providers must return translated messages, since `message` is shown verbatim in the editor notification. — `tests/Fixtures/RecordingImageDoctor.php:23`, `src/Filament/MediaAIEditActionExtender.php:55` — S

## 3. Missing Features (gaps)

Manifest `capabilities[]` = `media-ai`, `media-ai-admin`. The package deliberately declares no console capability because there is no `Console/` directory and `commands.install/setup/demo/doctor` are all `null` in `capell.json`.

- **Alt-text generation (table stakes, biggest gap).** The single most expected "Media AI" feature is auto alt-text / captions for images — for accessibility and SEO. `media-library` already ships a `MediaHealthTable` (`packages/media-library/src/Filament/Pages/Tables/MediaHealthTable.php`) that lists media but has **no missing-alt-text signal**; `overview.md` notes alt text lives in `translations.meta`. Media AI should generate localized alt text/captions and write them to that column — this is the differentiator that ties media-ai to media-library and seo-suite.
- **Auto-tagging / keyword extraction** for media records to power search and filtering — nothing today.
- **Batch / bulk processing.** The action is single-record only. A bulk table action ("generate alt text for all images missing it") is the obvious high-value console + admin feature and would finally give the `media-ai-console` capability a reason to exist.
- **Cost / token controls.** `ImageDoctorRequest` carries no budget, model, or quota hint; there is no per-site spend cap, rate limit, or usage logging. For a paid AI feature this is mandatory before GA.
- **Provider failover via ai-orchestrator.** `composer.json` only `suggest`s ai-orchestrator and the contract is provider-agnostic, but there is no adapter, no failover, and no model-selection path. Shipping a first-party `AIOrchestratorImageDoctor` adapter (with retry/failover) is the missing link between the two packages.
- **Moderation / safety gate** on generated or edited imagery (NSFW / policy checks) before the result is surfaced — absent.
- **Multilingual alt-text.** `ImageDoctorRequest` has no locale; Capell is multi-site/multi-language, so generated metadata should be per-locale. Add `locale` to the request and loop locales for batch runs.
- **Result audit trail.** No record of what was requested/generated (operation, instructions, outcome, cost) — needed for support, billing, and the activity log.

Differentiator vs table-stakes: alt-text + auto-tagging + multilingual + batch are _table stakes_ for an AI media product; the _differentiator_ is doing them natively inside the Capell media-health workflow with per-site cost governance and ai-orchestrator failover.

## 4. Issues / Risks

- **Shipped: Manifest `healthChecks[]` have executable coverage.** `MediaAIHealthCheck` implements `runDiagnostics()` and `passed()` for provider binding, edit-action registration, and structured request construction; tests cover the diagnostics and disabled edit-action check. — `src/Health/MediaAIHealthCheck.php`, `tests/Feature/MediaAIHealthCheckTest.php` — Done
- **Shipped: Unfulfilled `media-ai-console` capability removed.** `capell.json` now declares only `admin` surface/category metadata and `media-ai` / `media-ai-admin` capabilities because no console code ships. — `capell.json` — Done
- **Shipped: Disabled path, null-provider failure, and operation validation tests.** Tests now cover disabled service-provider registration, `NullImageDoctor` returning the localized `not_configured` failure, failed doctor results rendering as warning notifications, and crafted operation payloads failing before the provider is called. — `tests/Feature/MediaAIEditActionTest.php`, `tests/Feature/ImageDoctorRequestTest.php` — Done
- **Synchronous provider call = queue/latency risk** (see §2). No timeout, retry, or job isolation; a slow or throwing provider degrades the admin panel directly. — `src/Filament/MediaAIEditActionExtender.php:43` — M
- **No rate limiting / abuse ceiling** on a paid AI action — a user with media `update` rights can fire unlimited provider calls. — `src/Filament/MediaAIEditActionExtender.php` — M
- **Secret handling: deferred entirely to the (absent) provider.** Acceptable for a seam, but document that the `ImageDoctor` implementer must keep credentials in the provider package and never in `ImageDoctorRequest`/result, since `message` is rendered verbatim to the admin (`MediaAIEditActionExtender.php:55`). — `src/Contracts/ImageDoctor.php` — S
- **Public-output / cache safety: OK.** Manifest `cacheSafety.cacheable=false`, no frontend surface, no public Blade; nothing leaks to anonymous users. No action needed beyond keeping it that way.
- **Performance budget note.** `capell.json` sets `frontendRenderBudgetMs: 0` (correct — no frontend) and `adminQueryBudget: 40`. The current single action adds ~0 admin queries; a batch/bulk feature must be benchmarked against the 40-query admin budget. — `capell.json:44-46` — (track when batch lands)
- **i18n gap.** `ImageDoctorRequest` has no `locale`; generated metadata can't be per-language despite Capell being multi-locale (see §3). — `src/Data/ImageDoctorRequest.php` — S

## 5. Marketplace & Selling

**Critique.** Manifest `summary` and composer `description` are near-identical and self-referential ("adds optional AI-assisted media actions") — they name the category without stating a single concrete outcome (no mention of alt text, background removal, upscaling, accessibility, or SEO). A buyer can't tell what the action _does_. The composer `description` ("Optional AI-assisted media actions for Capell.") is even thinner. Both bury the actual capabilities listed in `resources/lang/en/media-ai.php` (improve / remove background / remove object / restore / upscale).

**Improved 1-sentence summary:**

> AI image actions inside Capell's media library — remove backgrounds, clean up and upscale photos, and (roadmap) auto-generate accessible, multilingual alt text — with a safe no-op fallback until you connect a provider.

**Improved 3–4 sentence description:**

> Media AI adds an AI "Doctor image" action to every image in the Capell Admin media library, letting editors remove backgrounds, erase objects, restore, and upscale without leaving the CMS. It's provider-agnostic by design: bind any image model through the `ImageDoctor` contract (pairs naturally with AI Orchestrator for failover), or leave it unconfigured and the action stays safely hidden behind a null implementation. On the roadmap, batch alt-text and caption generation feed Media Library's media-health workflow for accessibility and SEO at scale. Record-level media permissions are enforced on every request, and the package adds no public frontend output.

**Screenshot / media gaps.** Only the static `extension-card.jpg` is in the marketplace array. Add: (1) the existing `media-ai-doctor-image.png` light + dark captures (already in-repo, currently unreferenced by marketplace), (2) the operation-select modal, (3) a before/after of an actual edit once a provider is bound, and (4) a batch alt-text run once built. `docs/screenshots.json` `media-ai-doctor-image` entry is marked `required: false` and only captures the media index, not the action modal — upgrade it to open the action.

**Pricing / tier / bundle positioning.** Tier `premium`, group `Capell Media`, bundle `media`. Media AI is currently the **only** package in the `media` bundle (grep of `"bundle": "media"` across all `capell.json`), so the bundle can't yet be sold as a bundle — either fold `media-library` into the `media` bundle to make the cross-sell real, or position media-ai as a premium add-on _to_ media-library. As a contract-only seam with no working provider, premium pricing is hard to justify today; price should follow a shipped first-party provider adapter + alt-text batch.

**Cross-sell.** Hard deps: `capell-app/admin`, `capell-app/core`. Natural Extension-Suite cross-sells: **media-library** (owns Curator media + the `MediaHealthTable` that alt-text generation should populate), **ai-orchestrator** (provider/failover backend the `suggest` already points at), and **seo-suite** (alt text + captions are an SEO win — bundle the accessibility/SEO angle).

**Differentiators / value props / target buyer.** Value props: (1) AI image editing without leaving the CMS, (2) safe-by-default null fallback (install risk-free), (3) provider-agnostic contract (no lock-in). Target buyer: agencies and content teams on Capell with large image libraries who need accessibility/SEO compliance and faster media prep.

**8–12 keywords/tags:** `ai image editing`, `alt text generation`, `image upscaling`, `background removal`, `media accessibility`, `image captioning`, `media library ai`, `seo images`, `laravel cms ai`, `filament media action`, `multilingual alt text`, `ai orchestrator`.

## 6. Prioritized Roadmap

| Item                                                                                                  | Bucket  | Effort | Impact | Section ref | Evidence |
| ----------------------------------------------------------------------------------------------------- | ------- | ------ | ------ | ----------- | -------- |
| Ship a first-party `AIOrchestratorImageDoctor` adapter so the action actually works                   | Now     | L      | High   | §2, §3      | Not started |
| Fix Boost guideline (no metadata written) + reconcile `overview.md` "replacement media / link" claims | Shipped | S      | Med    | §2          | `resources/boost/guidelines/core.blade.php` and `docs/overview.md` describe notification-only, no-mutation behaviour |
| Server-side validate `operation` against known set                                                    | Shipped | S      | Med    | §2, §4      | `ImageDoctorRequest::OPERATIONS`, Select `->in(...)`, `ImageDoctorRequestTest`, crafted Livewire payload test |
| Add tests for disabled flag + NullImageDoctor warning path                                            | Shipped | S      | Med    | §4          | `MediaAIEditActionTest` covers disabled provider registration, null-provider failure result, and warning notification |
| Resolve manifest mismatches: empty `media-ai-console` capability, undercounted screenshots            | Shipped | S      | Med    | §4, §5      | `capell.json` removed unsupported console metadata and lists extension card plus light/dark screenshots |
| Move provider call to a queued job with timeout/retry                                                 | Next    | M      | High   | §2, §4      | Not started |
| Add `locale` to `ImageDoctorRequest`; document translated `message` requirement                       | Next    | S      | Med    | §3, §4      | Not started |
| Alt-text + caption generation written to `translations.meta`                                          | Next    | L      | High   | §3          | Not started |
| Add missing-alt-text signal/column to media-library `MediaHealthTable` (cross-pkg)                    | Next    | M      | High   | §3, §5      | Not started |
| Rate limit / per-site usage cap + cost/token controls on `ImageDoctorRequest`                         | Next    | M      | High   | §3, §4      | Not started |
| Batch/bulk processing (table bulk action + console command)                                           | Next    | M      | High   | §3          | Not started |
| Result audit trail (operation, outcome, cost) into activity log                                       | Later   | M      | Med    | §3          | Not started |
| Moderation/safety gate on generated imagery                                                           | Later   | M      | Med    | §3          | Not started |
| Auto-tagging / keyword extraction for media search                                                    | Later   | L      | Med    | §3          | Not started |
| Rewrite marketplace summary + description; expand screenshot set; sort bundle/tier positioning        | Now     | S      | High   | §5          | Partially shipped: summary/description and screenshot manifest updated; pricing/bundle positioning remains open |
