# Capell Theme Rebuild Prompts

Twenty-eight ready-to-run prompts for building new Capell themes, each modelled on
a real site. Every prompt is a self-contained brief that tells Claude (or a dev)
exactly how to ship the theme as a proper Capell package: identity, design tokens,
sections, **rich demo/beta data**, build steps, and acceptance tests.

- Read [`_shared-build-contract.md`](_shared-build-contract.md) once — it holds the
  build contract every prompt depends on.
- The authoritative engineering guide is [`../creating-a-theme.md`](../creating-a-theme.md).
- Then open the individual prompt you want and follow it end to end.

## How these were chosen

Two inputs:

1. **The current theme set** (reviewed below) — to find the gaps.
2. **Points of inspiration** — the sites open in the browser tab group, plus
   recommended additions. The tabs skew heavily toward AI, developer tooling,
   fintech/crypto, hardware, and refined studio/personal sites — exactly the
   verticals the current themes do **not** cover. Tooling tabs (ChatGPT, Stitch,
   ui-skills, the YouTube talk, impeccable.style) and Capell's own site were used
   as design-process references, not as theme archetypes.

## Current themes (the baseline)

| Theme key                        | Covers                                           |
| -------------------------------- | ------------------------------------------------ |
| `default` (foundation)           | Shared runtime + default look                    |
| `agency`                         | Bold creative studio / campaign launch pages     |
| `commerce`                       | Editorial retail / catalogue / product discovery |
| `corporate`                      | Restrained B2B / public sector / professional    |
| `education`                      | Courses, instructors, admissions, enrolment      |
| `estate-agents`                  | Property search, listings, valuations (resale)   |
| `healthcare`                     | Appointment-led care, clinicians, services       |
| `inertia-bookings` (+react/+vue) | Services + the Bookings request flow             |
| `knowledge`                      | Docs, knowledge bases, topic hubs                |
| `liquid-glass`                   | Glass visual style on the standard section set   |
| `local-services`                 | Trades, service areas, quote-led local journeys  |
| `nonprofit`                      | Campaigns, donations, volunteering, impact       |
| `portfolio`                      | Creator/consultant work grids + case studies     |
| `restaurant`                     | Menus, reservations, private dining, hours       |
| `saas`                           | Product-led software: pricing, comparison, demo  |

### Gap analysis

What the baseline is missing, and what the prompts add:

- **AI-native products** — no theme speaks to frontier models, AI APIs, AI agents,
  or AEO/analytics dashboards. (Prompts 01–04.)
- **Fintech, crypto, quant** — no theme handles compliance/trust, DeFi token
  metrics, or trading performance disclosures. (05–07.)
- **Developer tooling / open source** — `saas` is marketing-led, not
  install-command / SDK / changelog led. (08.)
- **Hardware & industrial** — nothing for robotics, manufacturing, or physical
  product/packaging suppliers with specs and RFQs. (09–11.)
- **Events & media** — no conference, podcast, or newsroom/magazine theme.
  (12–14.)
- **Refined studios & personal brands** — `agency` is loud; nothing covers
  quiet interior/architecture studios, engineering-led product studios, or
  minimal personal developer/creator sites. (15–18, 28.)
- **High-trust professional verticals** — no law, financial-advisory,
  construction, fitness, beauty, travel, automotive, property-development, or
  recruitment theme. (19–27.)

## The 28 prompts

| #   | Theme key             | Modelled on (inspiration)                        | Type            |
| --- | --------------------- | ------------------------------------------------ | --------------- |
| 01  | `ai-lab`              | x.ai, ElevenLabs FLUX.2                          | New vertical    |
| 02  | `api-platform`        | Cartesia Sonic, Clerk                            | New vertical    |
| 03  | `ai-agent`            | Fin.ai, Humble (smart manufacturing AI)          | New vertical    |
| 04  | `aeo-analytics`       | Profound                                         | New vertical    |
| 05  | `fintech-trust`       | Baselayer, Stripe                                | New vertical    |
| 06  | `crypto-defi`         | Loopscale, Tars                                  | New vertical    |
| 07  | `quant-trading`       | Blackalgo                                        | New vertical    |
| 08  | `devtool-oss`         | Cal.com, Clerk                                   | New vertical    |
| 09  | `robotics-hardware`   | Sunday Robotics                                  | New vertical    |
| 10  | `manufacturing`       | Humble                                           | New vertical    |
| 11  | `packaging-supplier`  | Yucca                                            | Niche vertical  |
| 12  | `conference-event`    | Stripe Sessions 2026                             | New vertical    |
| 13  | `podcast-show`        | Recommended                                      | New vertical    |
| 14  | `newsroom-magazine`   | Zapier blog / recommended                        | New vertical    |
| 15  | `design-studio`       | Groth Studio, Sketch                             | New vertical    |
| 16  | `product-studio`      | Bejamas, Drewl                                   | Niche of agency |
| 17  | `personal-dev`        | emilkowal.ski                                    | New vertical    |
| 18  | `creator-newsletter`  | Recommended                                      | New vertical    |
| 19  | `law-firm`            | Recommended                                      | Niche vertical  |
| 20  | `financial-advisory`  | Recommended                                      | Niche vertical  |
| 21  | `construction-trades` | Recommended (niche of local-services)            | Niche vertical  |
| 22  | `fitness-wellness`    | Recommended                                      | Niche vertical  |
| 23  | `beauty-spa`          | Recommended                                      | Niche vertical  |
| 24  | `travel-tourism`      | Recommended                                      | Niche vertical  |
| 25  | `automotive-dealer`   | Legalshowplates (automotive niche) / recommended | Niche vertical  |
| 26  | `property-developer`  | Recommended (complements estate-agents)          | Niche vertical  |
| 27  | `recruitment-jobs`    | Recommended                                      | New vertical    |
| 28  | `editorial-serif`     | Sketch / Groth / emilkowal.ski refinement        | Style theme     |

Each row links to its prompt file `NN-<key>.md` in this folder.

> Companion file: [`30-reference-inspired-capell-theme-prompts.md`](30-reference-inspired-capell-theme-prompts.md)
> is an earlier, separate brainstorm of design-gallery / awards-directory style
> prompts (looser, not tied to the build contract). The 28 prompts above are the
> build-ready ones: each maps to a concrete package, design tokens, sections, demo
> data, and acceptance tests.

## Using a prompt

1. Open the prompt file. Skim the inspiration + gap so you know the intent.
2. Hand the whole file to Claude (or build by hand) alongside
   `_shared-build-contract.md`.
3. Scaffold the package, fill the demo profile with the prompt's beta data, wire
   the sections, register autoload in both composer files.
4. Run the prompt's verification commands. Confirm anonymous output is editor-free.
