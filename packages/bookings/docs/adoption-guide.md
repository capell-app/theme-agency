# Bookings Adoption Guide

<!-- prettier-ignore-start -->

The operator and owner rollout for Bookings now lives in the [admin guide](admin-guide.md): how to set up services and availability, how to confirm an appointment request, what to turn on first, what to add when needed, and what not to enable first.

This page keeps only the developer handoff checklist below.

## Developer Handoff

Before calling an install production-ready, confirm:

- generated customer URLs do not expose model IDs;
- webhook tokens are configured outside URL paths;
- public Blade has no authoring metadata or database queries;
- package tests and PHPStan pass;
- a consuming site has browser-tested `/bookings` at desktop and mobile widths;
- screenshots and Marketplace metadata match the installed surfaces.

<!-- prettier-ignore-end -->
