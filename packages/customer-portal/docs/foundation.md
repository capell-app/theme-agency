# Customer Portal Foundation

The Customer Portal package starts as a package-local domain foundation only. It does not publish public Blade views, routes, authoring markers, signed editor URLs, admin selectors, or package internals into frontend output.

## Scope

- `PortalAccount` stores the authenticated customer identity boundary for a site.
- `PortalSupportRequest` stores self-service support submissions connected to a portal account.
- Profile, preferences, and dashboard item contracts let Access Gate, payments, document lifecycle, events, and newsletter packages contribute later without Customer Portal importing their internals.
- Actions are the entry points for creating accounts, updating preferences, submitting support requests, and resolving dashboard items.

## Next Slices

- Add authenticated frontend routes and controllers that call these actions.
- Add provider adapters in the owning packages for gated resources, orders/payments, documents, event registrations, and newsletter preferences.
- Add Filament/admin surfaces for support request triage without exposing admin/editor state to public responses.
- Add public-output safety tests once Blade or frontend assets are introduced.
