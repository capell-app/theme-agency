# Changelog

All notable changes to `capell-app/customer-portal` will be documented in this file.

## Unreleased

### 2026-06-04

- Added package model factories for `PortalAccount` and `PortalSupportRequest`, including site/account states for customer portal tests and demos.
- Added frontend account-isolation coverage proving one portal account cannot see another account's support requests on the same site.
- Added frontend negative-path coverage for unauthenticated portal requests and the support submission throttle.
- Added support request submitted/status-changed events and queued requester mail notifications.
- Replaced placeholder frontend performance metadata with a 200ms render budget and 20-query frontend budget.
- Added a package README covering implemented frontend, support, provider, safety, and testing workflows.
- Wired the advertised `portal-profile` capability through `ResolvePortalProfileAction`, profile providers, manifest discovery, and the authenticated dashboard profile section.
- Enforced `PortalAccountStatus` in the authenticated account resolver so suspended and archived portal accounts receive a forbidden response before dashboard, preference, or support workflows run.

### 2026-06-03

- Scoped the admin support-request triage resource to the current actor's assigned sites so one site's admin can no longer read another site's decrypted support requests on a multi-site install.
- Implemented the `customer-portal.package-health` check probe: it now verifies the required `portal_accounts` and `portal_support_requests` tables exist and that the `PortalAccount` and `PortalSupportRequest` models resolve, instead of being a stub.
- Rewrote the marketplace summary and description (and aligned the Composer description) to lead with the unified self-service hub value proposition.
