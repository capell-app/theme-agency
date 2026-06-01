# Capell Bookings

Bookings provides appointment request workflows for Capell sites.

## Included Capabilities

- Services with durations, buffers, lead times, and future booking windows.
- Staff members with opaque calendar feed tokens.
- Physical, virtual, phone, on-site, and to-be-confirmed locations.
- Weekly availability windows and date-specific availability exceptions.
- Public appointment request forms backed by package Actions.
- Confirmation, cancellation, audit log, notification, and reminder workflows.
- Staff calendar feeds that export confirmed appointments without exposing admin internals.

## Package Boundaries

Bookings owns appointment setup, public request handling, and calendar exports. Public frontend routes must stay no-store and must not expose Filament resources, package internals, authoring metadata, or admin-only identifiers.
