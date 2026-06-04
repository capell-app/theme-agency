# Customer Portal

Customer Portal turns a Capell site into an authenticated self-service hub. It gives signed-in customers a private dashboard for profile details, contributed self-service items, preferences, support requests, and recent support history without leaking admin/editor internals into frontend output.

## Features

- Authenticated frontend routes under `/portal` using the configured `web` and `auth` middleware.
- Site-scoped `PortalAccount` records with encrypted email, display name, profile, and preference storage.
- A profile surface powered by `ResolvePortalProfileAction` and `PortalProfileProviderRegistry`.
- Dashboard and self-service item registries so packages such as payments, document lifecycle, events, newsletter, and access-gate can contribute cards and feed items without Customer Portal importing their internals.
- Preference updates through `UpdatePortalPreferencesAction`, with option rendering and validation driven by the package preference schema.
- Support request submission through `SubmitSupportRequestAction`, with encrypted request details and requester email hashing.
- Support request submitted/status-changed events and queued requester mail notifications.
- Admin support-request triage through a Filament resource scoped to the current actor's assigned sites.
- Real package health diagnostics for required tables and model resolution.
- Package factories for `PortalAccount` and `PortalSupportRequest`.

## Frontend Safety

The dashboard is a private customer surface. Responses send `no-store` and `noindex` headers, and tests assert the rendered output does not expose package names, signed editor URLs, Filament internals, account ids, or unsafe profile fields. Public Blade receives hydrated arrays from controllers and Actions; it should not query models directly.

`capell.json` declares a 200ms frontend render budget and a 20-query frontend budget. Provider adapters should keep expensive lookups out of Blade and return already-hydrated `PortalDashboardItemData`, `PortalSelfServiceItemData`, and `PortalProfileData` objects.

## Extension Points

Register provider adapters through the package registries:

- `PortalProfileProviderRegistry` for customer-facing profile fields.
- `PortalDashboardItemRegistry` for dashboard cards.
- `PortalSelfServiceItemRegistry` for recent payments, documents, gated resources, event registrations, newsletter links, and support items.
- `PortalPreferencesProviderRegistry` for preference-schema work.

Provider output is escaped in Blade. Keep labels, descriptions, URLs, and profile values customer-facing, and do not include internal ids, tokens, admin URLs, selectors, or authoring metadata.

## Support Workflow

Customers can submit support requests from the dashboard. The package stores request subject, message, requester email, context, status, and priority in encrypted columns where appropriate. Submissions emit `PortalSupportRequestSubmitted`; status transitions emit `PortalSupportRequestStatusChanged` only when the status changes.

Requester notifications are sent on demand through Laravel's notification system when a requester email address is available.

## Testing

Run the package tests directly from the monorepo root:

```bash
vendor/bin/pest packages/customer-portal/tests --configuration=phpunit.xml
vendor/bin/phpstan analyse packages/customer-portal/src packages/customer-portal/tests --configuration=phpstan.neon --memory-limit=1G
vendor/bin/pint --test packages/customer-portal/src packages/customer-portal/tests
```

Use `vendor/bin/pest` directly in this repository; do not run `php artisan` here.
