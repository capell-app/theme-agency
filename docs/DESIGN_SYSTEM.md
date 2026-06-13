# Capell Documentation Design System

This file defines the documentation layout system for Capell package docs. It is
not a UI component library. Use it to keep package READMEs, overview pages,
screenshots, and generated reference docs consistent across the package repo.

## Page Shape

Each package README should use this order unless the package has no matching
surface:

1. `# Package Name`
2. `## What This Plugin Adds`
3. `## Why It Matters`
4. `## Screens And Workflow`
5. `## Technical Shape`
6. `## Data Model`
7. `## Install Impact`
8. `## Common Pitfalls`
9. `## Quick Start`
10. `## Next Steps`

Use `Plugin` in headings because the marketplace and extension language uses
plugin-facing copy, but describe the Composer package precisely in prose.

## Overview Length

The first section should be practical and short:

- Explain the job the package does.
- Name the Capell surface it extends.
- Say whether the package is available, optional, pipeline, premium,
  marketplace-owned, schema-owning, or no schema impact where useful.
- Keep the section under 500 words.

## Screenshots

Every package should plan screenshots before claiming a workflow is documented.
Use the committed `docs/screenshots.json` file as the source of truth when it
exists.

Expected screenshot plan:

- admin index screen
- create/edit screen
- settings/configuration screen if relevant
- frontend output if relevant
- package detail or install intent screen if marketplace-owned
- carousel steps if the plugin has a multi-step workflow

If the package has no visible UI, say so and document the command, route,
contract, or generated output that proves the workflow.

## Tables

Use tables only for compact reference data:

- package identity
- commands
- routes
- config keys
- tables
- permissions
- troubleshooting

Avoid large prose tables for package value. Use bullets instead.

## Diagrams

Use Mermaid diagrams for schema or workflow when the package owns more than one
model or has a multi-step external flow. Keep diagrams grounded in migrations,
models, or route/controller flow.

If a diagram would require guessing relationships, add `Docs gap:` and point to
the missing model, migration, or test coverage.

## Link Rules

- Link package-local docs from `## Next Steps`.
- Link cross-package references with relative paths.
- Link generated reports from `docs/README.md`.
- Do not link consolidated tombstone docs from active package READMEs.
- Keep screenshot paths relative to the package docs directory.

## Generated Docs

Generated files must include:

- the generator script path
- enough plain-language context for the intended reader
- a deterministic structure
- a check command that fails when the file is stale

Generated reference tables may include technical internals. If the intended
reader is a site owner, add a plain-language summary before the technical table.
