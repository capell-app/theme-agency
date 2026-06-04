---
title: 'Events Overview'
description: 'How the Capell Events package adds event management, recurring occurrences, registrations, calendar pages, and iCalendar feeds.'
---

# Events Overview

Events adds event records, venues, occurrences, registrations, admin calendar views, frontend event list/calendar pages, and iCalendar feeds to Capell.

## Hard Dependencies

- `capell-app/admin`
- `capell-app/frontend`
- `capell-app/navigation`
- `capell-app/publishing-studio`

Optional integrations are declared for address, form builder, SEO Suite, and tags.

## What It Adds

- Event, venue, occurrence, registration, and notification log tables.
- Admin resources for events, venues, occurrences, and registrations.
- Admin event calendar page and dashboard calendar widget.
- Frontend Livewire listing and calendar page components.
- Public `.ics` calendar feed routes.
- Event schema render hooks.
- Recurrence expansion through `rlanvin/php-rrule`.
- iCalendar generation through `spatie/icalendar-generator`.

## Admin Surfaces

| Surface                           | Purpose                                     |
| --------------------------------- | ------------------------------------------- |
| `EventResource` index/create/edit | Manage event records and recurrence fields. |
| `EventVenueResource`              | Manage venues.                              |
| `EventOccurrenceResource`         | Review generated occurrences.               |
| `EventRegistrationResource`       | Review, confirm, and cancel event registrations. |
| `EventCalendarPage`               | Calendar view under content navigation.     |
| `EventCalendarWidget`             | Dashboard/admin calendar widget.            |

## Frontend Surfaces

| Surface                         | Purpose                                             |
| ------------------------------- | --------------------------------------------------- |
| `EventsListingPage`             | Public list of upcoming events.                     |
| `EventsCalendarPage`            | Public calendar page.                               |
| `EventCalendar`                 | Livewire calendar component used by frontend pages. |
| `events.ics`                    | Global iCalendar feed route.                        |
| `events/{listingPage}/feed.ics` | Listing-specific iCalendar feed route.              |

Anonymous public output must not expose authoring controls, internal model IDs, editor URLs, or package internals.

## Registration Workflow

Native RSVP registrations start as pending or waitlisted depending on occurrence capacity. Staff can confirm or cancel registrations from `EventRegistrationResource`; cancelling a capacity-reserving registration reuses `UpdateRegistrationStatusAction`, refreshes the occurrence registration count, and promotes the next waitlisted attendee when capacity is available.

## Screenshot Coverage

The screenshot contract is stored in [screenshots.json](screenshots.json). Final capture should cover the admin resources, admin calendar, calendar widget, frontend listing/calendar pages, and feed route verification.

## Install And Verify

Install in a Capell app with the hard dependencies:

```bash
composer require capell-app/events
```

Then run:

```bash
vendor/bin/pest packages/events/tests --configuration=phpunit.xml
```
