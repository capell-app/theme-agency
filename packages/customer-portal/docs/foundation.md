# Customer Portal Foundation

The Customer Portal package now includes authenticated frontend routes for a minimal self-service dashboard. It does not publish authoring markers, signed editor URLs, admin selectors, or package internals into frontend output.

## Scope

- `PortalAccount` stores the authenticated customer identity boundary for a site.
- `PortalSupportRequest` stores self-service support submissions connected to a portal account.
- `PortalAccount::factory()` and `PortalSupportRequest::factory()` are available for package tests, demos, and consuming app test suites; support request factories keep `site_id`, account ownership, and requester email aligned by default.
- Profile, preferences, and dashboard item contracts let Access Gate, payments, document lifecycle, events, and newsletter packages contribute without Customer Portal importing their internals.
- Self-service item contracts let owning packages contribute gated resources, payments, documents, event registrations, newsletter preference links, and support items without Customer Portal importing their internals.
- Actions are the entry points for creating accounts, updating preferences, submitting support requests, and resolving dashboard items.
- `ResolvePortalSelfServiceItemsAction` aggregates typed self-service items for the authenticated dashboard.
- Authenticated frontend controllers resolve the current user into a portal account, render dashboard items, allow preference updates, and submit support requests.
- Suspended and archived portal accounts are blocked by `ResolveAuthenticatedPortalAccountAction` before frontend dashboard, preference, or support workflows run.
- A Filament admin resource lists and edits portal support requests for triage. Status transitions go through `UpdateSupportRequestStatusAction` so resolved and closed timestamps remain consistent.

## Integration

Owning packages register provider adapters through the Customer Portal registries. Access Gate, Payments, Document Lifecycle, Events, and Newsletter expose their own portal feed adapters while Customer Portal owns authentication, account/profile/preference storage, dashboard aggregation, and support request workflows.

Public-output safety tests should expand as additional Blade or frontend assets are introduced.
