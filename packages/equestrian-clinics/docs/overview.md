# Equestrian Clinics Overview

Equestrian Clinics is a premium Capell Operations package for tour-day and riding-school workflows. It is intentionally more specific than generic Bookings: it understands riders, horses, waivers, venues, resources, clinic credits, facility reports, and mobile coach delivery.

## V1 Feature Families

- Tour days and clinics.
- Public discovery and nearest-event search.
- Stripe, PayPal, guarded payment fees, and approved-cash workflows.
- Family rider profiles and horse profiles.
- Horse allocation, suitability, workload limits, and resource conflicts.
- Facility resources, add-ons, host requests, and facility reports.
- Waivers, intake snapshots, compliance exports, and deletion support.
- Clinic credits, lesson packs, memberships, gift cards, promotions, and referrals.
- Mobile coach timetable, attendance, no-shows, broadcasts, and coaching vault.
- Messaging, reviews, marketing links, analytics, AI prompts, and integration hooks.

## Architecture

The package owns equestrian-specific domain records and delegates generic engine behaviour to existing Capell packages:

- Bookings handles appointment holds, confirmations, cancellation windows, travel-aware schedules, and lesson links.
- Events handles public calendar/discovery, occurrences, venue listings, capacity, waitlists, and feeds.
- Payments handles Stripe and PayPal checkout/refund records.
- Customer Portal, Address, and Media Library provide account, geocoding, and private media surfaces.

## Safety

Public output must not expose admin state, private rider/horse data, medical disclosures, waiver snapshots, facility host-only notes, or signed media URLs. Host reports are explicit safe projections.
