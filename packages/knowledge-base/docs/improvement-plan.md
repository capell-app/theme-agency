# Knowledge Base — Improvement & Growth Plan

> Package: capell-app/knowledge-base · Kind: package · Tier: premium · Product group: Capell Content · Bundle: content-product · Status: Draft

## 1. Snapshot

Knowledge Base owns five tables (`knowledge_base_collections`, `knowledge_base_articles`, `knowledge_base_article_versions`, `knowledge_base_article_feedback`, `knowledge_base_related_articles`) and two Filament resources (Collections, Articles), plus three public routes under `/docs` (index, article, feedback POST). Domain logic lives in nine Actions; the public-facing ones (`BuildPublicKnowledgeBaseNavigationAction`, `BuildPublicKnowledgeBaseArticleDataAction`) feed hydrated Data objects into two thin Blade views that hold no DB queries, and the public-output safety story is well-tested. Deps are lean: `capell-app/core`, `capell-app/admin`, `lorisleiva/laravel-actions`, `spatie/laravel-data` (no Scout, no medialibrary, no translatable). Current marketplace `summary` (verbatim): _"Versioned docs content, public knowledge navigation, feedback capture, related articles, and AI-readable documentation output."_ — screenshot count: **0** (`screenshots: []`). The headline gap: the package's most differentiating capabilities (versioning, related articles, search documents, AI output) are **declared but not operable in production** — there is no Edit page in admin and no caller for the search/AI/version/relate Actions anywhere in the monorepo.

## 2. Improvements (existing functionality)

Prioritized:

1. **Add Edit pages to both Filament resources** — `KnowledgeBaseArticleResource::getPages()` and `KnowledgeBaseCollectionResource::getPages()` register only `index` + `create`. With no Edit page an author can never revise an article, create a new version, publish a draft, relate articles, or read feedback through the UI. This makes versioning, related-articles, and feedback-review capabilities unreachable for the buyer. — `src/Filament/Resources/Articles/KnowledgeBaseArticleResource.php:getPages()`, `src/Filament/Resources/Collections/KnowledgeBaseCollectionResource.php:getPages()` — **L**

2. **Wire the Edit page to `CreateKnowledgeBaseArticleVersionAction` + `PublishKnowledgeBaseArticleVersionAction`** — the create page (`CreateKnowledgeBaseArticle::handleRecordCreation`) correctly delegates to `CreateKnowledgeBaseArticleAction` which spins up `v1` and publishes. There is no equivalent edit flow, so the versioning engine that already exists in `src/Actions/` is dead in the admin surface. An Edit page should call the version + publish Actions and expose a version history relation manager. — `src/Filament/Resources/Articles/Pages/` (new `EditKnowledgeBaseArticle`) — **M**

3. **Throttle the public feedback endpoint** — `Route::post('/{collectionSlug}/{articleSlug}/feedback', ...)` runs under bare `['web']` middleware with no `throttle`. An anonymous visitor can POST unlimited helpful/not-helpful votes and 2 000-char comments. Add `->middleware('throttle:...')` and a per-`visitor_hash`+`article` dedupe check (the `knowledge_base_article_feedback_summary_index` and `visitor_hash` column already exist but nothing reads them). — `routes/web.php`, `src/Actions/RecordKnowledgeBaseArticleFeedbackAction.php` — **S**

4. **Route public requests through Capell frontend middleware** — `configurePackage()` uses `->hasRoute('web')` and `routes/web.php` applies only `['web']`. Per Capell architecture every frontend request should pass `frontend.resolve` → `frontend.cache` → `frontend.model_events`. As written, the `/docs` pages bypass site resolution, the shared HTML cache, and model-event cache invalidation entirely. — `src/Providers/KnowledgeBaseServiceProvider.php:configurePackage()`, `routes/web.php` — **M**

5. **Done/Shipped: guard article slug collisions** — `CreateKnowledgeBaseArticleAction` now checks for an existing slug in the target collection and returns a translated `ValidationException` instead of letting the database unique key surface a raw `QueryException`. Evidence: `KnowledgeBaseFoundationActionsTest` covers same-collection duplicate rejection while allowing the same slug in a different collection. — `src/Actions/CreateKnowledgeBaseArticleAction.php`, `tests/Feature/Actions/KnowledgeBaseFoundationActionsTest.php`

6. **Expose feedback signal in the admin table** — feedback is captured but invisible. Add a "helpful %" / vote-count column (the `knowledge_base_article_feedback_summary_index` index is purpose-built for this aggregate) so authors can see which articles need work. — `src/Filament/Resources/Articles/KnowledgeBaseArticleResource.php:table()` — **S**

7. **Make `relation_type` / `sort_order` editable for related articles** — `RelateKnowledgeBaseArticlesAction` supports `Prerequisite` / `NextStep` / `Related` and an order, and `BuildPublicKnowledgeBaseArticleDataAction` sorts by `sort_order`, but there is no UI to set either. A related-articles relation manager on the Edit page unlocks this. — new relation manager under `src/Filament/Resources/Articles/` — **M**

## 3. Missing Features (gaps)

Mapped to `capabilities[]` and KB-category norms. **Table-stakes** = expected of any KB product; **Differentiator** = sets it apart.

- **Full-text search (table-stakes, currently absent).** Manifest declares `knowledge-base-search-documents` + `knowledge-base-search-weighting` and `supports: capell-app/search`, but `BuildKnowledgeBaseSearchDocumentsAction` has **no caller** and nothing registers KB with Scout or the search package. There is no search box, no index sync, no relevance query. This is the single biggest gap for a KB product. — `src/Actions/BuildKnowledgeBaseSearchDocumentsAction.php`
- **AI-assisted / AI-readable output route (differentiator, half-built).** `BuildAiReadableKnowledgeBaseOutputAction` produces `AiReadableKnowledgeBaseArticleData` but is never invoked — there is no `llms.txt` / `/docs/ai` route or command exposing it. Shipping an `llms.txt`-style endpoint would deliver the advertised `knowledge-base-ai-output` capability and is a genuine differentiator. — `src/Actions/BuildAiReadableKnowledgeBaseOutputAction.php`
- **Article feedback aggregation & ratings surface (table-stakes, partial).** Raw votes are stored; no aggregate display, no "X of Y found this helpful" on the public page, no admin report.
- **Multi-locale articles (gap vs manifest claim).** `performance.cacheSafety.variesBy` lists `"locale"`, but articles store a single `body` per version with no `spatie/laravel-translatable` integration and no per-locale routing. Either implement translatable versions or drop `locale` from `variesBy`.
- **Multi-site scoping (gap vs manifest claim).** `variesBy` lists `"site"`, yet no table carries `site_id` and routes never resolve a site. KB content is global. Either add `site_id` (matching the multi-site core schema) or correct the manifest.
- **Categories/collections nesting in public output (partial).** Collections support `parent_id` (self-referential) and the admin form exposes it, but `BuildPublicKnowledgeBaseNavigationAction` renders a **flat** list — child collections are not nested on the public index. — `src/Actions/BuildPublicKnowledgeBaseNavigationAction.php`
- **Permissions / visibility (gap).** `permissions: []` and both policies return `true` for everything (see §4). No Shield permission set, no per-site/per-collection author scoping.
- **Search-within-article / table of contents, article tags, view counts, "last updated" display (table-stakes polish).** None present; the public article view shows title/summary/body/related only.
- **Install/demo/doctor commands.** `commands.{install,setup,demo,doctor}` are all `null`. A `demo` seeder (sample collections + versioned articles) would make the package demoable and back the marketplace screenshots that are currently missing.

## 4. Issues / Risks

- **Done/Shipped: real health check.** `KnowledgeBaseHealthCheck` now reports Diagnostics checks for required storage tables, Knowledge Base models, domain Actions, admin resources, and runtime/admin providers. Evidence: `tests/Feature/Health/KnowledgeBaseHealthCheckTest.php` covers the passing diagnostic set and missing-table failure mode. — `src/Health/KnowledgeBaseHealthCheck.php`
- **Manifest cache/invalidation is unwired.** `performance.cacheTags: ["knowledge-base"]`, `queueInvalidation: true`, and the `invalidationSources` model-event list imply integration with the core `CacheInvalidationRegistry` / `frontend.model_events`. No `CacheInvalidationRegistry::registerDependency()` call exists and routes skip `frontend.cache`/`frontend.model_events`. Public responses rely solely on a manual `Cache-Control: max-age=300` header in each controller — edits will serve stale docs for up to 5 minutes with no active purge. — `src/Http/Controllers/*Controller.php:cacheable()`, `src/Providers/KnowledgeBaseServiceProvider.php`
- **Dead capability surface (manifest mismatch).** `BuildKnowledgeBaseSearchDocumentsAction`, `BuildAiReadableKnowledgeBaseOutputAction`, `CreateKnowledgeBaseArticleVersionAction`, `PublishKnowledgeBaseArticleVersionAction`, `RelateKnowledgeBaseArticlesAction` have **zero callers** across the whole monorepo (verified by grep, excluding KB's own tests/manifest). None of the four `supports` packages (`search`, `seo-suite`, `theme-knowledge`, `site-discovery`) reference KB at all. Capabilities are advertised but not reachable by a buyer through any shipped surface.
- **Policies are wide-open.** `KnowledgeBaseArticlePolicy` / `KnowledgeBaseCollectionPolicy` return `true` for `viewAny/view/create/update` and `false` for `delete`, with no `User` arg, no Shield permission, no site access check. Contrast the sibling `Capell\FormBuilder\Policies\SubmissionPolicy`, which uses `ResolvesShieldPermission` + site-access abilities. Any authenticated panel user can author KB content for any tenant. — `src/Policies/KnowledgeBaseArticlePolicy.php`, `src/Policies/KnowledgeBaseCollectionPolicy.php`
- **No throttle on anonymous write endpoint** (also in §2.3) — abuse/ballot-stuffing vector. — `routes/web.php`
- **Test gaps.** Feature coverage includes public rendering safety (strong), feedback redaction, manifest shape, admin-resource registration, real health diagnostics, and article slug-collision validation. **Not covered:** the Edit flow (doesn't exist), version supersession / republish, `RelateKnowledgeBaseArticlesAction` self-relation guard is tested but `relation_type` ordering on the public page is not, and the search-document and AI-output Actions' filtering edge cases (draft/private exclusion is tested for nav but the dedicated builder Actions have only one happy-path assertion each). No Unit suite. — `tests/Feature/`
- **Performance budget unverifiable.** `frontendRenderBudgetMs: 20` / `adminQueryBudget: 40` are declared but no test or benchmark enforces them. `BuildPublicKnowledgeBaseNavigationAction` eager-loads `articles.currentVersion` (good), but the article controller eager-loads `relatedArticleLinks.relatedArticle.collection` + `.currentVersion` two levels deep on every hit with no cap on related count.
- **i18n is partial.** All admin/frontend strings are translated (`resources/lang/en/generic.php`), but the **content** is single-locale (no translatable article bodies), contradicting `variesBy: ["locale"]`.
- **Empty docs/README/CHANGELOG.** `README.md`, `docs/README.md`, and `CHANGELOG.md` are effectively empty. For a `premium` / `paid` / `priority-support` package with `privateDocsRequested: true`, there is no installation, configuration, extension, or integration documentation.

## 5. Marketplace & Selling

**Critique.** The composer `description` ("Knowledge base collections, versioned articles, feedback, and public docs discovery for Capell") and the marketplace `summary` both **lead with features that don't currently work end-to-end** (versioning, AI output, search weighting). That is a credibility risk for a paid first-party package: a buyer who installs it gets create-only authoring, a flat public docs list, and a feedback form — not the versioned, searchable, AI-ready KB the copy promises. The two strings also overlap heavily and neither states the buyer outcome. Zero screenshots compounds the problem — there is nothing to show, partly because there is no demo seeder and no Edit/search UI to screenshot.

**Improved 1-sentence summary:**

> A self-hosted help centre for Capell: organise docs into collections, publish versioned articles, capture reader feedback, and expose a clean public docs site plus AI-readable output — no theme lock-in.

**Improved 3–4 sentence description:**

> Knowledge Base turns your Capell install into a structured help centre. Authors group articles into nestable collections and publish them with full version history, while readers browse a fast, cacheable `/docs` site and tell you whether each article helped. Public navigation, article, and AI-readable payloads are emitted as typed data objects, so any theme can render them and assistants can consume them without leaking authoring internals. Pairs with the Search and SEO Suite extensions to make every article findable by humans and machines.

(Only ship this copy once §2 and §3 close the version-UI and search gaps — otherwise it oversells.)

**Screenshot/media gaps:** ship at minimum (1) the Articles list with feedback column, (2) the article Edit page with version history, (3) the public `/docs` index, (4) a public article with related articles + feedback widget, (5) the `llms.txt`/AI-output endpoint. All currently impossible to capture until the UI/routes exist — gated on roadmap "Now" items.

**Pricing / tier / bundle.** `premium` tier in the `content-product` bundle is defensible _if_ versioning + search land; as shipped it is closer to a `standard` content add-on. Keep it in `content-product` and cross-sell aggressively: declare a real integration with **`capell-app/search`** (search box + index sync) and **`capell-app/seo-suite`** (article schema.org `Article`/`FAQPage`, sitemap entries for `/docs/*`). The `theme-knowledge` package named in `supports` should ship matching public templates — today it doesn't reference KB at all, so the "no theme lock-in" promise has no reference implementation.

**Differentiators / value props:** (1) first-class **version history** with publish workflow, (2) **AI-readable output** for LLM ingestion (rare in CMS KB add-ons), (3) **privacy-preserving feedback** (visitor/IP hashed with app-key salt — already implemented and tested), (4) **theme-agnostic typed payloads** that never leak admin internals. **Target buyer:** SaaS/product teams and agencies running Capell who need a docs/help centre without standing up a separate tool (Helpscout, Zendesk Guide, GitBook).

**8–12 keywords/tags:** `knowledge-base`, `help-centre`, `documentation`, `docs-site`, `versioned-articles`, `article-feedback`, `related-articles`, `full-text-search`, `ai-readable`, `llms-txt`, `self-hosted`, `filament-cms`.

## 6. Prioritized Roadmap

| Item                                                                                        | Bucket | Effort | Impact                | Section ref |
| ------------------------------------------------------------------------------------------- | ------ | ------ | --------------------- | ----------- |
| Add Edit pages to Article + Collection resources                                            | Now    | L      | Critical              | §2.1        |
| Wire version-create + publish into the Edit flow (history relation manager)                 | Now    | M      | Critical              | §2.2, §3    |
| Throttle + dedupe the anonymous feedback endpoint                                           | Now    | S      | High (abuse)          | §2.3, §4    |
| Done/Shipped: implement a real `KnowledgeBaseHealthCheck` (models/tables/Actions/resources/providers discoverable) | Done   | S      | High (trust)          | §4          |
| Lock policies to Shield permissions + site access (match SubmissionPolicy)                  | Now    | M      | High (security)       | §4          |
| Done/Shipped: slug-collision guard in `CreateKnowledgeBaseArticleAction`                    | Done   | S      | Medium                | §2.5        |
| Route `/docs` through `frontend.resolve`/`cache`/`model_events` + register cache dependency | Next   | M      | High                  | §2.4, §4    |
| Search integration: register with `capell-app/search` + add public search box               | Next   | L      | Critical (category)   | §3          |
| Add `llms.txt`/AI-output route consuming `BuildAiReadableKnowledgeBaseOutputAction`         | Next   | M      | High (differentiator) | §3          |
| Related-articles relation manager (type + order editable)                                   | Next   | M      | Medium                | §2.7, §3    |
| Feedback aggregate column in admin + "was this helpful" stats on public page                | Next   | S      | Medium                | §2.6, §3    |
| Resolve `variesBy` mismatch: add `site_id` + translatable bodies, or correct manifest       | Next   | M      | Medium (correctness)  | §4          |
| Write README/docs/CHANGELOG + `demo` seeder command; capture screenshots                    | Next   | M      | High (sales)          | §4, §5      |
| Nest child collections in public navigation output                                          | Later  | S      | Low                   | §3          |
| SEO Suite integration (Article schema + `/docs/*` sitemap)                                  | Later  | M      | Medium (cross-sell)   | §5          |
| `theme-knowledge` reference templates for KB payloads                                       | Later  | M      | Medium (bundle)       | §5          |
