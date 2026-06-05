# Bookings Overview

Bookings supports healthcare, local services, education, consulting, nonprofit, and portfolio appointment workflows.

The package includes first-class records for:

- Services
- Staff members
- Locations
- Availability windows
- Availability exceptions
- Appointment requests
- Appointment audit logs

## Workflow

Visitors submit public appointment requests through the package route. Requests are validated against service lead times, future booking windows, weekly availability, date-specific exceptions, capacity, and service buffers. Operators confirm or cancel requests in admin resources, and the workflow records audit logs and queues translated notifications.

Confirmed appointments can be exported through opaque staff calendar feed URLs.

## Screenshot Plan

`docs/screenshots.json` describes the required Marketplace capture set:

- the public booking request route with service, staff, location, timezone, and customer fields;
- the appointment request admin queue showing request workflow state and audit context.

The committed SVG assets under `docs/assets/marketplace/` are interim gallery previews. Replace or supplement them with route-backed PNG captures before final Marketplace approval.

## Traceability

The package manifest declares admin resources, models, frontend routes, migrations, actions, and capabilities. `contributionTraceability.deferredContributions` is intentionally empty because the current appointment-request, notification, reminder, and calendar-feed scope is implemented by package-owned code.
