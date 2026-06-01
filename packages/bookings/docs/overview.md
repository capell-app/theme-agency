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

## Traceability

The package manifest declares admin resources, models, frontend routes, migrations, actions, and capabilities. `contributionTraceability.deferredContributions` is intentionally empty because the current appointment-request, notification, reminder, and calendar-feed scope is implemented by package-owned code.
