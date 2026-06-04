# Comments — Improvement & Growth Plan

> Package: capell-app/comments · Kind: package · Tier: premium · Product group: Capell Engagement · Bundle: comments · Status: Draft

## 1. Snapshot

Comments adds moderated, threaded, dynamically-loaded discussion to registered Capell content (pages and, when Blog is installed, articles). It exposes two surfaces: an **admin** moderation experience (`CommentModerationInbox` page, `CommentResource` + `CommentAuthorResource`, `CommentStatsWidget`, `LatestCommentsWidget`) and a **frontend** Livewire component (`CommentThreadComponent`) loaded post-page via a no-store endpoint (`RenderCommentThreadController` → route `capell-comments.thread`). Key Actions are `CreateCommentAction`, `BuildPublicThreadAction` / `ResolvePublicCommentableThreadAction`, `TransitionCommentStatusAction`, `RequestCommentEmailVerificationAction` / `VerifyCommentAuthorEmailAction`, and `RegisterDefaultCommentablesAction`. Models/tables: `comments`, `comment_authors`, `comment_tokens`, `comment_moderation_events` (all registered as protected tables, all soft-delete on `comments`). Deps: `capell-app/{core,admin,frontend}`, Filament, Livewire, `lorisleiva/laravel-actions`, `spatie/laravel-data`, `spatie/laravel-settings`. Author `name`/`email` are `encrypted` casts; email is also stored as an HMAC `email_hash` for lookup.

Current marketplace `summary` (verbatim): _"Comments adds moderated, configurable, cache-safe threaded discussion surfaces to Capell content."_ Screenshots declared in the manifest: **1** (`docs/assets/marketplace/extension-card.jpg`). Note the mismatch: `docs/screenshots.json` defines **4** required runtime screenshots (moderation inbox, comments resource, authors resource, public thread) that the marketplace `screenshots[]` array does not reference — the marketplace block ships only the generic extension card.

## Completed Improvement Slices

- **2026-06-03:** Rewrote marketplace/Composer copy, declared `LatestCommentsWidget` in manifest contributions, and added manifest coverage for registered dashboard widgets.
- **2026-06-04:** Added public comment form bot-trap controls: a hidden honeypot field, configurable minimum form age, action-level rejection before persistence, component state reset, and action/Livewire tests.
- **2026-06-04:** Implemented real `CommentsHealthCheck` diagnostics for required storage tables, settings registration, the public thread route, and the public thread Livewire component, with focused failure-mode tests.
- **2026-06-04:** Implemented automatic local spam scoring for configured link-count and blocked-term rules, storing `spam_reasons`, marking flagged submissions as `Spam`, and skipping verification tokens for auto-spam comments.
- **2026-06-04:** Hardened the public submit throttle key to use commentable + IP data without attacker-controlled author email, with Livewire regression coverage.
- **2026-06-04:** Wired queued moderator notifications for configured moderator email addresses when new comments enter pending approval or pending email verification, with listener registration and status-gating tests.
- **2026-06-04:** Added auto-inject anonymous-leakage coverage for the real render-hook path plus package Arch tests for strict equality, public-runtime admin/authoring isolation, and public Blade database/authoring-marker guards.
- **2026-06-04:** Implemented settings-aware reply pagination with per-parent "load more replies" support, bounded child hydration, recursive public rendering, and Livewire-safe public comment DTO serialization.
- **2026-06-04:** Hardened public body sanitization by stripping invisible Unicode format controls and ASCII control bytes, and broadened spam link detection to count scheme, `www.`, and bare-domain links without counting email domains.
- **2026-06-04:** Memoized the resolved public commentable model within each Livewire request, avoiding duplicate page lookups during submit/refresh flows without serializing Eloquent models into Livewire state.
- **2026-06-04:** Locale-pinned public timestamp labels to the resolved commentable language so `diffForHumans()` output follows page `language_id` instead of the ambient app locale, with Livewire serialization coverage.
- **2026-06-04:** Added enforceable performance-budget coverage for public thread hydration/rendering and admin comment widgets, and batched sibling reply counts to avoid empty-grandchild query fanout.

## 2. Improvements (existing functionality)

- **Shipped 2026-06-04: Wire moderator new-comment notifications** — `CommentCreated` now has a queued `NotifyModeratorsOfNewComment` listener registered by `CommentsServiceProvider`. It notifies valid, de-duplicated addresses from `config('capell-comments.notifications.moderators')` when a comment lands in `PendingApproval` or `PendingEmailVerification`, and skips spam/approved comments. — `src/Events/CommentCreated.php`, `src/Listeners/NotifyModeratorsOfNewComment.php`, `src/Notifications/ModerateCommentNotification.php`, `src/Providers/CommentsServiceProvider.php`, `tests/Feature/ModeratorNotificationTest.php` — M

- **Shipped 2026-06-04: Make `CommentsHealthCheck` a real check** — `CommentsHealthCheck::runDiagnostics()` now reports storage-table, settings-registration, thread-route, and Livewire-component checks, and `passed()` reflects the aggregate result. Focused tests cover happy path plus missing table, settings, and route failures. — `src/Health/CommentsHealthCheck.php`, `tests/Feature/CommentsHealthCheckTest.php` — M

- **Shipped 2026-06-04: Add a honeypot + minimum-render-age check to the public form** — `thread.blade.php` now renders a hidden honeypot field, `CommentThreadComponent` tracks `formRenderedAt`, and `CreateCommentAction` rejects honeypot-filled or too-fast submissions before persistence. The minimum age is configurable at `capell-comments.spam.minimum_form_age_seconds`. — `src/Livewire/CommentThreadComponent.php`, `resources/views/livewire/thread.blade.php`, `src/Data/CreateCommentData.php` — S

- **Shipped 2026-06-04: Paginate / lazy-load replies** — `BuildPublicThreadAction` now resolves root and reply page sizes through `CommentSettingsResolver`, hydrates each parent with a bounded child page, exposes `replyCount` / `hasMoreReplies`, and accepts per-public-id reply limits. `CommentThreadComponent` tracks per-parent reply limits and exposes `loadMoreReplies()`, while the public Blade now renders nested comments recursively with a translated load-more action. — `src/Actions/BuildPublicThreadAction.php`, `src/Livewire/CommentThreadComponent.php`, `src/Data/PublicCommentData.php`, `resources/views/livewire/partials/comment-list.blade.php`, `tests/Integration/Actions/PublicThreadActionTest.php`, `tests/Integration/CommentThreadComponentTest.php` — M

- **Surface validation errors per-field on submit** — `thread.blade.php` shows a generic "validation_failed" banner (`:9-13`) plus per-field `@error` blocks, but `CreateCommentAction` throws `ValidationException` from inside `CreateCommentAction::run()` called in `submit()` without Livewire `$this->validate()` wiring, so messages may not bind to the component's `$errors` bag reliably. — why: guests get an opaque failure. Catch `ValidationException` in `submit()` and map to `addError()`, or move validation into the component rules. — `src/Livewire/CommentThreadComponent.php:61-87` — S

- **Shipped 2026-06-04: Stop double-querying the commentable on every Livewire action** — `CommentThreadComponent` now keeps the decrypted/resolved model in private request-local properties and resets that cache on Livewire hydration. Submit flows reuse the same model for `CreateCommentAction` and the post-submit `refreshComments()` call, with query-count coverage proving the duplicate page lookup is gone. — `src/Livewire/CommentThreadComponent.php`, `tests/Integration/CommentThreadComponentTest.php` — S

- **Shipped 2026-06-04: Enforce public/admin performance budgets** — `BuildPublicThreadAction` now batches approved child-count lookups for each sibling group and skips empty child fetches, removing the old per-comment empty-grandchild query fanout. `PerformanceBudgetTest` seeds a bounded hot public thread, asserts hydration stays at or below 20 queries, and checks warmed Blade rendering against `capell.json` `frontendRenderBudgetMs: 20`; `CommentsAdminSurfaceTest` asserts the stats/latest-comments widgets stay under `adminQueryBudget: 40`. — `src/Actions/BuildPublicThreadAction.php`, `tests/Integration/PerformanceBudgetTest.php`, `tests/Integration/Filament/CommentsAdminSurfaceTest.php` — M

- **Shipped 2026-06-03: Index/labels parity in admin** — `LatestCommentsWidget` is registered at runtime and declared in `capell.json` `contributes[]`, with manifest coverage proving every dashboard widget stays discoverable by marketplace and Diagnostics tooling. — `capell.json`, `tests/Unit/ManifestRequirementsTest.php` — S

## 3. Missing Features (gaps)

Manifest `capabilities[]` = `comments`, `comments-admin`, `comments-frontend`, `comments-created-event` — these are structurally present. The gaps below are category table-stakes that are advertised in config/README but not built, plus differentiators.

- **Shipped 2026-06-04: Spam scoring (table-stakes, advertised).** `ScoreCommentSpamAction` reads `config('capell-comments.spam.max_links')` and `spam.blocked_terms`, returns `CommentSpamScoreData`, and `CreateCommentAction` stores `spam_reasons`, sets `CommentStatus::Spam`, stamps `marked_spam_at`, and skips verification-token creation for flagged submissions. Focused coverage proves direct scoring and create-flow persistence. — `src/Actions/ScoreCommentSpamAction.php`, `src/Data/CommentSpamScoreData.php`, `src/Actions/CreateCommentAction.php`, `tests/Integration/Actions/CreateCommentActionTest.php`

- **Shipped 2026-06-04: Rate-limit hardening (anti-abuse).** The primary public submit throttle key now uses commentable type, commentable ID, and requester IP only, so changing `$this->authorEmail` cannot reset the primary bucket. Focused Livewire coverage proves a second submission to the same thread/IP is throttled even when the email changes. — `src/Livewire/CommentThreadComponent.php`, `tests/Integration/CommentThreadComponentTest.php`

- **Moderator digests / richer templates.** Instant configured-address moderator notifications are shipped. A daily moderation digest, role-based moderator discovery, and Email Studio-backed templates remain natural premium differentiators and the `supports: capell-app/email-studio` dependency is the intended vehicle.

- **Reactions / voting (differentiator).** No upvote/like/reaction model. A `comment_reactions` table + aggregate counts on `PublicCommentData` would differentiate against bundled-CMS comment add-ons and is a common engagement lever for the "Capell Engagement" group.

- **Author reply notifications (differentiator).** When a reply is approved under a parent, the parent's verified author is never notified. Tokens infrastructure (`comment_tokens`) already supports one-click unsubscribe. Drives re-engagement.

- **Edit / delete window for authors (table-stakes-ish).** Authenticated/verified authors cannot edit or soft-delete their own comments from the frontend; only admins transition status. A short author edit window is expected by most comment systems.

- **Akismet / external spam provider hook (differentiator).** A pluggable spam-provider contract (Akismet, reCAPTCHA/Turnstile) layered over the local heuristics would be a strong premium up-sell and pairs with the honeypot in §2.

## 4. Issues / Risks

- **Health check coverage shipped.** `src/Health/CommentsHealthCheck.php` now exposes real Diagnostics results for the critical package-health declaration in `capell.json`. Residual risk: these checks cover package wiring, not spam scoring, notifications, or public render budgets.

- **Local spam scoring shipped.** `spam.max_links`, `spam.blocked_terms`, the `spam_reasons` column, and `linkCount()` are now enforced during `CreateCommentAction`. Link detection now covers `http(s)://`, `www.`, and bare-domain links while avoiding email-domain false positives. Residual risk: the heuristic remains intentionally local and simple; external spam providers, CAPTCHA/Turnstile, and richer reputation scoring remain separate roadmap items.

- **Throttle bypass via email field shipped.** The public submit throttle no longer includes attacker-controlled author email in the primary bucket. Residual risk: there is not yet a secondary per-author-email cap or broader abuse telemetry.

- **Performance budgets shipped.** Manifest budgets are now enforced by focused tests: a seeded public thread checks bounded hydration query count plus warmed Blade render time against `frontendRenderBudgetMs: 20`, and admin stats/latest-comments widget queries are checked against `adminQueryBudget: 40`. Residual risk: these are deterministic package-level guards, not production load tests across every host theme.

- **XSS / sanitization hardening shipped.** Body is `strip_tags` + whitespace-collapsed, capped at 5000 chars, and now strips Unicode format controls plus ASCII control bytes before storage (`src/Support/CommentBodySanitizer.php`). Output is Blade-escaped via `{{ }}` and `PublicCommentData` omits sensitive fields, so stored-XSS and text-spoofing surface is low. Focused coverage proves bidi/zero-width stripping and broader link counting.

- **Public-output safety coverage shipped.** `BuildPublicThreadAction::toData()` correctly emits only public id, sanitized body, display name, timestamp, depth, reply count, children — no status, model id, email, hashes, tokens, or admin URL (matches the README Public Safety contract). The thread endpoint sets `Cache-Control: no-store, private` (`src/Http/Controllers/RenderCommentThreadController.php:20`). `AutoInjectRenderHookTest` now exercises the real `RenderHookRegistry` auto-inject path and proves the cached shell omits comment bodies, author PII, model identifiers, moderation state, Livewire snapshots, and admin URLs. `CommentsBoundaryTest` adds package Arch coverage plus public Blade guards against database access and authoring markers.

- **PII / retention.** Author `name`/`email` are `encrypted` at rest ✔ and `email_hash` is HMAC ✔ (`src/Models/CommentAuthor.php:50-58,123`). Gaps: (1) `email_hash_secret`/`visitor_hash_secret` default to `null` from env and silently fall back to `app.key` (`config/capell-comments.php:22-23`, `src/Support/VisitorHasher.php:15`, `CommentAuthor::emailHash`) — rotating `app.key` orphans all hashes; document and warn. (2) No retention/erasure policy or command for visitor IP/UA hashes or author records (GDPR right-to-erasure). (3) `internal_notes` and `moderation_note` are plaintext.

- **Test gaps.** Coverage now includes health diagnostics, spam scoring, sanitizer hardening, moderator notification wiring/status-gating, `auto_inject` anonymous-leakage, reply pagination, performance budgets, public-output Architecture guards, Livewire commentable memoization, locale-pinned public timestamps, public submit throttling, and bot-trap rejection at action and Livewire levels.

- **i18n shipped.** Strings are translated via `capell-comments::` namespaces ✔. Public timestamp labels are now produced by `BuildPublicThreadAction` with the commentable language locale, serialized through `PublicCommentData`, and rendered as preformatted labels in Blade so Livewire hydration does not fall back to the ambient app locale.

- **Manifest/README mismatches (low sev).** README "Best Used With" lists `html-cache` but `capell.json`/`composer.json` `supports` lists only `blog` + `email-studio`. Marketplace `screenshots[]` (1) ≠ `screenshots.json` required runtime captures (4).

## 5. Marketplace & Selling

**Critique.** The marketplace `summary` and the composer `description` are now more specific about cache-safe public rendering, encrypted author records, and per-site moderation controls. The remaining weak spot is operational proof: only 1 marketing screenshot is declared despite 4 high-value runtime captures already specified in `screenshots.json` (moderation inbox and a real public thread are the money shots).

**Improved 1-sentence summary:** "Add moderated, threaded discussion to any Capell page or article — with cache-safe public rendering, encrypted author records, and per-site moderation controls, no custom code required."

**Improved 3–4 sentence description:** "Comments lets site owners open public, threaded discussion on pages and Blog articles while editors keep full control from a dedicated moderation inbox with author records, status transitions, and dashboard widgets. Threads load after the page via a private, no-store endpoint, so they never contaminate cached HTML or leak moderation state to anonymous visitors. Author identities are encrypted at rest and matched by HMAC, with optional email verification, guest-vs-authenticated identity modes, and configurable approval, throttling, and depth limits per site or content type. Developers register a commentable type once and the package resolves public-safe thread data for the frontend."

**Media gaps:** wire the 4 `screenshots.json` captures into `capell.json.marketplace.screenshots`; add a short GIF of the moderation approve/reject flow and a before/after of a cached page with comments loading in.

**Pricing / tier / bundle positioning:** `tier: premium`, `bundle: comments`, `proposedLicense: paid`, `requestedCertification: first-party`, `supportPolicy: priority`. The package now has the baseline premium moderation loop: spam scoring plus instant configured moderator notifications. Cross-sell paths already in deps/manifest: **Blog** (`supports`) for article discussion — lead with this in the listing; **Email Studio** (`supports`) for richer moderator/author notification templates and digests; **HTML Cache** (README "Best Used With") — position the no-store design as the reason these two coexist safely. Extension-suite angle: package with Blog + Email Studio as an "Engagement Suite."

**Differentiators / value props:** cache-safe post-load rendering; encrypted PII + HMAC dedup; per-site & per-commentable-type setting overrides; verification flows (verify-then-moderate / moderate-then-verify); soft-delete + full moderation audit trail (`comment_moderation_events`). **Target buyer:** content/marketing teams on Capell (esp. Blog users) who want managed discussion without standing up Disqus/a third party and without breaking page caching.

**Keywords/tags (8–12):** comments, discussion, threaded-comments, moderation, engagement, blog-comments, spam-filtering, email-verification, livewire, cache-safe, filament, capell.

## 6. Prioritized Roadmap

| Item                                                                                                      | Bucket | Effort | Impact | Section ref |
| --------------------------------------------------------------------------------------------------------- | ------ | ------ | ------ | ----------- |
| Shipped 2026-06-04: implement real `CommentsHealthCheck` (tables, settings, route, component)             | Now    | M      | High   | §2, §4      |
| Shipped 2026-06-04: implement automatic spam scoring (`max_links`, `blocked_terms`, write `spam_reasons`) | Now    | M      | High   | §3, §4      |
| Shipped 2026-06-04: fix throttle key (drop attacker-controlled email from primary bucket)                 | Now    | S      | High   | §3, §4      |
| Shipped 2026-06-04: wire moderator new-comment notification listener on `CommentCreated`                  | Now    | M      | High   | §2, §3      |
| Shipped 2026-06-04: add `auto_inject` anonymous-leakage tests and Arch tests                              | Now    | M      | High   | §4          |
| Shipped 2026-06-03: add `LatestCommentsWidget` to `capell.json` contributes[]                             | Now    | S      | Med    | §2, §4      |
| Wire the 4 `screenshots.json` captures into marketplace + new summary/description                         | Now    | S      | Med    | §5          |
| Shipped 2026-06-04: implement reply pagination using `reply_page_size`; cap subtree query                 | Next   | M      | High   | §2, §3      |
| Shipped 2026-06-04: benchmark + assert `frontendRenderBudgetMs`/`adminQueryBudget`                        | Next   | M      | Med    | §4          |
| Shipped 2026-06-04: harden sanitizer (bidi/zero-width strip; broaden link detection)                      | Next   | S      | Med    | §4          |
| Shipped 2026-06-04: memoize resolved commentable in Livewire component                                    | Next   | S      | Low    | §2          |
| Shipped 2026-06-04: locale-pin public timestamps to page `language_id`                                    | Next   | S      | Low    | §4          |
| Pluggable external spam provider (Akismet/Turnstile) contract                                             | Later  | M      | Med    | §3          |
| Reactions/voting + author-reply notifications (Engagement Suite up-sell)                                  | Later  | L      | Med    | §3, §5      |
| PII retention/erasure command + secret-rotation docs for hashes                                           | Later  | M      | Med    | §4          |
