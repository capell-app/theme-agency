# Capell Extension Suites

Extension suites are commercial and onboarding groupings. They do not rename Composer packages, and they do not turn optional integrations into hard dependencies. Package manifests should use `dependencies.supports` to expose implemented or documented optional pairings.

## Suite Catalog

| Suite              | Anchor                            | Attach Packages                                                                                              | Positioning                                                                                                                                                               |
| ------------------ | --------------------------------- | ------------------------------------------------------------------------------------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Layout Pro         | `capell-app/layout-builder`       | `block-library`, `content-sections`, `structured-content-library`, `frontend-authoring`, `publishing-studio` | Visual page composition, reusable sections, structured content records, and safe editor workflows.                                                                        |
| Media Pro / DAM    | `capell-app/media-library`        | `media-ai`, `seo-suite`, premium DAM depth                                                                   | Curator-backed media operations with missing-alt workflows, rights metadata, duplicate detection, usage drill-downs, and future visual focal/crop and conversion tooling. |
| Account Security   | `capell-app/password-policy`      | `login-audit`, `privacy-center`, `access-gate`, `diagnostics`                                                | Admin access hardening, login evidence, policy enforcement, consent/privacy evidence, and operational health checks.                                                      |
| AI Content Ops     | `capell-app/ai-orchestrator`      | `seo-suite`, `media-ai`, `translation-manager`, `content-sections`                                           | Shared AI governance for metadata generation, media assistance, translation drafting, and content operations.                                                             |
| Storefront         | `capell-app/shopify-commerce`     | `theme-commerce`, `payments`, `search`, `contacts`, `media-library`                                          | Shopify-backed catalog sync, product-aware theme surfaces, checkout/payment records, search, and customer data capture.                                                   |
| Migration & SEO    | `capell-app/migration-assistant`  | `wordpress-importer`, `url-manager`, `seo-suite`, `site-discovery`, `media-library`                          | Previewed imports, media migration, redirect preservation, public URL discovery, and SEO recovery after migrations.                                                       |
| Findability        | `capell-app/search`               | `seo-suite`, `site-discovery`, `url-manager`, `agent-delivery`                                               | Public search, canonical URL registry, AI/search-engine output, and redirect remediation.                                                                                 |
| Member Portal      | `capell-app/customer-portal`      | `access-gate`, `payments`, `document-lifecycle`, `events`, `newsletter`, `contacts`, `privacy-center`        | Logged-in customer self-service for billing, documents, gated access, registrations, support, and consent/privacy workflows.                                              |
| Courses / LMS Lite | `capell-app/theme-education`      | `events`, `bookings`, `form-builder`, `access-gate`, `customer-portal`, `payments`, `seo-suite`              | Course discovery, open days, applications, gated learner areas, appointments, and paid enrolment paths.                                                                   |
| Equestrian Ops     | `capell-app/equestrian-clinics`   | `bookings`, `events`, `payments`, `customer-portal`, `address`, `media-library`, `email-studio`              | Tour days, clinic discovery, riders, horses, waivers, payments, facilities, host requests, and coach day-of operations.                                                   |
| Hospitality        | `capell-app/theme-restaurant`     | `bookings`, `form-builder`, `events`, `blog`, `seo-suite`, `contacts`                                        | Menu-led venue sites, reservation capture, private dining, events, opening hours, and local discovery.                                                                    |
| Local Business     | `capell-app/theme-local-services` | `address`, `bookings`, `form-builder`, `seo-suite`, `events`, `contacts`                                     | Service-area pages, quote/contact workflows, bookings, location data, local SEO, and lead capture.                                                                        |
| Property           | `capell-app/theme-estate-agents`  | `search`, `address`, `form-builder`, `blog`, `seo-suite`, `contacts`                                         | Property search, featured listings, vendor valuations, local-area guides, agent proof, and viewing requests.                                                              |

## Manifest Rules

- Use `product.group`, `product.bundle`, and `product.tier` for first-party marketplace grouping.
- Use `dependencies.supports` for optional suite pairings that have real code, docs, or package-owned extension points.
- Do not add suite packages to `dependencies.requires` unless the package cannot boot or deliver its core feature without that package.
- Keep marketplace summaries package-specific. Suite language belongs in overview docs, package "best used with" sections, or future marketplace bundle metadata.

## Completion Notes

- Layout Pro, Media Pro / DAM, AI Content Ops, Storefront, Migration & SEO, Findability, Member Portal, Courses / LMS Lite, Equestrian Ops, and Local Business all rely on existing optional package boundaries rather than direct imports.
- Account Security is the main security/compliance upsell path for Operations packages and should stay separate from general Diagnostics positioning.
- Media Pro / DAM is intentionally a premium suite above the free Media Library foundation. The free package may expose data seams such as missing-alt candidates and usage drill-downs; visual editing, rich galleries, DAM taxonomy, and generated conversion tooling are premium depth.
