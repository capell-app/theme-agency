# Changelog

All notable changes to `capell-app/events` will be documented in this file.

## Unreleased

- Added configurable recurrence sync window bounds and DST-crossing recurrence coverage.

### 2026-06-04

- Added confirm and cancel row actions to the event registration admin resource; cancellation now reaches the existing waitlist-promotion workflow from the shipped staff surface.
- Declared the Events resource permissions in `capell.json` so the manifest matches the registered event, venue, occurrence, and registration policies.
- Documented registration workflow management in the package docs.

- Prepared package metadata and documentation for ongoing Capell 4.x package work.

## 2026-06-03

- Replaced the stubbed `EventsHealthCheck` with real diagnostics covering recurrence expansion, registration capacity storage, the iCal feed library, and schema.org Event structured data, including pass/fail test coverage.
- Rewrote the marketplace summary and description (manifest and Composer) to convey buyer outcomes and key capabilities instead of a flat feature list.
