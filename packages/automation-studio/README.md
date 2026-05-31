# Automation Studio

Automation Studio is the rule orchestration layer for Capell package events.

This first package slice is intentionally database-free. Packages or future admin resources can register `AutomationRuleData` objects against the package-local `AutomationRuleRegistry`, and Automation Studio will dispatch matching trigger events to registered action handlers.

## Current foundation

- Triggers: form submitted, access approved, page published, campaign converted.
- Actions: send email, webhook, tag contact, create note, subscribe user, queue agent capability, run Public Action.
- Built-in handler: `public_action`, which delegates to Public Actions when that package is available.
- Package-local listeners normalize optional package events without editing those packages.

See `docs/automation-studio.md` for the implementation plan and next slices.
