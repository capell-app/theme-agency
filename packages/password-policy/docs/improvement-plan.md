# Password Policy — Improvement & Growth Plan

> Package: capell-app/password-policy · Kind: package · Tier: premium · Product group: Capell Operations · Bundle: operations · Status: Draft

## 1. Snapshot

Password Policy adds opt-in admin password controls to Capell: password expiry, forced "must change", recent-password reuse blocking, and Laravel's compromised-password (HIBP) check. It surfaces through two Filament admin paths — an Extensions-page settings modal (`PasswordPolicySettingsSchema`) and a forced-change page (`ForcedPasswordChangePage`) gated by an auth middleware (`EnsurePasswordPolicyCompliance`) — plus columns/filters/row+bulk actions injected into the core Users table via `PasswordPolicyUserTableExtender` / `PasswordPolicyUserFormExtender`. Domain logic lives in six Actions (`ValidatePasswordChangeAction`, `EvaluatePasswordPolicyAction`, `UpdatePasswordAction`, `RecordPasswordHistoryAction`, `MarkUserForPasswordChangeAction`, `BuildPasswordSecurityPostureReportAction`); persistence is two `users` columns (`password_changed_at`, `must_change_password`), the `password_policy_password_histories` table, and a `password_policy` settings group. Deps: `capell-app/admin`, `capell-app/core`, `lorisleiva/laravel-actions`, `spatie/laravel-data`, `spatie/laravel-package-tools`.

Current marketplace `summary`: _"Enforce admin password expiry, forced resets, reuse history, and breach (HIBP) checks across your Capell panels — configured from one settings screen, no code."_ Screenshot media is open again: `capell.json` keeps only the extension card because the prior settings, forced-change, and Users-table product captures were mock admin artwork rather than Capell screenshot runner output. The composer `description`, `capell.json` description/summary, README, and overview now agree on the shipped admin password controls.

## 2. Improvements (existing functionality)

- **Screenshot contract reopened** — the settings page, forced-change form, and Users-table policy-column targets remain in `docs/screenshots.json`, but the previous mock captures were removed from `docs/screenshots/` and from marketplace media. Generate these from a migrated Capell admin runner app with Password Policy settings, the forced-change route, and the Users table extender visible before promotion. — `docs/screenshots.json`, `capell.json` — **S**

- **Configurable complexity in the validator is wired** — settings now cover minimum length, mixed case, numbers, and symbols, and `ValidatePasswordChangeAction` builds Laravel's `Password` rule from those settings. — `src/Actions/ValidatePasswordChangeAction.php` + `src/Filament/Settings/PasswordPolicySettingsSchema.php` — **Done**
- **Shipped 2026-06-06: extension-surface registration is single-owner.** Bridge-capable hosts now let `PasswordPolicyAdminBridge` register the Extensions settings surface; the service-provider fallback only runs for legacy non-bridge hosts. — `src/Providers/PasswordPolicyServiceProvider.php` + `src/Bridges/PasswordPolicyAdminBridge.php` — **Done**
- **Shipped 2026-06-06: password history pruning.** `RecordPasswordHistoryAction` now trims old rows for the user after inserting a new hash, keeping the most recent configured `passwordHistoryCount` entries ordered by creation/id. — `src/Actions/RecordPasswordHistoryAction.php` — **S**
- **Admin edit history ownership is single-path** — `PasswordPolicyUserFormExtender` validates before save, stashes the previous hash, and records exactly one history row from `afterSave()` only after Filament confirms the password changed. `UpdatePasswordAction` remains the owner for the forced-change flow. — `src/Filament/Extenders/PasswordPolicyUserFormExtender.php` + `tests/Feature/PasswordPolicyAdminTest.php` — **Done**
- **`password_changed_at` install safety is closed** — the install migration backfills existing users when the column is added, and policy evaluation no longer treats a missing legacy timestamp as expired by itself. — `database/migrations/2026_05_10_190863_01_add_password_policy_columns_to_users_table.php` + `tests/Unit/PasswordPolicySettingsSchemaTest.php` — **Done**
- **History reuse check ignores the configured count for the live hash** — reuse always includes the current password hash plus up to `passwordHistoryCount` history rows; fine, but `max(1, …)` silently rewrites a `0` setting to `1`. Validate the setting at the form layer (min 1 already in schema) and drop the silent clamp so behaviour matches the configured value. — `src/Actions/ValidatePasswordChangeAction.php:66` — **S**
- **Add `down()` to the settings migration** — the Spatie settings migration only defines `up()`; rollbacks leave orphaned `password_policy.*` rows. — `database/settings/2026_05_10_190864_01_create_password_policy_settings.php` — **S**
- **`canAccess()` on the forced-change page is any-authenticated** — `ForcedPasswordChangePage::canAccess()` returns true for any `Authenticatable`, so the page is reachable in every panel even when the policy is disabled or the user is compliant. Gate on `EvaluatePasswordPolicyAction` status (or at least `force_change_enabled || password_expiry_enabled`). — `src/Filament/Pages/ForcedPasswordChangePage.php:43` — **S**
- **Shipped 2026-06-06: schema probes use `RuntimeSchemaState`.** Password Policy actions, form persistence, posture reporting, and health diagnostics now route table/column checks through the shared cached schema state instead of raw `Schema` facade probes. — `src/Actions/*.php`, `src/Filament/Extenders/PasswordPolicyUserFormExtender.php`, `src/Health/PasswordPolicyHealthCheck.php` — **Done**
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
- **Closed: core password validation failures use package i18n.** `ValidatePasswordChangeAction` now passes package-owned messages for required, confirmation, minimum length, mixed-case, numbers, symbols, and compromised-password failures. — `src/Actions/ValidatePasswordChangeAction.php` + `resources/lang/en/validation.php` — **Closed**
- **Closed: history table is pruned per user.** `RecordPasswordHistoryAction` now keeps only the configured number of recent history rows after recording a password change, bounding future validation work. — `src/Actions/RecordPasswordHistoryAction.php` — **Medium**
- **Performance budget plausibility narrowed.** Raw schema probes now use `RuntimeSchemaState` (§2), reducing repeated metadata queries. The remaining risk is the per-row `must_change_password` policy/`Gate` checks in the table extender (`canMarkUser` per record); no test asserts the query count. — `capell.json` `performance` + `src/Filament/Extenders/PasswordPolicyUserTableExtender.php:111` — **Medium**
- **`config('capell-password-policy.enabled')` kill-switch is coarse and untested.** Setting it false skips _all_ registration (settings, surfaces, middleware) — including the forced-change page route — which could strand already-flagged users mid-flow. No test covers the disabled path. — `src/Providers/PasswordPolicyServiceProvider.php:38` — **Low**
- **Manifest/doc drift is mostly closed.** composer and Capell descriptions now match, README is populated, and the unimplemented console surface/capability was removed. The remaining low-level follow-up is to confirm whether `deferredContributions: ["admin-page"]` should stay while the Extensions modal is the live settings path. — `composer.json`, `README.md`, `capell.json` — **Low**

## 5. Marketplace & Selling

**Critique.** Marketplace and Composer copy now agree on the shipped admin password controls: expiry, forced resets, reuse history, breach checks, and one settings screen. README and overview are populated. The required marketplace screenshot contract is now complete, so the remaining buyer-facing depth is feature expansion rather than visual proof.

**Improved summary (1 sentence):**

> Enforce admin password expiry, forced resets, reuse history, and breach (HIBP) checks across your Capell panels — configured from one settings screen, no code.

**Improved description (3–4 sentences):**

> Password Policy gives Capell administrators enterprise-grade account controls without touching code. Toggle password expiry windows, force flagged users to reset on next login, block recently reused passwords, and reject known-breached passwords via Have I Been Pwned — all from a single admin settings page. Policy state lives on the users table with full table columns, filters, and one-click "require change" row and bulk actions. Built on Capell Actions so host apps can reuse the same validation rules in their own registration and reset flows.

**Screenshot/media status.** `capell.json` currently lists only the extension card. The three required deployment screenshots declared in `docs/screenshots.json` (settings page, forced-change form, user-table columns + filters) need real Capell runner captures before promotion. Add a deeper complexity-rules or lockout screenshot once the future §3 feature rows land.

**Pricing / tier / bundle positioning.** `tier: premium`, `bundle: operations`, group `Capell Operations`, license `paid`, certification `first-party`, support `priority` — appropriate for a security control. To justify premium over Laravel's free built-ins, the differentiators (lockout, expiry notifications, per-role scoping, breach checks with admin toggle) in §3 need to ship; today the core (expiry + history + `uncompromised`) is thin for a paid security add-on.

**Cross-sell.** Manifest deps are only the two Capell platform packages, so cross-sell is by _workflow_ not by dependency. `docs/README.md` already points at **Login Audit** and **Diagnostics** as neighbours — lean into that as an **Extension Suite**: pair Password Policy with **login-audit** (who reset/was-locked, when) and a **privacy-center** (data-subject / credential hygiene) package. Emitting password lifecycle events (§3) makes login-audit a natural attach. Position the trio as a "Capell Account Security" suite bundle.

**Differentiators / value props / target buyer.** Target buyer: agencies and in-house teams running Capell for clients with compliance needs (SOC 2 / ISO 27001 / internal IT policy) who need auditable admin password controls out of the box. Value props: zero-code config, breach-aware, reusable in custom auth flows, and (once built) lockout + expiry notifications + per-role rules.

**Keywords/tags (8–12):** password policy, password expiry, forced password change, password history, compromised password, have i been pwned, hibp, account security, admin security, compliance, password reuse, filament security.

## 6. Prioritized Roadmap

| Item                                                                                         | Bucket | Effort | Impact | Section ref |
| -------------------------------------------------------------------------------------------- | ------ | ------ | ------ | ----------- |
| Test HIBP/`uncompromised` path with `Http::fake()` + stray-request guard                     | Done   | S      | High   | §4          |
| Backfill `password_changed_at` on install (or treat null as non-expired)                     | Done   | M      | High   | §2, §4      |
| Add direct middleware tests (redirect, allowed-route, compliant no-op)                       | Done   | S      | High   | §4          |
| Reconcile manifest drift: composer↔capell.json desc, fill README, resolve console capability | Done   | S      | Medium | §4, §5      |
| Generate real Capell runner marketplace screenshots for settings, forced-change, and Users-table policy columns | Next   | S      | High   | §5          |
| Shipped: rewrite marketplace summary/description                                             | Done   | S      | High   | §5          |
| Add configurable complexity rules (length/case/number/symbol) + wire into validator          | Done   | M      | High   | §3, §2      |
| Fix double/duplicate history write on admin edit; record once post-hash                      | Done   | M      | Medium | §2          |
| De-duplicate extension-surface registration (bridge vs provider)                             | Done   | S      | Medium | §2          |
| Prune `password_policy_password_histories` to configured count                               | Done   | S      | Medium | §2, §4      |
| Ship reusable `PasswordPolicyRule` + Fortify register/reset hooks                            | Next   | M      | High   | §3          |
| Route Action schema probes through `RuntimeSchemaState`; assert admin query budget           | Done   | M      | Medium | §2, §4      |
| Translate core password validation failures into package i18n namespace                      | Done   | S      | Medium | §4          |
| Emit password lifecycle events for login-audit / 2FA cross-sell                              | Next   | M      | Medium | §3, §5      |
| Account lockout / login throttle + expiry-warning notifications                              | Later  | L      | High   | §3          |
| Per-role / per-panel policy scoping                                                          | Later  | L      | Medium | §3          |
| Real health-check assertions (enabled-but-not-installed → fail)                              | Later  | M      | Medium | §3, §4      |
| Add Artisan console commands (expire-stale, require-change, prune-history, doctor)           | Later  | M      | Medium | §3          |
