# Knowledge Base Docs

Knowledge Base includes collections, versioned articles, public documentation routes, related articles, reader feedback capture, search document output, and AI-readable article output.

The package owns the content models, admin resources, public docs routes, feedback workflow, search document DTOs, and AI-readable output. Theme packages can render these public payloads without Knowledge Base storing theme-specific markup.

## Demo

`capell:knowledge-base-demo` seeds a compact help-centre fixture:

- Getting Started and Operations collections.
- Published Installing Capell and Cache checklist articles.
- A next-step related article link.
- Positive and negative reader feedback for aggregate displays.

The fixture uses semantic article HTML only and does not write presentation wrappers or theme classes into content fields.

## Screenshots

`docs/screenshots.json` documents four runner-backed captures. `capell.json` promotes the two Capell admin screenshots for the marketplace gallery and keeps the two public `/docs` route captures as supporting evidence.
