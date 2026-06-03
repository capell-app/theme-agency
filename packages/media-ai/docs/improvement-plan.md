# Media AI — Improvement & Growth Plan

> Package: capell-app/media-ai · Kind: package · Tier: premium · Product group: Capell Media · Bundle: media · Status: Draft

## 1. Snapshot

Media AI is a thin admin/console seam package (7 PHP files in `src/`) that adds one Filament header action — `doctor-image` — to the Capell Admin Media edit page via the `MediaEditActionExtender` tag (`src/Filament/MediaAIEditActionExtender.php`, `src/Providers/MediaAIServiceProvider.php`). It defines an `ImageDoctor` contract (`src/Contracts/ImageDoctor.php`) with `doctor(Media, ImageDoctorRequest): ImageDoctorResult`, ships a safe `NullImageDoctor` bound by default (`src/Support/NullImageDoctor.php`), and two readonly Data objects (`ImageDoctorRequest` = `operation` + `instructions` strings; `ImageDoctorResult` = `successful` bool + nullable `message`). It owns no models, migrations, settings, routes, or public output; deps are `capell-app/admin`, `capell-app/core`, plus a soft `suggest` on `capell-app/ai-orchestrator`. Current marketplace summary verbatim: **"Media AI adds optional AI-assisted media actions for Capell."** Marketplace declares **1 screenshot** (`docs/assets/marketplace/extension-card.jpg`) while `docs/screenshots.json` lists 1 entry and the repo actually contains 2 more captures (`docs/images/screenshots/media-ai-doctor-image.png` + `-dark.png`) — the marketplace media array under-counts available assets.

## 2. Improvements (existing functionality)

- **Bind a real `ImageDoctor` somewhere — today there is no production provider** — The whole package is a contract seam with only `NullImageDoctor` bound; grep across the monorepo shows zero non-test implementations, and `ai-orchestrator/src` exposes only `AIOrchestratorModule` / `AIOrchestratorProviderConnector` (no image/vision/doctor contract). The advertised "AI-assisted media actions" therefore never run for any installer. — `src/Providers/MediaAIServiceProvider.php:33`, `packages/ai-orchestrator/src/Contracts/` — L
- **Reconcile `ImageDoctorResult` with documented behaviour** — `docs/overview.md` promises the result returns "optional replacement media" and a notification that "links the editor to the generated image", but the Data object only carries `successful` + `message` and the action only sends a text `Notification` (`MediaAIEditActionExtender.php:54-61`). Either add a `?Media replacement` / `?string url` field and render a link, or correct the docs. — `src/Data/ImageDoctorResult.php`, `src/Filament/MediaAIEditActionExtender.php` — M
- **Fix the Boost guideline so it matches the code** — `resources/boost/guidelines/core.blade.php` states the package "augments media records with AI-assisted metadata," but no code path writes metadata; it fires `doctor()` and notifies. This misleads any AI assistant working in the package. — `resources/boost/guidelines/core.blade.php` — S
- **Validate `operation` against the known set server-side** — the action passes `$data['operation']` straight into `ImageDoctorRequest` with only the Select's client-side options (`improve`, `remove_background`, `remove_object`, `restore`, `upscale`) constraining it; add `->in(array_keys(__('...operations')))` or validate in the Data constructor so a crafted Livewire payload can't reach a provider with an arbitrary operation. — `src/Filament/MediaAIEditActionExtender.php:43-52`, `src/Data/ImageDoctorRequest.php` — S
- **Run provider calls on a queue, not in the request** — `doctor()` is invoked synchronously inside the Filament action; a real image model (upscale/restore) will be multi-second and block the admin request/worker. Dispatch a job and notify on completion (Filament database notifications). — `src/Filament/MediaAIEditActionExtender.php:43-62` — M
- **Make `MediaEditActionExtender` interface-presence a hard signal, not silent** — registration is skipped when `interface_exists(MediaEditActionExtender::class)` is false (`MediaAIServiceProvider.php:37`); if admin is absent the package installs and does nothing with no diagnostic. Surface this in the health check (see §4). — `src/Providers/MediaAIServiceProvider.php:35-40` — S
- **Localise the `RecordingImageDoctor`/result `message` strings through lang** — the test fixture returns a hardcoded English `'Doctor finished'`; the contract should document that providers must return translated messages, since `message` is shown verbatim in the editor notification. — `tests/Fixtures/RecordingImageDoctor.php:23`, `src/Filament/MediaAIEditActionExtender.php:55` — S

## 3. Missing Features (gaps)

Manifest `capabilities[]` = `media-ai`, `media-ai-admin`, `media-ai-console`. The `console` capability has **no code** — there is no `Console/` directory and `commands.install/setup/demo/doctor` are all `null` in `capell.json`. So a declared capability is entirely unfulfilled.

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

- **Manifest `healthChecks[]` are not executed.** `capell.json` declares three checks (`media-ai.provider-adapter`, `media-ai.edit-action`, `media-ai.structured-request`) with severities, but `MediaAIHealthCheck` implements only `compatibleCapellApiVersion()` and nothing else (`src/Health/MediaAIHealthCheck.php`). This is consistent platform-wide — every sibling (`ai-orchestrator`, `insights`, etc.) does the same — so the manifest entries are descriptive metadata, not runtime assertions. Risk: the manifest _reads_ like three guarantees are verified at runtime when none are. Either wire `ChecksExtensionHealth` to assert them (e.g. confirm `ImageDoctor` resolves, confirm the action hides under `NullImageDoctor`) or stop implying executable coverage. — `src/Health/MediaAIHealthCheck.php`, `capell.json:56-78` — M
- **Unfulfilled `media-ai-console` capability.** Declared in `capabilities[]` and `surfaces` (`console`) with no console code — a manifest-vs-code mismatch a marketplace reviewer will flag. — `capell.json:16,43` — S
- **No tests for the disabled path or the null result.** `tests/Feature/MediaAIEditActionTest.php` covers: extender registered, action hidden under `NullImageDoctor`, request forwarded to a bound doctor, and read-only authorization. **Not covered:** `config('capell-media-ai.enabled') === false` skips registration; `NullImageDoctor` returns the `not_configured` failure and the action shows a `warning`; operation validation. The `enabled` flag (`config/capell-media-ai.php`) has zero test coverage. — `tests/Feature/MediaAIEditActionTest.php` — S
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

| Item                                                                                                  | Bucket | Effort | Impact | Section ref |
| ----------------------------------------------------------------------------------------------------- | ------ | ------ | ------ | ----------- |
| Ship a first-party `AIOrchestratorImageDoctor` adapter so the action actually works                   | Now    | L      | High   | §2, §3      |
| Fix Boost guideline (no metadata written) + reconcile `overview.md` "replacement media / link" claims | Now    | S      | Med    | §2          |
| Server-side validate `operation` against known set                                                    | Now    | S      | Med    | §2, §4      |
| Add tests for disabled flag + NullImageDoctor warning path                                            | Now    | S      | Med    | §4          |
| Resolve manifest mismatches: empty `media-ai-console` capability, undercounted screenshots            | Now    | S      | Med    | §4, §5      |
| Move provider call to a queued job with timeout/retry                                                 | Next   | M      | High   | §2, §4      |
| Add `locale` to `ImageDoctorRequest`; document translated `message` requirement                       | Next   | S      | Med    | §3, §4      |
| Alt-text + caption generation written to `translations.meta`                                          | Next   | L      | High   | §3          |
| Add missing-alt-text signal/column to media-library `MediaHealthTable` (cross-pkg)                    | Next   | M      | High   | §3, §5      |
| Rate limit / per-site usage cap + cost/token controls on `ImageDoctorRequest`                         | Next   | M      | High   | §3, §4      |
| Batch/bulk processing (table bulk action + console command) — fulfils `media-ai-console`              | Next   | M      | High   | §3          |
| Result audit trail (operation, outcome, cost) into activity log                                       | Later  | M      | Med    | §3          |
| Moderation/safety gate on generated imagery                                                           | Later  | M      | Med    | §3          |
| Auto-tagging / keyword extraction for media search                                                    | Later  | L      | Med    | §3          |
| Rewrite marketplace summary + description; expand screenshot set; sort bundle/tier positioning        | Now    | S      | High   | §5          |
