# Password Policy — Improvement & Growth Plan

> Package: capell-app/password-policy · Kind: package · Tier: premium · Product group: Capell Operations · Bundle: operations · Status: Draft

## 1. Snapshot

Password Policy adds opt-in admin password controls to Capell: password expiry, forced "must change", recent-password reuse blocking, and Laravel's compromised-password (HIBP) check. It surfaces through two Filament admin paths — an Extensions-page settings modal (`PasswordPolicySettingsSchema`) and a forced-change page (`ForcedPasswordChangePage`) gated by an auth middleware (`EnsurePasswordPolicyCompliance`) — plus columns/filters/row+bulk actions injected into the core Users table via `PasswordPolicyUserTableExtender` / `PasswordPolicyUserFormExtender`. Domain logic lives in six Actions (`ValidatePasswordChangeAction`, `EvaluatePasswordPolicyAction`, `UpdatePasswordAction`, `RecordPasswordHistoryAction`, `MarkUserForPasswordChangeAction`, `BuildPasswordSecurityPostureReportAction`); persistence is two `users` columns (`password_changed_at`, `must_change_password`), the `password_policy_password_histories` table, and a `password_policy` settings group. Deps: `capell-app/admin`, `capell-app/core`, `lorisleiva/laravel-actions`, `spatie/laravel-data`, `spatie/laravel-package-tools`.

Current marketplace `summary` (verbatim): _"Password Policy provides opt-in password rules and enforcement for Capell CMS."_ Screenshots advertised in `capell.json`: **1** (`docs/assets/marketplace/extension-card.jpg`) — but `docs/screenshots.json` declares **3 required** deployment screenshots (settings page, forced-change form, user-table columns) and none are generated yet. The composer `description` ("Password expiry, forced password changes, and password safety policy for Capell CMS") does **not** match the `capell.json` description/summary — a manifest mismatch to reconcile.

## 2. Improvements (existing functionality)

- **Configurable complexity in the validator is wired** — settings now cover minimum length, mixed case, numbers, and symbols, and `ValidatePasswordChangeAction` builds Laravel's `Password` rule from those settings. — `src/Actions/ValidatePasswordChangeAction.php` + `src/Filament/Settings/PasswordPolicySettingsSchema.php` — **Done**
- **De-duplicate the extension-surface registration** — `registerPasswordPolicySettingsExtensionPage()` registers the settings surface, and `PasswordPolicyAdminBridge::register()` registers the _same_ `ExtensionManagementSurfaceData::settings(...)` again. On hosts where both the legacy fallback and the bridge run, the surface is registered twice. Pick one owner (bridge) and drop the other. — `src/Providers/PasswordPolicyServiceProvider.php:117` + `src/Bridges/PasswordPolicyAdminBridge.php:32` — **S**
- **History pruning** — `RecordPasswordHistoryAction` inserts a new hash on every change but never trims; `password_policy_password_histories` grows unbounded per user. Keep only the most recent `password_history_count` (+1) rows after insert. — `src/Actions/RecordPasswordHistoryAction.php:25` — **S**
- **Admin edit history ownership is single-path** — `PasswordPolicyUserFormExtender` validates before save, stashes the previous hash, and records exactly one history row from `afterSave()` only after Filament confirms the password changed. `UpdatePasswordAction` remains the owner for the forced-change flow. — `src/Filament/Extenders/PasswordPolicyUserFormExtender.php` + `tests/Feature/PasswordPolicyAdminTest.php` — **Done**
- **`password_changed_at` install safety is closed** — the install migration backfills existing users when the column is added, and policy evaluation no longer treats a missing legacy timestamp as expired by itself. — `database/migrations/2026_05_10_190863_01_add_password_policy_columns_to_users_table.php` + `tests/Unit/PasswordPolicySettingsSchemaTest.php` — **Done**
- **History reuse check ignores the configured count for the live hash** — reuse always includes the current password hash plus up to `passwordHistoryCount` history rows; fine, but `max(1, …)` silently rewrites a `0` setting to `1`. Validate the setting at the form layer (min 1 already in schema) and drop the silent clamp so behaviour matches the configured value. — `src/Actions/ValidatePasswordChangeAction.php:66` — **S**
- **Add `down()` to the settings migration** — the Spatie settings migration only defines `up()`; rollbacks leave orphaned `password_policy.*` rows. — `database/settings/2026_05_10_190864_01_create_password_policy_settings.php` — **S**
- **`canAccess()` on the forced-change page is any-authenticated** — `ForcedPasswordChangePage::canAccess()` returns true for any `Authenticatable`, so the page is reachable in every panel even when the policy is disabled or the user is compliant. Gate on `EvaluatePasswordPolicyAction` status (or at least `force_change_enabled || password_expiry_enabled`). — `src/Filament/Pages/ForcedPasswordChangePage.php:43` — **S**
- **Repeated `Schema::hasColumn` / `Schema::hasTable` probes** — every Action re-probes the schema on each call (`UpdatePasswordAction`, `EvaluatePasswordPolicyAction`, `RecordPasswordHistoryAction`, `ValidatePasswordChangeAction`). The table extender already uses `RuntimeSchemaState` (cached); the Actions do not. Route all probes through `RuntimeSchemaState` for consistency and to respect the 40-query admin budget. — `src/Actions/*.php` (Schema facade) vs `src/Filament/Extenders/PasswordPolicyUserTableExtender.php:97` — **M**
- **`ForcedPasswordChangePage` redirect target is hand-built** — `redirect('/' . trim($panelPath, '/'))` rebuilds the panel URL by hand; use `Filament::getCurrentPanel()?->getUrl()` / the panel home route to survive path/prefix changes. — `src/Filament/Pages/ForcedPasswordChangePage.php:108` — **S**

## 3. Missing Features (gaps)

Tied to `capabilities[]`: `password-policy`, `password-policy-admin`.

- **Configurable complexity rules (closed in current slice).** The package now exposes minimum length, mixed-case, number, and symbol settings and wires them into `Password::min(...)->mixedCase()->numbers()->symbols()` in `ValidatePasswordChangeAction`. Keep future register/reset integration aligned with this same settings boundary. — **Differentiator-critical: this is the headline feature buyers expect.**
- **Enforcement at non-admin auth entry points.** Rules only fire through the two Filament admin forms + the forced-change page. There is **no Fortify/registration/password-reset hook**: a password set via the host app's register or reset-password flow bypasses complexity, history, and compromised checks entirely. Provide a reusable `PasswordPolicyRule` (a single `ValidationRule` object built from settings) that host apps and Fortify `CreateNewUser` / `ResetUserPassword` can opt into. — **Table stakes for a "password policy" product.**
- **Account lockout / throttle on failed logins.** No brute-force protection (rate-limit, temporary lockout, lockout counter columns). A natural premium capability and a common compliance requirement. — **Differentiator.**
- **Password expiry _notifications_.** Expiry only acts at request time (redirect). No "your password expires in N days" email/notification ahead of lockout. — **Differentiator.**
- **HIBP hardening + admin toggle context.** The compromised check delegates to Laravel's `uncompromised()` (HIBP k-anonymity, range API). There is no timeout, failure fallback (fail-open vs fail-closed), result caching, or documentation of the privacy model. Add config for timeout + fail mode and document k-anonymity so security-conscious buyers trust it. — **Table stakes + trust.**
- **Console capability drift is closed.** `capell.json` now declares the shipped admin surface/capabilities only. Console commands remain a future product row rather than an advertised surface. — **Manifest-vs-code gap closed.**
- **2FA / step-up hooks.** No events or extension points other packages (e.g. a future 2FA package) could subscribe to on password change/expiry. Emit domain events (`PasswordChanged`, `PasswordExpired`, `UserMarkedForChange`) via `CapellCore::subscriberManager()`. — **Differentiator + cross-sell enabler.**
- **Per-role / per-panel policy scoping.** Settings are global. Enterprises typically want stricter rules for admins than editors, or different rules per panel. — **Differentiator (enterprise tier).**
- **Real health-check assertions.** `PasswordPolicyHealthCheck` implements only the marker `ChecksExtensionHealth` (returns `securityPosture()` data, no pass/fail). The manifest marks it `severity: critical` with a label claiming "install health are discoverable". Add an actual check that fails when a policy is enabled but its backing column/table is missing (e.g. `force_change_enabled` on without `must_change_password`). — **Closes the manifest claim.**

## 4. Issues / Risks

- **Compromised-password (HIBP) path now has regression coverage.** `PasswordPolicySettingsSchemaTest` fakes the pwned-passwords range API, prevents stray requests, and proves a breached password is rejected. — `tests/Unit/PasswordPolicySettingsSchemaTest.php` — **Closed**
- **The middleware class itself is never unit-tested.** Redirect behaviour is asserted at the Action level (`EvaluatePasswordPolicyAction` → `it('redirects flagged admin users…')`), but `EnsurePasswordPolicyCompliance` is never instantiated; `middleware` / `authMiddleware` have **0** test hits. The allowed-route logic (logout route, change-password path matching via `$request->is()`) and the "no redirect when compliant" branch are unverified. A regression here locks admins out of the panel. — `src/Http/Middleware/EnsurePasswordPolicyCompliance.php` — **High**
- **Expiry enablement lockout risk is closed for the shipped path.** The install migration backfills existing users when adding `password_changed_at`, and missing legacy timestamps no longer force expiry redirects by themselves. — `src/Actions/EvaluatePasswordPolicyAction.php` + `database/migrations/2026_05_10_190863_01_add_password_policy_columns_to_users_table.php` — **Closed**
- **Enforcement surface is narrow / partially advertised.** Rules apply to Filament admin forms + forced-change page only. The package name promises a "policy" but cannot enforce on register/reset (§3). Document this boundary explicitly and ship the reusable rule. — **Medium**
- **Validation messages partly bypass i18n.** `current_password` and `password_reused` are translated (`resources/lang/en/validation.php`), but the core `Password::min(8)` / `confirmed` / `uncompromised` failures fall back to **Laravel's default English** messages, not the package's translation namespace. A buyer running a non-English panel gets mixed-language errors. — `src/Actions/ValidatePasswordChangeAction.php:40-45` — **Medium**
- **History table grows unbounded.** No pruning (§2). On a large/long-lived install the `password_policy_password_histories` table and the per-validation `Hash::check` loop both grow without limit. — `src/Actions/RecordPasswordHistoryAction.php` — **Medium**
- **Performance budget plausibility.** `capell.json` sets `adminQueryBudget: 40` and `cacheable: false`. The repeated raw `Schema` probes (§2) and the per-row `must_change_password` policy/`Gate` checks in the table extender (`canMarkUser` per record) risk approaching that budget on large user tables; no test asserts the query count. — `capell.json` `performance` + `src/Filament/Extenders/PasswordPolicyUserTableExtender.php:111` — **Medium**
- **`config('capell-password-policy.enabled')` kill-switch is coarse and untested.** Setting it false skips _all_ registration (settings, surfaces, middleware) — including the forced-change page route — which could strand already-flagged users mid-flow. No test covers the disabled path. — `src/Providers/PasswordPolicyServiceProvider.php:38` — **Low**
- **Manifest/doc drift is mostly closed.** composer and Capell descriptions now match, README is populated, and the unimplemented console surface/capability was removed. The remaining low-level follow-up is to confirm whether `deferredContributions: ["admin-page"]` should stay while the Extensions modal is the live settings path. — `composer.json`, `README.md`, `capell.json` — **Low**

## 5. Marketplace & Selling

**Critique.** The `summary` and composer `description` disagree, and both are weak. The summary ("opt-in password rules and enforcement") is generic and buries the actual deliverables; the composer description lists three features but omits compromised-password (HIBP) checks and history. Neither communicates _who it's for_ or _why it beats Laravel defaults_. The README is empty, so the GitHub/Packagist landing tells a buyer nothing. Only 1 of 3 required marketplace screenshots exists.

**Improved summary (1 sentence):**

> Enforce admin password expiry, forced resets, reuse history, and breach (HIBP) checks across your Capell panels — configured from one settings screen, no code.

**Improved description (3–4 sentences):**

> Password Policy gives Capell administrators enterprise-grade account controls without touching code. Toggle password expiry windows, force flagged users to reset on next login, block recently reused passwords, and reject known-breached passwords via Have I Been Pwned — all from a single admin settings page. Policy state lives on the users table with full table columns, filters, and one-click "require change" row and bulk actions. Built on Capell Actions so host apps can reuse the same validation rules in their own registration and reset flows.

**Screenshot/media gaps.** Generate the 3 required deployment screenshots declared in `docs/screenshots.json` (settings page, forced-change form, user-table columns + filters). Add the complexity-rules settings screenshot once §3 lands. The single `extension-card.jpg` is fine as the card image but is not a feature demo.

**Pricing / tier / bundle positioning.** `tier: premium`, `bundle: operations`, group `Capell Operations`, license `paid`, certification `first-party`, support `priority` — appropriate for a security control. To justify premium over Laravel's free built-ins, the differentiators (lockout, expiry notifications, per-role scoping, breach checks with admin toggle) in §3 need to ship; today the core (expiry + history + `uncompromised`) is thin for a paid security add-on.

**Cross-sell.** Manifest deps are only the two Capell platform packages, so cross-sell is by _workflow_ not by dependency. `docs/README.md` already points at **Login Audit** and **Diagnostics** as neighbours — lean into that as an **Extension Suite**: pair Password Policy with **login-audit** (who reset/was-locked, when) and a **privacy-center** (data-subject / credential hygiene) package. Emitting password lifecycle events (§3) makes login-audit a natural attach. Position the trio as a "Capell Account Security" suite bundle.

**Differentiators / value props / target buyer.** Target buyer: agencies and in-house teams running Capell for clients with compliance needs (SOC 2 / ISO 27001 / internal IT policy) who need auditable admin password controls out of the box. Value props: zero-code config, breach-aware, reusable in custom auth flows, and (once built) lockout + expiry notifications + per-role rules.

**Keywords/tags (8–12):** password policy, password expiry, forced password change, password history, compromised password, have i been pwned, hibp, account security, admin security, compliance, password reuse, filament security.

## 6. Prioritized Roadmap

| Item                                                                                         | Bucket | Effort | Impact | Section ref |
| -------------------------------------------------------------------------------------------- | ------ | ------ | ------ | ----------- |
| Test HIBP/`uncompromised` path with `Http::fake()` + stray-request guard                     | Done   | S      | High   | §4          |
| Backfill `password_changed_at` on install (or treat null as non-expired)                     | Done   | M      | High   | §2, §4      |
| Add direct middleware tests (redirect, allowed-route, compliant no-op)                       | Now    | S      | High   | §4          |
| Reconcile manifest drift: composer↔capell.json desc, fill README, resolve console capability | Done   | S      | Medium | §4, §5      |
| Generate the 3 required marketplace screenshots; rewrite summary/description                 | Now    | S      | High   | §5          |
| Add configurable complexity rules (length/case/number/symbol) + wire into validator          | Done   | M      | High   | §3, §2      |
| Fix double/duplicate history write on admin edit; record once post-hash                      | Done   | M      | Medium | §2          |
| De-duplicate extension-surface registration (bridge vs provider)                             | Next   | S      | Medium | §2          |
| Prune `password_policy_password_histories` to configured count                               | Next   | S      | Medium | §2, §4      |
| Ship reusable `PasswordPolicyRule` + Fortify register/reset hooks                            | Next   | M      | High   | §3          |
| Route Action schema probes through `RuntimeSchemaState`; assert admin query budget           | Next   | M      | Medium | §2, §4      |
| Translate core password validation failures into package i18n namespace                      | Next   | S      | Medium | §4          |
| Emit password lifecycle events for login-audit / 2FA cross-sell                              | Next   | M      | Medium | §3, §5      |
| Account lockout / login throttle + expiry-warning notifications                              | Later  | L      | High   | §3          |
| Per-role / per-panel policy scoping                                                          | Later  | L      | Medium | §3          |
| Real health-check assertions (enabled-but-not-installed → fail)                              | Later  | M      | Medium | §3, §4      |
| Add Artisan console commands (expire-stale, require-change, prune-history, doctor)           | Later  | M      | Medium | §3          |
