# Changelog

All notable changes to `capell-app/customer-portal` will be documented in this file.

## Unreleased

### 2026-06-03

- Scoped the admin support-request triage resource to the current actor's assigned sites so one site's admin can no longer read another site's decrypted support requests on a multi-site install.
- Implemented the `customer-portal.package-health` check probe: it now verifies the required `portal_accounts` and `portal_support_requests` tables exist and that the `PortalAccount` and `PortalSupportRequest` models resolve, instead of being a stub.
- Rewrote the marketplace summary and description (and aligned the Composer description) to lead with the unified self-service hub value proposition.
