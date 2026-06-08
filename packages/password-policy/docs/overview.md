---
title: 'Password Policy Overview'
description: 'How the Capell Password Policy package enforces expiry, forced changes, compromised password checks, and password history.'
---

# Password Policy Overview

Password Policy adds opt-in password safety rules to Capell Admin. It can force a user to change their password, expire old passwords, enforce complexity rules, reject compromised passwords, and prevent recent password reuse.

Use it for Capell installs that need stronger admin account controls without putting password rules into app-specific Filament pages.

## What It Adds

- Settings-backed password policy rules.
- User columns for password change state and last password change time.
- Password history storage for recent-password reuse checks.
- Actions for evaluation, validation, updates, forced changes, and history recording.
- Admin settings and forced-password-change surfaces.
- Console commands for stale expiry marking, forced-change marking, history pruning, and diagnostics.

## Policy Rules

| Rule              | Setting                                                                               | Behaviour                                                                                                                                                    |
| ----------------- | ------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| Password expiry   | `password_expiry_enabled`, `password_expiry_days`                                     | Users with an old `password_changed_at` value are treated as expired; missing legacy timestamps are shown separately but do not force a reset by themselves. |
| Forced change     | `force_change_enabled`                                                                | Users with `must_change_password` set must choose a new password.                                                                                            |
| Complexity        | `minimum_password_length`, `require_mixed_case`, `require_numbers`, `require_symbols` | New passwords are checked with Laravel's password rule builder for the configured length and character requirements.                                         |
| Compromised check | `compromised_password_checks_enabled`                                                 | New passwords can use Laravel's `Password::uncompromised()` rule.                                                                                            |
| Password history  | `password_history_enabled`, `password_history_count`                                  | Recent hashes are checked before a new password is accepted.                                                                                                 |

The package checks for required columns and tables before using them, so partially migrated environments fail softly where possible.

When the package installs the `password_changed_at` column, existing users are backfilled to the install time. This avoids turning on expiry and immediately forcing every existing administrator through a password reset. If a legacy user still has a missing timestamp, expiry treats that as unknown rather than expired; explicit `must_change_password` flags still take precedence.

## Data And Persistence

Password Policy adds:

- columns on the users table for password policy state
- `password_policy_password_histories` for previous password hashes
- settings under the `password_policy` group

The settings class is `Capell\PasswordPolicy\Settings\PasswordPolicySettings`.

The package registers its two normal Laravel migrations through `PasswordPolicyServiceProvider`. The package install flow must also publish the settings migration from `database/settings` so the settings rows exist before the Filament settings page is used.

## Action Boundary

Use the Actions directly when changing password behaviour:

| Action                            | Purpose                                                                                               |
| --------------------------------- | ----------------------------------------------------------------------------------------------------- |
| `EvaluatePasswordPolicyAction`    | Returns whether a user must change password or has an expired password.                               |
| `ValidatePasswordChangeAction`    | Validates current password, confirmation, complexity, compromised-password checks, and history reuse. |
| `UpdatePasswordAction`            | Updates the user's password through the package flow.                                                 |
| `RecordPasswordHistoryAction`     | Stores password history after a successful change.                                                    |
| `MarkUserForPasswordChangeAction` | Marks a user for the forced-change flow.                                                              |

Keep validation and history rules in these Actions rather than duplicating them in Filament pages or controllers.

## Console Commands

Use the console commands for scheduled or operator-driven maintenance:

| Command                                 | Purpose                                                                                              |
| --------------------------------------- | ---------------------------------------------------------------------------------------------------- |
| `capell:password-policy:expire-stale`   | Marks users with passwords older than the configured or supplied `--days` value for password change. |
| `capell:password-policy:require-change` | Marks one user with `--user-id` or every user with `--all`; supports `--dry-run`.                    |
| `capell:password-policy:prune-history`  | Prunes old password history rows, optionally scoped by `--user-id` or `--keep`.                      |
| `capell:password-policy:doctor`         | Runs the package health diagnostics, with optional `--json` output.                                  |

## Lifecycle Events

Password Policy notifies Capell Core subscribers when password state changes so
Login Audit, 2FA, or other security packages can react without coupling to
Filament pages.

| Event name                               | Context class                                              | Emitted when                                                               |
| ---------------------------------------- | ---------------------------------------------------------- | -------------------------------------------------------------------------- |
| `password-policy.password-changed`       | `Capell\PasswordPolicy\Events\PasswordChanged`             | A password is changed through the forced-change action or admin user form. |
| `password-policy.password-expired`       | `Capell\PasswordPolicy\Events\PasswordExpired`             | Policy evaluation detects an expired password.                             |
| `password-policy.user-marked-for-change` | `Capell\PasswordPolicy\Events\UserMarkedForPasswordChange` | A user is marked for forced password change.                               |

Subscribe through `CapellCore::subscriberManager()` with an implementation of
`Capell\Core\Contracts\EventSubscriber`.

## Install And Verify

Install the package in a host Capell app:

```bash
composer require capell-app/password-policy
```

Run the host app's package install and migration flow, then verify package changes in this repository with:

```bash
vendor/bin/pest packages/password-policy/tests --configuration=phpunit.xml
```

## Screenshot Coverage

The marketplace manifest lists the extension card plus Capell screenshot runner
captures for the settings page, forced-password-change form, and Users-table
policy-column surface in light and dark mode.
