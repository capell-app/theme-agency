# Bookings Package Implementation Plan

## Foundation Included

- Package scaffold under `packages/bookings` with runtime provider, manifest, translations, migrations, models, factories, and Pest coverage.
- Domain records for services, staff members, locations, weekly availability windows, and appointment requests.
- Backed enums for appointment status, availability status, and location type.
- DTOs and Actions for creating and confirming appointment requests.

## Next Slices

1. Admin resources for services, staff, locations, availability, and appointment requests.
2. Availability expansion that handles exceptions, holidays, capacity, buffers, lead times, and existing confirmed appointments.
3. Public request form integration with Form Builder or package-owned frontend components, keeping public output free of admin/editor internals.
4. Confirmation, cancellation, and reminder notifications with queued mail and audit logs.
5. Calendar exports for confirmed appointments using iCalendar feeds and per-staff calendar URLs.
6. Optional bridges for address records, Events-style calendar views, and healthcare/education/service theme presets without coupling to package internals.

## Constraints

- Keep Events as an idea reference only; Bookings owns its own models and actions.
- Public rendering must not expose model IDs, staff internals, admin URLs, or editor markers.
- Blade views must receive hydrated data and must not query.
