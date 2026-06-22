# AI Creator Overview

AI Creator is a premium first-party Capell extension for reviewed AI-assisted content creation on an ordinary existing Capell site.

It is intentionally not positioned as a standalone site builder. The primary workflow is helping editors and admins create better pages, content plans, layout plans, reusable sections, and structured records inside the site they already manage. Site, domain, and app-local theme creation can be added where the installed package set supports those operations.

## Buyer value

- Start a guided creation session from a plain-language intent.
- See a deterministic preview before anything is applied.
- Understand which Capell extensions make the requested outcome better.
- Keep AI-generated output behind reviewed Actions instead of executing generated code.
- Preserve Capell public-output safety rules for cached HTML and anonymous visitors.

## Extension surfaces

- Admin: AI Creator session list and reviewed creation workflow.
- Agent Bridge: start, preview, and apply session capabilities.
- Persistence: `capell_ai_creator_sessions`.

## Works better with

AI Creator recommends packages as required, recommended, or optional. Layout Builder is required for complex page composition. Content Sections, Structured Content Library, Media Library, Media AI, SEO Suite, Publishing Studio, Navigation, Search, Blog, Form Builder, Newsletter, Events, Campaign Studio, Frontend Authoring, HTML Cache, Frontend Optimizer, Insights, GA4 Reports, and Site Monitor are recommended when the session intent makes them useful.

## Safety caveats

- v1 must not silently install optional packages.
- AI output may generate drafts and plans, but deterministic Actions apply changes.
- No raw AI-generated PHP or Blade may execute directly.
- App-local theme file writes stay disabled in production by default.
- Public Blade and cached HTML must never leak authoring metadata.

## Screenshot notes

Marketplace screenshots are deferred until the Filament session UI is implemented. The current slice is infrastructure-first: persistence, recommendations, Agent Bridge registration, and focused tests.
