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

## 2. Improvements (existing functionality)

- **Wire moderator new-comment notifications (advertised, not implemented)** — `CommentCreated` is dispatched (`src/Actions/CreateCommentAction.php:119`) but has **no listener**, and `config('capell-comments.notifications.moderators')` (`config/capell-comments.php:32`) / the README's "notification settings" claim are never consumed. Add a queued listener that notifies the configured moderator addresses (and/or users holding the comment policy ability) when a comment lands in `PendingApproval` / `PendingEmailVerification`. — why: editors currently have zero signal that a queue is filling up; the manifest capability `comments-created-event` only emits, nothing acts. — `src/Events/CommentCreated.php`, `src/Providers/CommentsServiceProvider.php` — M

- **Shipped 2026-06-04: Make `CommentsHealthCheck` a real check** — `CommentsHealthCheck::runDiagnostics()` now reports storage-table, settings-registration, thread-route, and Livewire-component checks, and `passed()` reflects the aggregate result. Focused tests cover happy path plus missing table, settings, and route failures. — `src/Health/CommentsHealthCheck.php`, `tests/Feature/CommentsHealthCheckTest.php` — M

- **Shipped 2026-06-04: Add a honeypot + minimum-render-age check to the public form** — `thread.blade.php` now renders a hidden honeypot field, `CommentThreadComponent` tracks `formRenderedAt`, and `CreateCommentAction` rejects honeypot-filled or too-fast submissions before persistence. The minimum age is configurable at `capell-comments.spam.minimum_form_age_seconds`. — `src/Livewire/CommentThreadComponent.php`, `resources/views/livewire/thread.blade.php`, `src/Data/CreateCommentData.php` — S

- **Paginate / lazy-load replies and root comments** — `BuildPublicThreadAction` loads all roots up to `rootLimit` (default 20) and then _all_ descendants in one query, building the full tree (`src/Actions/BuildPublicThreadAction.php:35-89`). `reply_page_size` exists in config/settings (`config/capell-comments.php:20`, `src/Settings/CommentSettings.php:33`) but is **never used**. — why: a popular thread blows the 20ms `frontendRenderBudgetMs` and returns unbounded payloads; the advertised reply paging is dead. Add "load more replies" / root cursor pagination honoring `reply_page_size`. — `src/Actions/BuildPublicThreadAction.php`, `src/Livewire/CommentThreadComponent.php:111` — M

- **Surface validation errors per-field on submit** — `thread.blade.php` shows a generic "validation_failed" banner (`:9-13`) plus per-field `@error` blocks, but `CreateCommentAction` throws `ValidationException` from inside `CreateCommentAction::run()` called in `submit()` without Livewire `$this->validate()` wiring, so messages may not bind to the component's `$errors` bag reliably. — why: guests get an opaque failure. Catch `ValidationException` in `submit()` and map to `addError()`, or move validation into the component rules. — `src/Livewire/CommentThreadComponent.php:61-87` — S

- **Stop double-querying the commentable on every Livewire action** — `resolveCommentable()` decrypts the thread key and re-`find()`s the model on `mount`, every `submit`, and every `refreshComments` (`src/Livewire/CommentThreadComponent.php:127`). — why: each round trip re-runs a DB lookup; memoize the resolved model per request. — `src/Livewire/CommentThreadComponent.php` — S

- **Shipped 2026-06-03: Index/labels parity in admin** — `LatestCommentsWidget` is registered at runtime and declared in `capell.json` `contributes[]`, with manifest coverage proving every dashboard widget stays discoverable by marketplace and Diagnostics tooling. — `capell.json`, `tests/Unit/ManifestRequirementsTest.php` — S

## 3. Missing Features (gaps)

Manifest `capabilities[]` = `comments`, `comments-admin`, `comments-frontend`, `comments-created-event` — these are structurally present. The gaps below are category table-stakes that are advertised in config/README but not built, plus differentiators.

- **Shipped 2026-06-04: Spam scoring (table-stakes, advertised).** `ScoreCommentSpamAction` reads `config('capell-comments.spam.max_links')` and `spam.blocked_terms`, returns `CommentSpamScoreData`, and `CreateCommentAction` stores `spam_reasons`, sets `CommentStatus::Spam`, stamps `marked_spam_at`, and skips verification-token creation for flagged submissions. Focused coverage proves direct scoring and create-flow persistence. — `src/Actions/ScoreCommentSpamAction.php`, `src/Data/CommentSpamScoreData.php`, `src/Actions/CreateCommentAction.php`, `tests/Integration/Actions/CreateCommentActionTest.php`

- **Shipped 2026-06-04: Rate-limit hardening (anti-abuse).** The primary public submit throttle key now uses commentable type, commentable ID, and requester IP only, so changing `$this->authorEmail` cannot reset the primary bucket. Focused Livewire coverage proves a second submission to the same thread/IP is throttled even when the email changes. — `src/Livewire/CommentThreadComponent.php`, `tests/Integration/CommentThreadComponentTest.php`

- **Moderator notifications & digests (advertised).** See §2 — no new-comment notification exists; only the author email-verification mail is wired (`src/Notifications/ConfirmCommentAuthorEmailNotification.php`). A daily/instant moderation digest is a natural premium differentiator and the `supports: capell-app/email-studio` dependency is the intended vehicle.

- **Reactions / voting (differentiator).** No upvote/like/reaction model. A `comment_reactions` table + aggregate counts on `PublicCommentData` would differentiate against bundled-CMS comment add-ons and is a common engagement lever for the "Capell Engagement" group.

- **Author reply notifications (differentiator).** When a reply is approved under a parent, the parent's verified author is never notified. Tokens infrastructure (`comment_tokens`) already supports one-click unsubscribe. Drives re-engagement.

- **Edit / delete window for authors (table-stakes-ish).** Authenticated/verified authors cannot edit or soft-delete their own comments from the frontend; only admins transition status. A short author edit window is expected by most comment systems.

- **Akismet / external spam provider hook (differentiator).** A pluggable spam-provider contract (Akismet, reCAPTCHA/Turnstile) layered over the local heuristics would be a strong premium up-sell and pairs with the honeypot in §2.

## 4. Issues / Risks

- **Health check coverage shipped.** `src/Health/CommentsHealthCheck.php` now exposes real Diagnostics results for the critical package-health declaration in `capell.json`. Residual risk: these checks cover package wiring, not spam scoring, notifications, or public render budgets.

- **Local spam scoring shipped.** `spam.max_links`, `spam.blocked_terms`, the `spam_reasons` column, and `linkCount()` are now enforced during `CreateCommentAction`. Residual risk: the heuristic remains intentionally local and simple; external spam providers, CAPTCHA/Turnstile, richer link detection, and moderator notification workflows remain separate roadmap items.

- **Throttle bypass via email field shipped.** The public submit throttle no longer includes attacker-controlled author email in the primary bucket. Residual risk: there is not yet a secondary per-author-email cap or broader abuse telemetry.

- **Public render performance budget unverifiable / at risk.** Manifest sets `frontendRenderBudgetMs: 20` and `adminQueryBudget: 40`, but `BuildPublicThreadAction` fetches the entire approved subtree with no depth/reply cap beyond `rootLimit` (`src/Actions/BuildPublicThreadAction.php:65-81`). No test or benchmark asserts either budget. On a hot thread this exceeds 20ms and the budget is effectively aspirational.

- **XSS / sanitization — low residual but worth hardening.** Body is `strip_tags` + whitespace-collapsed, capped at 5000 chars (`src/Support/CommentBodySanitizer.php`), and output is Blade-escaped via `{{ }}` (`resources/views/livewire/thread.blade.php:27,50`), and `PublicCommentData` omits all sensitive fields — so stored-XSS surface is low. Residual: the sanitizer does not strip Unicode bidi/zero-width/control characters (spoofing) and `linkCount()` only counts `http(s)://` (misses `www.`/bare domains), weakening any future link-based spam rule. `src/Support/CommentBodySanitizer.php:11-25`.

- **Public-output safety — overall good, two notes.** `BuildPublicThreadAction::toData()` correctly emits only public id, sanitized body, display name, timestamp, depth, reply count, children — no status, model id, email, hashes, tokens, or admin URL (matches the README Public Safety contract). The thread endpoint sets `Cache-Control: no-store, private` (`src/Http/Controllers/RenderCommentThreadController.php:20`). Notes: (1) public Blade does not query the DB — data is hydrated by the Action ✔; (2) `auto_inject` injects `thread-shell` with the encrypted `threadKey` into cached page HTML (`src/Providers/FrontendServiceProvider.php:92`) — the key is `Crypt::encryptString` of type/id/site/lang, opaque and safe, but there is no test proving an anonymous, cached page never leaks the decrypted payload or moderation state. Add an explicit anonymous-leakage test for the `auto_inject` path.

- **PII / retention.** Author `name`/`email` are `encrypted` at rest ✔ and `email_hash` is HMAC ✔ (`src/Models/CommentAuthor.php:50-58,123`). Gaps: (1) `email_hash_secret`/`visitor_hash_secret` default to `null` from env and silently fall back to `app.key` (`config/capell-comments.php:22-23`, `src/Support/VisitorHasher.php:15`, `CommentAuthor::emailHash`) — rotating `app.key` orphans all hashes; document and warn. (2) No retention/erasure policy or command for visitor IP/UA hashes or author records (GDPR right-to-erasure). (3) `internal_notes` and `moderation_note` are plaintext.

- **Test gaps.** Coverage now includes health diagnostics, spam scoring, public submit throttling, and bot-trap rejection at action and Livewire levels. Still not covered: moderator notification (feature absent), `auto_inject` anonymous-leakage, reply pagination, performance budgets, and there are **no Architecture tests** for this package.

- **i18n.** Strings are translated via `capell-comments::` namespaces ✔. `diffForHumans()` in the public Blade (`thread.blade.php:24,47`) is not locale-pinned to the site language and may render in the app locale rather than the page's `language_id`.

- **Manifest/README mismatches (low sev).** README "Best Used With" lists `html-cache` but `capell.json`/`composer.json` `supports` lists only `blog` + `email-studio`. Marketplace `screenshots[]` (1) ≠ `screenshots.json` required runtime captures (4).

## 5. Marketplace & Selling

**Critique.** The marketplace `summary` and the composer `description` are now more specific about cache-safe public rendering, encrypted author records, and per-site moderation controls. The remaining weak spot is operational proof: moderator notifications still are not wired, and only 1 marketing screenshot is declared despite 4 high-value runtime captures already specified in `screenshots.json` (moderation inbox and a real public thread are the money shots).

**Improved 1-sentence summary:** "Add moderated, threaded discussion to any Capell page or article — with cache-safe public rendering, encrypted author records, and per-site moderation controls, no custom code required."

**Improved 3–4 sentence description:** "Comments lets site owners open public, threaded discussion on pages and Blog articles while editors keep full control from a dedicated moderation inbox with author records, status transitions, and dashboard widgets. Threads load after the page via a private, no-store endpoint, so they never contaminate cached HTML or leak moderation state to anonymous visitors. Author identities are encrypted at rest and matched by HMAC, with optional email verification, guest-vs-authenticated identity modes, and configurable approval, throttling, and depth limits per site or content type. Developers register a commentable type once and the package resolves public-safe thread data for the frontend."

**Media gaps:** wire the 4 `screenshots.json` captures into `capell.json.marketplace.screenshots`; add a short GIF of the moderation approve/reject flow and a before/after of a cached page with comments loading in.

**Pricing / tier / bundle positioning:** `tier: premium`, `bundle: comments`, `proposedLicense: paid`, `requestedCertification: first-party`, `supportPolicy: priority`. Reasonable, but to _hold_ premium it must close the moderator notification gap — a "moderated comments" product that can auto-flag spam but cannot alert moderators is still hard to defend at premium against bundled CMS comment add-ons. Cross-sell paths already in deps/manifest: **Blog** (`supports`) for article discussion — lead with this in the listing; **Email Studio** (`supports`) for richer moderator/author notification templates — make this the up-sell once §3 notifications ship; **HTML Cache** (README "Best Used With") — position the no-store design as the reason these two coexist safely. Extension-suite angle: package with Blog + Email Studio as an "Engagement Suite."

**Differentiators / value props:** cache-safe post-load rendering; encrypted PII + HMAC dedup; per-site & per-commentable-type setting overrides; verification flows (verify-then-moderate / moderate-then-verify); soft-delete + full moderation audit trail (`comment_moderation_events`). **Target buyer:** content/marketing teams on Capell (esp. Blog users) who want managed discussion without standing up Disqus/a third party and without breaking page caching.

**Keywords/tags (8–12):** comments, discussion, threaded-comments, moderation, engagement, blog-comments, spam-filtering, email-verification, livewire, cache-safe, filament, capell.

## 6. Prioritized Roadmap

| Item                                                                                          | Bucket | Effort | Impact | Section ref |
| --------------------------------------------------------------------------------------------- | ------ | ------ | ------ | ----------- |
| Shipped 2026-06-04: implement real `CommentsHealthCheck` (tables, settings, route, component) | Now    | M      | High   | §2, §4      |
| Shipped 2026-06-04: implement automatic spam scoring (`max_links`, `blocked_terms`, write `spam_reasons`) | Now    | M      | High   | §3, §4      |
| Shipped 2026-06-04: fix throttle key (drop attacker-controlled email from primary bucket)     | Now    | S      | High   | §3, §4      |
| Wire moderator new-comment notification listener on `CommentCreated`                          | Now    | M      | High   | §2, §3      |
| Add `auto_inject` anonymous-leakage + throttle Pest tests; add Arch tests                     | Now    | M      | High   | §4          |
| Shipped 2026-06-03: add `LatestCommentsWidget` to `capell.json` contributes[]                 | Now    | S      | Med    | §2, §4      |
| Wire the 4 `screenshots.json` captures into marketplace + new summary/description             | Now    | S      | Med    | §5          |
| Implement reply pagination using `reply_page_size`; cap subtree query                         | Next   | M      | High   | §2, §3      |
| Benchmark + assert `frontendRenderBudgetMs`/`adminQueryBudget`                                | Next   | M      | Med    | §4          |
| Harden sanitizer (bidi/zero-width strip; broaden link detection)                              | Next   | S      | Med    | §4          |
| Memoize resolved commentable in Livewire component                                            | Next   | S      | Low    | §2          |
| Locale-pin public timestamps to page `language_id`                                            | Next   | S      | Low    | §4          |
| Pluggable external spam provider (Akismet/Turnstile) contract                                 | Later  | M      | Med    | §3          |
| Reactions/voting + author-reply notifications (Engagement Suite up-sell)                      | Later  | L      | Med    | §3, §5      |
| PII retention/erasure command + secret-rotation docs for hashes                               | Later  | M      | Med    | §4          |
