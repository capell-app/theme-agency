# Capell Content Language Plan

This is the shared vocabulary for all Capell package documentation. Use it so every README, overview, and admin guide reads in one consistent voice. It covers two audiences: developers and owners who read the README, and non-technical editors and operators who read the admin docs.

Two related guides sit alongside this one:

- [Package Documentation Standard](package-documentation-standard.md) for the README and admin-guide shapes and the strict audit rules.
- [Documentation Design System](DESIGN_SYSTEM.md) for structure, screenshots, and generated-docs rules.

## Status labels

Use these exact words for package status, and nothing else:

| Label | Meaning |
| --- | --- |
| Available | Shipped and supported. Safe to install and operate. |
| Pipeline | In development. Documented but not yet ready to depend on. |
| Deprecated | Still present but scheduled for removal. Do not adopt for new work. |

## Proof rules (developer and owner docs)

Every claim must be backed by something real in the package. Do not describe behaviour the code does not have.

- Name the real surface: the Action, Data object, route name, command signature, table, setting, or extension point.
- State outcomes, not adjectives. Say what the package lets a team do, not how good it is.
- If a capability does not exist yet, say so plainly instead of implying it ships.

## Banned phrasing

Do not use vague or promotional words. The strict docs audit fails on these in `README.md` and `docs/overview.md`:

`powerful`, `seamless`, `future-proof`, `all-in-one`, `game-changing`, `best-in-class`, `supercharge`, `unlock`.

Also avoid empty restatements ("Blog adds a blog") and unsupported superlatives.

| Weak | Better |
| --- | --- |
| Adds generic content tools. | Adds article, archive, and tag page types so teams can publish editorial content without custom page schemas. |
| Improves performance. | Indexes cached page URLs and exposes admin cache widgets so operators can see stale HTML and refresh affected pages. |
| Powerful Shopify integration. | Stores site-scoped Shopify connections and syncs products into local tables for admin-side catalog search. |

## Admin voice (overview.md and admin-guide.md)

Admin docs are written for a non-technical editor or owner who operates the feature. They must read as plain instructions, never as developer reference.

Phrasing rules:

- Use the real on-screen label, in bold: "Click **Request review**", not "submit it for approval".
- Lead with the user's goal, not the feature: "How to schedule a post for next week" beats "Using the scheduler".
- Name the nav path: "Go to **Content > Articles**." Use `>` (a plain greater-than) between levels, not an arrow.
- Use plain verbs: create, edit, schedule, preview, approve, publish, restore, export, filter.
- State the safety or visibility fact plainly: "Visitors never see a draft", "Old logs are removed after the number of days you set".

Never put any of these in `overview.md` or `admin-guide.md`:

- Class names, table names, Action names, model IDs, field paths, selectors, or migration filenames.
- `composer require`, `php artisan`, or any install command (those belong in the README).
- Fenced code blocks. Use short inline examples instead.
- Non-ASCII punctuation (em dashes, curly quotes). Use plain hyphens and straight quotes.

## Tiering

Coverage is tiered by how much an owner or editor operates the package. See the [Package Documentation Standard](package-documentation-standard.md#admin-documentation) for the tier table and which packages get a full `admin-guide.md`.
