# Equestrian Clinics Docs

Use these docs when installing, reviewing, or extending the Equestrian Clinics package.

## Documents

| Document                                  | Use                                                                                                  |
| ----------------------------------------- | ---------------------------------------------------------------------------------------------------- |
| [Overview](overview.md)                   | Feature scope, workflow, technical shape, data model, install impact, and next implementation layer. |
| [Screenshot contract](screenshots.json)   | Deployment screenshot runner targets for public discovery, coach timetable, and marketplace assets.  |
| [Marketplace assets](assets/marketplace/) | Committed marketplace artwork used by the extension listing.                                         |

## Review Checklist

- Confirm the package is both Composer-installed and marked installed in Capell before expecting public routes.
- Confirm public Blade stays free of authoring metadata, admin URLs, signed editor URLs, private rider data, and medical disclosure output.
- Confirm docs and manifest claims distinguish implemented Actions/routes from future Filament, portal, checkout, messaging, and automation layers.
- Run focused tests with `vendor/bin/pest packages/equestrian-clinics/tests --configuration=phpunit.xml`.
