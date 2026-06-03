# Comments — Improvement & Growth Plan

> Package: capell-app/comments · Kind: package · Tier: premium · Product group: Capell Engagement · Bundle: comments · Status: Draft

## 1. Snapshot

Comments adds moderated, threaded, dynamically-loaded discussion to registered Capell content (pages and, when Blog is installed, articles). It exposes two surfaces: an **admin** moderation experience (`CommentModerationInbox` page, `CommentResource` + `CommentAuthorResource`, `CommentStatsWidget`, `LatestCommentsWidget`) and a **frontend** Livewire component (`CommentThreadComponent`) loaded post-page via a no-store endpoint (`RenderCommentThreadController` → route `capell-comments.thread`). Key Actions are `CreateCommentAction`, `BuildPublicThreadAction` / `ResolvePublicCommentableThreadAction`, `TransitionCommentStatusAction`, `RequestCommentEmailVerificationAction` / `VerifyCommentAuthorEmailAction`, and `RegisterDefaultCommentablesAction`. Models/tables: `comments`, `comment_authors`, `comment_tokens`, `comment_moderation_events` (all registered as protected tables, all soft-delete on `comments`). Deps: `capell-app/{core,admin,frontend}`, Filament, Livewire, `lorisleiva/laravel-actions`, `spatie/laravel-data`, `spatie/laravel-settings`. Author `name`/`email` are `encrypted` casts; email is also stored as an HMAC `email_hash` for lookup.

Current marketplace `summary` (verbatim): _"Comments adds moderated, configurable, cache-safe threaded discussion surfaces to Capell content."_ Screenshots declared in the manifest: **1** (`docs/assets/marketplace/extension-card.jpg`). Note the mismatch: `docs/screenshots.json` defines **4** required runtime screenshots (moderation inbox, comments resource, authors resource, public thread) that the marketplace `screenshots[]` array does not reference — the marketplace block ships only the generic extension card.

## 2. Improvements (existing functionality)

- **Wire moderator new-comment notifications (advertised, not implemented)** — `CommentCreated` is dispatched (`src/Actions/CreateCommentAction.php:119`) but has **no listener**, and `config('capell-comments.notifications.moderators')` (`config/capell-comments.php:32`) / the README's "notification settings" claim are never consumed. Add a queued listener that notifies the configured moderator addresses (and/or users holding the comment policy ability) when a comment lands in `PendingApproval` / `PendingEmailVerification`. — why: editors currently have zero signal that a queue is filling up; the manifest capability `comments-created-event` only emits, nothing acts. — `src/Events/CommentCreated.php`, `src/Providers/CommentsServiceProvider.php` — M

- **Make `CommentsHealthCheck` a real check** — `src/Health/CommentsHealthCheck.php` implements only `compatibleCapellApiVersion()`; it has no `check()`/health method, yet `capell.json` advertises it as `severity: "critical"` with the label "package surfaces, providers, and moderation health are discoverable by Diagnostics." — why: a critical health check that returns nothing is a false green in Diagnostics. It should assert the four required tables exist, settings resolve, the thread route is registered, and the Livewire component is bound. — `src/Health/CommentsHealthCheck.php`, `capell.json` — M

- **Add a honeypot + minimum-render-age check to the public form** — `resources/views/livewire/thread.blade.php` and `CommentThreadComponent::submit()` accept name/email/body with no bot trap. — why: this is the single cheapest, highest-yield anti-spam control for guest comment forms and is expected of the category. Add a non-displayed honeypot field plus a "form rendered at" timestamp, both validated in `CreateCommentData`/`CreateCommentAction`. — `src/Livewire/CommentThreadComponent.php:61`, `resources/views/livewire/thread.blade.php:67`, `src/Data/CreateCommentData.php` — S

- **Paginate / lazy-load replies and root comments** — `BuildPublicThreadAction` loads all roots up to `rootLimit` (default 20) and then _all_ descendants in one query, building the full tree (`src/Actions/BuildPublicThreadAction.php:35-89`). `reply_page_size` exists in config/settings (`config/capell-comments.php:20`, `src/Settings/CommentSettings.php:33`) but is **never used**. — why: a popular thread blows the 20ms `frontendRenderBudgetMs` and returns unbounded payloads; the advertised reply paging is dead. Add "load more replies" / root cursor pagination honoring `reply_page_size`. — `src/Actions/BuildPublicThreadAction.php`, `src/Livewire/CommentThreadComponent.php:111` — M

- **Surface validation errors per-field on submit** — `thread.blade.php` shows a generic "validation_failed" banner (`:9-13`) plus per-field `@error` blocks, but `CreateCommentAction` throws `ValidationException` from inside `CreateCommentAction::run()` called in `submit()` without Livewire `$this->validate()` wiring, so messages may not bind to the component's `$errors` bag reliably. — why: guests get an opaque failure. Catch `ValidationException` in `submit()` and map to `addError()`, or move validation into the component rules. — `src/Livewire/CommentThreadComponent.php:61-87` — S

- **Stop double-querying the commentable on every Livewire action** — `resolveCommentable()` decrypts the thread key and re-`find()`s the model on `mount`, every `submit`, and every `refreshComments` (`src/Livewire/CommentThreadComponent.php:127`). — why: each round trip re-runs a DB lookup; memoize the resolved model per request. — `src/Livewire/CommentThreadComponent.php` — S

- **Index/labels parity in admin** — `LatestCommentsWidget` is registered (`src/Providers/AdminServiceProvider.php:67`) but is **absent from `capell.json` `contributes[]`** (which lists only `CommentStatsWidget`). — why: manifest is the marketplace/Diagnostics source of truth; the widget is invisible to tooling. Add it to `contributes[]`. — `capell.json`, `src/Providers/AdminServiceProvider.php` — S

## 3. Missing Features (gaps)

Manifest `capabilities[]` = `comments`, `comments-admin`, `comments-frontend`, `comments-created-event` — these are structurally present. The gaps below are category table-stakes that are advertised in config/README but not built, plus differentiators.

- **Spam scoring (table-stakes, advertised, NOT built).** `config('capell-comments.spam.max_links')` and `spam.blocked_terms` exist (`config/capell-comments.php:28-31`), the `comments.spam_reasons` JSON column exists (migration `..._000002`) and is `fillable`/cast (`src/Models/Comment.php:58,171`), and `CommentBodySanitizer::linkCount()` is computed and stored on every comment (`src/Actions/CreateCommentAction.php:97`) — **but nothing ever reads `max_links`/`blocked_terms`, writes `spam_reasons`, or sets `CommentStatus::Spam` automatically.** Spam status is only reachable via manual moderator action (`src/Filament/Resources/Comments/Tables/CommentsTable.php:63`, `TransitionCommentStatusAction`). The README's "spam ... settings" claim is unfulfilled. Build a `ScoreCommentSpamAction` invoked from `CreateCommentAction` that applies link-count + blocked-term rules, populates `spam_reasons`, and routes to `Spam`/`PendingApproval`.

- **Rate-limit hardening (anti-abuse).** The submit throttle key includes attacker-controlled `$this->authorEmail` (`src/Livewire/CommentThreadComponent.php:176-181`), so a bot varying the email field resets its own bucket. Re-key on IP + commentable (+ optional author hash) only, and consider a per-author-email _secondary_ cap rather than the primary key. Table-stakes for guest comments.

- **Moderator notifications & digests (advertised).** See §2 — no new-comment notification exists; only the author email-verification mail is wired (`src/Notifications/ConfirmCommentAuthorEmailNotification.php`). A daily/instant moderation digest is a natural premium differentiator and the `supports: capell-app/email-studio` dependency is the intended vehicle.

- **Reactions / voting (differentiator).** No upvote/like/reaction model. A `comment_reactions` table + aggregate counts on `PublicCommentData` would differentiate against bundled-CMS comment add-ons and is a common engagement lever for the "Capell Engagement" group.

- **Author reply notifications (differentiator).** When a reply is approved under a parent, the parent's verified author is never notified. Tokens infrastructure (`comment_tokens`) already supports one-click unsubscribe. Drives re-engagement.

- **Edit / delete window for authors (table-stakes-ish).** Authenticated/verified authors cannot edit or soft-delete their own comments from the frontend; only admins transition status. A short author edit window is expected by most comment systems.

- **Akismet / external spam provider hook (differentiator).** A pluggable spam-provider contract (Akismet, reCAPTCHA/Turnstile) layered over the local heuristics would be a strong premium up-sell and pairs with the honeypot in §2.

## 4. Issues / Risks

- **Stub health check = false-positive Diagnostics.** `src/Health/CommentsHealthCheck.php` has no check logic but is `severity: critical` in `capell.json`. High risk: operators trust a green that means nothing.

- **Dead spam configuration.** `spam.max_links`, `spam.blocked_terms`, the `spam_reasons` column, and `linkCount()` are wired up to the _write_ path but never _enforced_ (see §3). Tech debt + a security gap: the package presents as having spam protection it does not have. `config/capell-comments.php:28`, `src/Actions/CreateCommentAction.php:97`, `src/Models/Comment.php:58`.

- **Throttle bypass via email field.** `src/Livewire/CommentThreadComponent.php:176`. A trivial bot loop defeats the only built-in rate limit. Spam resilience risk.

- **Public render performance budget unverifiable / at risk.** Manifest sets `frontendRenderBudgetMs: 20` and `adminQueryBudget: 40`, but `BuildPublicThreadAction` fetches the entire approved subtree with no depth/reply cap beyond `rootLimit` (`src/Actions/BuildPublicThreadAction.php:65-81`). No test or benchmark asserts either budget. On a hot thread this exceeds 20ms and the budget is effectively aspirational.

- **XSS / sanitization — low residual but worth hardening.** Body is `strip_tags` + whitespace-collapsed, capped at 5000 chars (`src/Support/CommentBodySanitizer.php`), and output is Blade-escaped via `{{ }}` (`resources/views/livewire/thread.blade.php:27,50`), and `PublicCommentData` omits all sensitive fields — so stored-XSS surface is low. Residual: the sanitizer does not strip Unicode bidi/zero-width/control characters (spoofing) and `linkCount()` only counts `http(s)://` (misses `www.`/bare domains), weakening any future link-based spam rule. `src/Support/CommentBodySanitizer.php:11-25`.

- **Public-output safety — overall good, two notes.** `BuildPublicThreadAction::toData()` correctly emits only public id, sanitized body, display name, timestamp, depth, reply count, children — no status, model id, email, hashes, tokens, or admin URL (matches the README Public Safety contract). The thread endpoint sets `Cache-Control: no-store, private` (`src/Http/Controllers/RenderCommentThreadController.php:20`). Notes: (1) public Blade does not query the DB — data is hydrated by the Action ✔; (2) `auto_inject` injects `thread-shell` with the encrypted `threadKey` into cached page HTML (`src/Providers/FrontendServiceProvider.php:92`) — the key is `Crypt::encryptString` of type/id/site/lang, opaque and safe, but there is no test proving an anonymous, cached page never leaks the decrypted payload or moderation state. Add an explicit anonymous-leakage test for the `auto_inject` path.

- **PII / retention.** Author `name`/`email` are `encrypted` at rest ✔ and `email_hash` is HMAC ✔ (`src/Models/CommentAuthor.php:50-58,123`). Gaps: (1) `email_hash_secret`/`visitor_hash_secret` default to `null` from env and silently fall back to `app.key` (`config/capell-comments.php:22-23`, `src/Support/VisitorHasher.php:15`, `CommentAuthor::emailHash`) — rotating `app.key` orphans all hashes; document and warn. (2) No retention/erasure policy or command for visitor IP/UA hashes or author records (GDPR right-to-erasure). (3) `internal_notes` and `moderation_note` are plaintext.

- **Test gaps.** 41 cases across 8 files (`CreateCommentActionTest` 13, `PublicThreadActionTest` 10, `CommentsAdminSurfaceTest` 9, `CommentThreadComponentTest` 3, `VerifyCommentAuthorEmailRouteTest` 3, three Unit tests 1 each). Covered: script-tag sanitization (`PublicThreadActionTest.php:211-226`, `CreateCommentActionTest.php:32`), spam comments excluded from public thread, verification flows, admin surface actions, no-store thread headers, manifest requirements. **Not covered:** rate-limit/throttle behavior, honeypot (feature absent), automatic spam scoring (feature absent), moderator notification (feature absent), `auto_inject` anonymous-leakage, reply pagination, performance budgets, and there are **no Architecture tests** for this package.

- **i18n.** Strings are translated via `capell-comments::` namespaces ✔. `diffForHumans()` in the public Blade (`thread.blade.php:24,47`) is not locale-pinned to the site language and may render in the app locale rather than the page's `language_id`.

- **Manifest/README mismatches (low sev).** README "Best Used With" lists `html-cache` but `capell.json`/`composer.json` `supports` lists only `blog` + `email-studio`. `LatestCommentsWidget` missing from `contributes[]` (see §2). Marketplace `screenshots[]` (1) ≠ `screenshots.json` required runtime captures (4).

## 5. Marketplace & Selling

**Critique.** The marketplace `summary` and the composer `description` ("Configurable moderated comments for Capell pages and content.") are accurate but generic and lean on two adjectives ("moderated, configurable, cache-safe") that undersell the real differentiators (post-load no-store rendering that keeps cached pages safe; encrypted author PII; per-site/per-type setting overrides) — and, worse, "configurable" currently implies spam/notification toggles that don't function (§3/§4). Fix the product before leaning on those words. Only 1 marketing screenshot is wired despite 4 high-value runtime captures already specified in `screenshots.json` (moderation inbox and a real public thread are the money shots).

**Improved 1-sentence summary:** "Add moderated, threaded discussion to any Capell page or article — with cache-safe public rendering, encrypted author records, and per-site moderation controls, no custom code required."

**Improved 3–4 sentence description:** "Comments lets site owners open public, threaded discussion on pages and Blog articles while editors keep full control from a dedicated moderation inbox with author records, status transitions, and dashboard widgets. Threads load after the page via a private, no-store endpoint, so they never contaminate cached HTML or leak moderation state to anonymous visitors. Author identities are encrypted at rest and matched by HMAC, with optional email verification, guest-vs-authenticated identity modes, and configurable approval, throttling, and depth limits per site or content type. Developers register a commentable type once and the package resolves public-safe thread data for the frontend."

**Media gaps:** wire the 4 `screenshots.json` captures into `capell.json.marketplace.screenshots`; add a short GIF of the moderation approve/reject flow and a before/after of a cached page with comments loading in.

**Pricing / tier / bundle positioning:** `tier: premium`, `bundle: comments`, `proposedLicense: paid`, `requestedCertification: first-party`, `supportPolicy: priority`. Reasonable, but to _hold_ premium it must close the spam/notification gaps — a "moderated comments" product that can't auto-flag spam or alert moderators is hard to defend at premium against bundled CMS comment add-ons. Cross-sell paths already in deps/manifest: **Blog** (`supports`) for article discussion — lead with this in the listing; **Email Studio** (`supports`) for richer moderator/author notification templates — make this the up-sell once §3 notifications ship; **HTML Cache** (README "Best Used With") — position the no-store design as the reason these two coexist safely. Extension-suite angle: package with Blog + Email Studio as an "Engagement Suite."

**Differentiators / value props:** cache-safe post-load rendering; encrypted PII + HMAC dedup; per-site & per-commentable-type setting overrides; verification flows (verify-then-moderate / moderate-then-verify); soft-delete + full moderation audit trail (`comment_moderation_events`). **Target buyer:** content/marketing teams on Capell (esp. Blog users) who want managed discussion without standing up Disqus/a third party and without breaking page caching.

**Keywords/tags (8–12):** comments, discussion, threaded-comments, moderation, engagement, blog-comments, spam-filtering, email-verification, livewire, cache-safe, filament, capell.

## 6. Prioritized Roadmap

| Item                                                                                      | Bucket | Effort | Impact | Section ref |
| ----------------------------------------------------------------------------------------- | ------ | ------ | ------ | ----------- |
| Implement real `CommentsHealthCheck` (tables, settings, route, component)                 | Now    | M      | High   | §2, §4      |
| Implement automatic spam scoring (`max_links`, `blocked_terms`, write `spam_reasons`)     | Now    | M      | High   | §3, §4      |
| Fix throttle key (drop attacker-controlled email from primary bucket)                     | Now    | S      | High   | §3, §4      |
| Wire moderator new-comment notification listener on `CommentCreated`                      | Now    | M      | High   | §2, §3      |
| Add honeypot + min-render-age to public form                                              | Now    | S      | High   | §2, §3      |
| Add `auto_inject` anonymous-leakage + throttle Pest tests; add Arch tests                 | Now    | M      | High   | §4          |
| Add `LatestCommentsWidget` to `capell.json` contributes[]; fix README/supports mismatches | Now    | S      | Med    | §2, §4      |
| Wire the 4 `screenshots.json` captures into marketplace + new summary/description         | Now    | S      | Med    | §5          |
| Implement reply pagination using `reply_page_size`; cap subtree query                     | Next   | M      | High   | §2, §3      |
| Benchmark + assert `frontendRenderBudgetMs`/`adminQueryBudget`                            | Next   | M      | Med    | §4          |
| Harden sanitizer (bidi/zero-width strip; broaden link detection)                          | Next   | S      | Med    | §4          |
| Memoize resolved commentable in Livewire component                                        | Next   | S      | Low    | §2          |
| Locale-pin public timestamps to page `language_id`                                        | Next   | S      | Low    | §4          |
| Pluggable external spam provider (Akismet/Turnstile) contract                             | Later  | M      | Med    | §3          |
| Reactions/voting + author-reply notifications (Engagement Suite up-sell)                  | Later  | L      | Med    | §3, §5      |
| PII retention/erasure command + secret-rotation docs for hashes                           | Later  | M      | Med    | §4          |
