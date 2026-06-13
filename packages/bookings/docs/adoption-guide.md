# Bookings Adoption Guide

<!-- prettier-ignore-start -->

Use this guide when deciding how much of Bookings to enable. The package has a lot of capability, but a good install should feel calm: start with the workflow the team already understands, then add automation when it removes real work.

## Default Setup

Start here for most sites:

1. Create services with clear durations and lead times.
2. Add staff, locations, and availability windows.
3. Verify the public booking request form.
4. Train operators on the appointment request queue.
5. Confirm that audit logs and message logs are understandable.

This gives the site owner the core outcome: customers request appointments through the site, and the team manages those requests in Capell.

## Add When Needed

| Need | Add |
| --- | --- |
| Customers need appointment history or lesson feedback | Portal lesson links and review requests. |
| Customers need to approve changes | Change proposals. |
| Missed messages are a problem | Consent-aware reminders and message logs. |
| Capacity fills up | Waitlist entries and expiring offers. |
| Travel affects the day | Travel observations, work zones, day planner, and fuel reports. |
| Clinics or group sessions matter | Group sessions and attendance import. |
| Payments gate confirmation | Provisional holds, bundle credits, and payment fulfilment actions. |
| Quality needs monitoring | Multi-participant review loops, risk scoring, owner prompts, and retention pruning. |

## What Not To Enable First

- Do not turn on webhook providers before tokens and processors are configured.
- Do not expose payment-gated flows until the host app has a tested payment package path.
- Do not train operators on every resource at once. Start with appointment requests, then add waitlist, messages, and reviews.
- Do not hand-build customer links. Use the package Actions so signatures, expiry, and tokens stay correct.

## Operator Training Path

1. **Requests:** review new requests, confirm or cancel, and check the audit trail.
2. **Messages:** understand reminder status, skipped consent, failed delivery, and recipient fields.
3. **Reviews:** open review requests, check participants, and understand completed vs pending feedback.
4. **Waitlist:** add demand, offer a slot, and let expired offers age out.
5. **Planning:** use day planner, work zones, and travel observations when route planning matters.

The goal is not to make operators understand the package internals. The goal is to give each role the next useful screen.

## Developer Handoff

Before calling an install production-ready, confirm:

- generated customer URLs do not expose model IDs;
- webhook tokens are configured outside URL paths;
- public Blade has no authoring metadata or database queries;
- package tests and PHPStan pass;
- a consuming site has browser-tested `/bookings` at desktop and mobile widths;
- screenshots and Marketplace metadata match the installed surfaces.

<!-- prettier-ignore-end -->
