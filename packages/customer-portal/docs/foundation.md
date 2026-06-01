# Customer Portal Foundation

The Customer Portal package now includes authenticated frontend routes for a minimal self-service dashboard. It does not publish authoring markers, signed editor URLs, admin selectors, or package internals into frontend output.

## Scope

- `PortalAccount` stores the authenticated customer identity boundary for a site.
- `PortalSupportRequest` stores self-service support submissions connected to a portal account.
- Profile, preferences, and dashboard item contracts let Access Gate, payments, document lifecycle, events, and newsletter packages contribute later without Customer Portal importing their internals.
- Self-service item contracts let owning packages contribute gated resources, payments, documents, event registrations, newsletter preference links, and support items without Customer Portal importing their internals.
- Actions are the entry points for creating accounts, updating preferences, submitting support requests, and resolving dashboard items.
- `ResolvePortalSelfServiceItemsAction` aggregates typed self-service items for the authenticated dashboard.
- Authenticated frontend controllers resolve the current user into a portal account, render dashboard items, allow preference updates, and submit support requests.
- A Filament admin resource lists and edits portal support requests for triage. Status transitions go through `UpdateSupportRequestStatusAction` so resolved and closed timestamps remain consistent.

## Next Slices

- Add provider adapters in the owning packages for gated resources, orders/payments, documents, event registrations, and newsletter preferences.
- Expand public-output safety tests as additional Blade or frontend assets are introduced.
