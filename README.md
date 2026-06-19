# Capell Packages

![Capell Packages GitHub banner](docs/assets/github/capell-packages-banner.png)

First-party optional packages for Capell CMS. This repository is the package workspace beside the host application in `../capell-4`; install only the packages a project needs.

Package pages are published at `docs.capell.app/packages/<package>/`. Each package page should be useful on its own for developers searching for a specific Capell capability, and should link to deeper docs when the package owns setup, data, workflow, API, or extension details.

This repository is the source of truth for first-party package code. Individual package repositories under `capell-app/<package>` are generated splits for Composer installs and focused collaborator PRs. Maintainers merge forwarded package changes in this monorepo, not in the split repositories.

Each package README follows the same shape:

- At a glance: Composer name, namespace, runtime surfaces, service providers, and package dependencies.
- What it adds: the editor, frontend, console, queue, or integration behaviour the package owns.
- Code map: the package directories future changes should inspect first.
- Surfaces: Filament, Livewire, HTTP, commands, persistence, extension points, docs, and tests.

## Find A Package By Job

| Job                                            | Start with                                                                                                                                                                                                                                                      | Why                                                                                               |
| ---------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------- |
| Build editable pages and reusable layouts      | [layout-builder](packages/layout-builder/README.md), [content-sections](packages/content-sections/README.md), [hero](packages/hero/README.md)                                                                                                                   | Owns layout containers, elements, content sections, and starter visual content.                   |
| Publish articles, archives, and tagged content | [blog](packages/blog/README.md), [tags](packages/tags/README.md), [site-discovery](packages/site-discovery/README.md)                                                                                                                                           | Adds article workflow, taxonomy, public archives, and sitemap discovery.                          |
| Add campaign and conversion reporting          | [campaign-studio](packages/campaign-studio/README.md), [insights](packages/insights/README.md), [ga4-reports](packages/ga4-reports/README.md)                                                                                                                   | Connects landing pages, goals, first-party events, and GA4 snapshots.                             |
| Improve technical SEO and search               | [seo-suite](packages/seo-suite/README.md), [search](packages/search/README.md), [site-discovery](packages/site-discovery/README.md), [url-manager](packages/url-manager/README.md)                                                                              | Covers metadata, structured data, sitemaps, public search, redirects, and checks.                 |
| Expose public content to agents                | [agent-delivery](packages/agent-delivery/README.md), [site-discovery](packages/site-discovery/README.md), [seo-suite](packages/seo-suite/README.md)                                                                                                             | Adds public-safe manifests, semantic chunks, and discovery outputs.                               |
| Prepare demos, screenshots, and fixture sites  | [demo-kit](packages/demo-kit/README.md), [foundation-theme](packages/foundation-theme/README.md)                                                                                                                                                                | Generates repeatable demo sites, package demo content, and frontend theme output.                 |
| Preview unsaved page edits                     | [filament-peek](packages/filament-peek/README.md), [frontend-authoring](packages/frontend-authoring/README.md), [publishing-studio](packages/publishing-studio/README.md)                                                                                       | Separates temporary editor preview state from saved public pages.                                 |
| Add public comments and moderation             | [comments](packages/comments/README.md), [blog](packages/blog/README.md), [email-studio](packages/email-studio/README.md)                                                                                                                                       | Adds moderated frontend discussion and admin review tools.                                        |
| Tighten admin operations and access controls   | [diagnostics](packages/diagnostics/README.md), [site-monitor](packages/site-monitor/README.md), [dashboard-reports](packages/dashboard-reports/README.md), [password-policy](packages/password-policy/README.md), [login-audit](packages/login-audit/README.md) | Adds health checks, uptime checks, dashboard signals, password enforcement, and login visibility. |
| Run customer and commerce workflows            | [customer-portal](packages/customer-portal/README.md), [contacts](packages/contacts/README.md), [bookings](packages/bookings/README.md), [payments](packages/payments/README.md), [equestrian-clinics](packages/equestrian-clinics/README.md)                   | Adds self-service, CRM records, appointment requests, vertical operations, and payment flows.     |

## Package Index

### Foundation And Content

| Package                                                                     | Composer package                        | Purpose                                                                                            |
| --------------------------------------------------------------------------- | --------------------------------------- | -------------------------------------------------------------------------------------------------- |
| [address](packages/address/README.md)                                       | `capell-app/address`                    | Country, region, and address data for Capell forms and admin records.                              |
| [block-library](packages/block-library/README.md)                           | `capell-app/block-library`              | Shared typed content block primitives used by layout and content packages.                         |
| [blog](packages/blog/README.md)                                             | `capell-app/blog`                       | Article publishing, archive pages, tag pages, article elements, and sitemap contributions.         |
| [content-sections](packages/content-sections/README.md)                     | `capell-app/content-sections`           | Reusable content section records and Livewire rendering.                                           |
| [events](packages/events/README.md)                                         | `capell-app/events`                     | Event records, venues, occurrences, registrations, calendar pages, and iCalendar feeds.            |
| [hero](packages/hero/README.md)                                             | `capell-app/hero`                       | Default home-page hero element rendering and setup.                                                |
| [knowledge-base](packages/knowledge-base/README.md)                         | `capell-app/knowledge-base`             | Collections, articles, public docs routes, feedback, search output, and AI-readable docs.          |
| [media-library](packages/media-library/README.md)                           | `capell-app/media-library`              | Awcodes Curator backend integration for Capell media.                                              |
| [navigation](packages/navigation/README.md)                                 | `capell-app/navigation`                 | Editor-managed menus for Capell frontend themes.                                                   |
| [notes](packages/notes/README.md)                                           | `capell-app/notes`                      | Contextual notes, assignments, mentions, and reminders.                                            |
| [structured-content-library](packages/structured-content-library/README.md) | `capell-app/structured-content-library` | Reusable business-content records for themes and content packages.                                 |
| [tags](packages/tags/README.md)                                             | `capell-app/tags`                       | Shared editor-controlled taxonomies.                                                               |
| [foundation-theme](packages/foundation-theme/README.md)                     | `capell-app/foundation-theme`           | Default frontend theme, asset pipeline, Blade directives, URL generation, and SVG media rendering. |

### Authoring And Publishing

| Package                                                       | Composer package                 | Purpose                                                                                    |
| ------------------------------------------------------------- | -------------------------------- | ------------------------------------------------------------------------------------------ |
| [filament-peek](packages/filament-peek/README.md)             | `capell-app/filament-peek`       | Private unsaved Page preview snapshots for Capell Admin.                                   |
| [frontend-authoring](packages/frontend-authoring/README.md)   | `capell-app/frontend-authoring`  | Authenticated admin in-page editing bridge for public frontend pages.                      |
| [frontend-optimizer](packages/frontend-optimizer/README.md)   | `capell-app/frontend-optimizer`  | Profile-based CSS and JavaScript delivery for public pages.                                |
| [html-cache](packages/html-cache/README.md)                   | `capell-app/html-cache`          | Static HTML cache, dependency indexing, and cache administration.                          |
| [publishing-studio](packages/publishing-studio/README.md)     | `capell-app/publishing-studio`   | Revisions, release workspaces, scheduling, approvals, previews, and controlled publishing. |
| [translation-manager](packages/translation-manager/README.md) | `capell-app/translation-manager` | File-based Laravel translation management for Capell and Filament panels.                  |
| [welcome-tour](packages/welcome-tour/README.md)               | `capell-app/welcome-tour`        | Optional Filament welcome tour for Capell Admin.                                           |

### Frontend Runtime

| Package                                                           | Composer package                   | Purpose                                                                    |
| ----------------------------------------------------------------- | ---------------------------------- | -------------------------------------------------------------------------- |
| [api](packages/api/README.md)                                     | `capell-app/api`                   | Public JSON delivery of published Capell page data for external renderers. |
| [inertia](packages/inertia/README.md)                             | `capell-app/inertia`               | Inertia runtime bridge for Capell frontend pages.                          |
| [inertia-react-adapter](packages/inertia-react-adapter/README.md) | `capell-app/inertia-react-adapter` | React adapter for Capell Inertia page rendering.                           |
| [inertia-vue-adapter](packages/inertia-vue-adapter/README.md)     | `capell-app/inertia-vue-adapter`   | Vue adapter for Capell Inertia page rendering.                             |

### Forms, Access, And Public Workflows

| Package                                               | Composer package             | Purpose                                                                                                    |
| ----------------------------------------------------- | ---------------------------- | ---------------------------------------------------------------------------------------------------------- |
| [access-gate](packages/access-gate/README.md)         | `capell-app/access-gate`     | Public access gates, entitlement checks, and gated delivery foundations.                                   |
| [comments](packages/comments/README.md)               | `capell-app/comments`        | Moderated public comment threads for registered Capell content.                                            |
| [contacts](packages/contacts/README.md)               | `capell-app/contacts`        | Encrypted CRM records, source adapters, merge workflows, and privacy actions.                              |
| [customer-portal](packages/customer-portal/README.md) | `capell-app/customer-portal` | Authenticated customer dashboard, preferences, support requests, and self-service registries.              |
| [form-builder](packages/form-builder/README.md)       | `capell-app/form-builder`    | Editor-managed forms, fields, submissions, validation, and notifications.                                  |
| [newsletter](packages/newsletter/README.md)           | `capell-app/newsletter`      | Audience management, subscriptions, consent state, imports, notifications, and public subscription routes. |
| [password-policy](packages/password-policy/README.md) | `capell-app/password-policy` | Password expiry, forced password changes, and password safety policy.                                      |
| [public-actions](packages/public-actions/README.md)   | `capell-app/public-actions`  | Reusable public submit actions, outbound automation dispatch, and integration endpoints.                   |

### Commerce And Appointments

| Package                                                     | Composer package                | Purpose                                                                                         |
| ----------------------------------------------------------- | ------------------------------- | ----------------------------------------------------------------------------------------------- |
| [bookings](packages/bookings/README.md)                     | `capell-app/bookings`           | Services, staff, locations, availability, appointment requests, and reminders.                  |
| [equestrian-clinics](packages/equestrian-clinics/README.md) | `capell-app/equestrian-clinics` | Tour days, riders, horses, venues, waivers, payments, facilities, credits, and coach workflows. |
| [payments](packages/payments/README.md)                     | `capell-app/payments`           | Stripe Checkout, webhook processing, paid downloads, and payment records.                       |
| [shopify-commerce](packages/shopify-commerce/README.md)     | `capell-app/shopify-commerce`   | Shopify OAuth connections, catalog sync, and commerce admin workflows.                          |

### Growth, Search, And Reporting

| Package                                                   | Composer package               | Purpose                                                                                       |
| --------------------------------------------------------- | ------------------------------ | --------------------------------------------------------------------------------------------- |
| [campaign-studio](packages/campaign-studio/README.md)     | `capell-app/campaign-studio`   | Campaign landing pages, CTA blocks, UTM attribution, conversion goals, and campaign insights. |
| [dashboard-reports](packages/dashboard-reports/README.md) | `capell-app/dashboard-reports` | Generic reporting widgets for Capell dashboards.                                              |
| [email-studio](packages/email-studio/README.md)           | `capell-app/email-studio`      | Transactional templates, delivery audit, provider events, replies, and suppressions.          |
| [automation-studio](packages/automation-studio/README.md) | `capell-app/automation-studio` | Rule-driven automation for package events and operator workflows.                             |
| [experiments](packages/experiments/README.md)             | `capell-app/experiments`       | Server-side experiments, variants, audience rules, allocation, goals, and winner reporting.   |
| [ga4-reports](packages/ga4-reports/README.md)             | `capell-app/ga4-reports`       | GA4 dashboard reporting for Capell.                                                           |
| [insights](packages/insights/README.md)                   | `capell-app/insights`          | First-party insights, visitor journeys, click tracking, and consent management.               |
| [live-chat](packages/live-chat/README.md)                 | `capell-app/live-chat`         | AI-assisted website chat, lead capture, human handoff, and Contacts sync.                     |
| [search](packages/search/README.md)                       | `capell-app/search`            | Public site search, optional logging, and admin search insights.                              |
| [seo-suite](packages/seo-suite/README.md)                 | `capell-app/seo-suite`         | Metadata panels, structured data, social meta, SEO audits, sitemaps, and AI-assisted SEO.     |
| [site-discovery](packages/site-discovery/README.md)       | `capell-app/site-discovery`    | Public discoverability and sitemap outputs.                                                   |
| [site-monitor](packages/site-monitor/README.md)           | `capell-app/site-monitor`      | Public URL, SSL, domain, and incident monitoring for Capell operators.                        |
| [social-feeds](packages/social-feeds/README.md)           | `capell-app/social-feeds`      | Provider-backed social feed records, cached rendering, and Block Library widgets.             |
| [url-manager](packages/url-manager/README.md)             | `capell-app/url-manager`       | Managed redirects, 404 opportunity tracking, canonical URL policy, and hit pruning.           |

### Operations, Agents, And Migration

| Package                                                       | Composer package                 | Purpose                                                                          |
| ------------------------------------------------------------- | -------------------------------- | -------------------------------------------------------------------------------- |
| [agent-bridge](packages/agent-bridge/README.md)               | `capell-app/agent-bridge`        | Agent Bridge servers and capability adapters.                                    |
| [agent-delivery](packages/agent-delivery/README.md)           | `capell-app/agent-delivery`      | Public-safe page manifests and semantic chunks for agents and search assistants. |
| [ai-orchestrator](packages/ai-orchestrator/README.md)         | `capell-app/ai-orchestrator`     | AI providers, prompts, structured requests, and package integration workflows.   |
| [demo-kit](packages/demo-kit/README.md)                       | `capell-app/demo-kit`            | Demo content and media setup for Capell packages.                                |
| [deployments](packages/deployments/README.md)                 | `capell-app/deployments`         | Repository deployment connections and Composer publishing.                       |
| [diagnostics](packages/diagnostics/README.md)                 | `capell-app/diagnostics`         | Developer and operational diagnostics.                                           |
| [document-lifecycle](packages/document-lifecycle/README.md)   | `capell-app/document-lifecycle`  | Document requests, review, delivery, expiry, and retention workflows.            |
| [equestrian-clinics](packages/equestrian-clinics/README.md)   | `capell-app/equestrian-clinics`  | Tour days, clinic discovery, riders, horses, waivers, payments, and facilities.  |
| [exception-reports](packages/exception-reports/README.md)     | `capell-app/exception-reports`   | Sanitized queued email reports for unhandled exceptions and diagnostics health.  |
| [login-audit](packages/login-audit/README.md)                 | `capell-app/login-audit`         | Authentication log and login visibility.                                         |
| [media-ai](packages/media-ai/README.md)                       | `capell-app/media-ai`            | Optional AI-assisted media actions.                                              |
| [migration-assistant](packages/migration-assistant/README.md) | `capell-app/migration-assistant` | Export, import, rollback report, and migration workflow support.                 |
| [privacy-center](packages/privacy-center/README.md)           | `capell-app/privacy-center`      | Consent, policy acceptance, DSAR, retention, export, and anonymization records.  |
| [record-switcher](packages/record-switcher/README.md)         | `capell-app/record-switcher`     | Admin record-switching support for Filament resources.                           |
| [wordpress-importer](packages/wordpress-importer/README.md)   | `capell-app/wordpress-importer`  | WordPress WXR import source for Migration Assistant.                             |

### Themes

Use the [Capell Theme Scale](docs/theme-scale.md) when creating a theme, changing renderer contracts, or deciding whether configuration belongs in theme settings, page blueprints, widget blueprints, Layout Builder assets, or Blade.

| Package                                                                         | Composer package                          | Tier    | Purpose                                                                                                                                                                                                                              |
| ------------------------------------------------------------------------------- | ----------------------------------------- | ------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| [foundation-theme](packages/foundation-theme/README.md)                         | `capell-app/foundation-theme`             | Free    | Default frontend runtime and renderer.                                                                                                                                                                                               |
| [theme-aeo-analytics](packages/theme-aeo-analytics/README.md)                   | `capell-app/theme-aeo-analytics`          | Premium | A premium Capell theme for aeo/geo analytics saas, brand visibility tools.                                                                                                                                                           |
| [theme-agency](packages/theme-agency/README.md)                                 | `capell-app/theme-agency`                 | Premium | A bold, motion-led theme for creative studios and marketing agencies — campaign hero, case-study proof, and a filterable work showcase, with three presets from high-contrast Signal to editorial Atelier.                           |
| [theme-ai-agent](packages/theme-ai-agent/README.md)                             | `capell-app/theme-ai-agent`               | Premium | A premium Capell theme for ai support agents, ops automation products.                                                                                                                                                               |
| [theme-ai-lab](packages/theme-ai-lab/README.md)                                 | `capell-app/theme-ai-lab`                 | Premium | A premium Capell theme for ai research labs, foundation model providers.                                                                                                                                                             |
| [theme-api-platform](packages/theme-api-platform/README.md)                     | `capell-app/theme-api-platform`           | Premium | A premium Capell theme for developer api products, voice/auth/infra platforms.                                                                                                                                                       |
| [theme-automotive-dealer](packages/theme-automotive-dealer/README.md)           | `capell-app/theme-automotive-dealer`      | Premium | A premium Capell theme for car dealerships, used-car retailers.                                                                                                                                                                      |
| [theme-beauty-spa](packages/theme-beauty-spa/README.md)                         | `capell-app/theme-beauty-spa`             | Premium | A premium Capell theme for day spas, beauty clinics.                                                                                                                                                                                 |
| [theme-commerce](packages/theme-commerce/README.md)                             | `capell-app/theme-commerce`               | Premium | A premium, conversion-focused retail theme for Capell — image-led catalog, product discovery, social proof, and buying-guide layouts that turn browsing into baskets.                                                                |
| [theme-conference-event](packages/theme-conference-event/README.md)             | `capell-app/theme-conference-event`       | Premium | A premium Capell theme for conferences and summits, multi-track events.                                                                                                                                                              |
| [theme-construction-trades](packages/theme-construction-trades/README.md)       | `capell-app/theme-construction-trades`    | Premium | A premium Capell theme for builders & contractors, construction firms.                                                                                                                                                               |
| [theme-corporate](packages/theme-corporate/README.md)                           | `capell-app/theme-corporate`              | Premium | A restrained, trust-led website theme for B2B, professional-services, and public-sector organisations — formal hierarchy, board-grade proof blocks, and six palette presets out of the box.                                          |
| [theme-creator-newsletter](packages/theme-creator-newsletter/README.md)         | `capell-app/theme-creator-newsletter`     | Premium | A premium Capell theme for newsletter creators, solo media brands.                                                                                                                                                                   |
| [theme-crypto-defi](packages/theme-crypto-defi/README.md)                       | `capell-app/theme-crypto-defi`            | Premium | A premium Capell theme for defi protocols, on-chain lending markets.                                                                                                                                                                 |
| [theme-design-studio](packages/theme-design-studio/README.md)                   | `capell-app/theme-design-studio`          | Premium | A premium Capell theme for interior design studios, architecture practices.                                                                                                                                                          |
| [theme-devtool-oss](packages/theme-devtool-oss/README.md)                       | `capell-app/theme-devtool-oss`            | Premium | A premium Capell theme for open-source developer tools, self-host + cloud products.                                                                                                                                                  |
| [theme-dog-walkers](packages/theme-dog-walkers/README.md)                       | `capell-app/theme-dog-walkers`            | Premium | A trust-led Capell theme for dog walkers and pet-care teams, built around walk enquiries, service-area confidence, safety proof, and warm neighbourhood presentation.                                                                |
| [theme-editorial-serif](packages/theme-editorial-serif/README.md)               | `capell-app/theme-editorial-serif`        | Premium | A premium Capell theme for publications & journals, writers & essayists.                                                                                                                                                             |
| [theme-education](packages/theme-education/README.md)                           | `capell-app/theme-education`              | Premium | A polished, course-first theme for schools, academies, and training providers — turning programme discovery, faculty trust, open days, and enrolment into one coherent learner journey.                                              |
| [theme-estate-agents](packages/theme-estate-agents/README.md)                   | `capell-app/theme-estate-agents`          | Premium | A premium property theme for estate agencies, lettings teams, valuations, local guides, and viewing-led enquiry journeys.                                                                                                            |
| [theme-financial-advisory](packages/theme-financial-advisory/README.md)         | `capell-app/theme-financial-advisory`     | Premium | A premium Capell theme for financial advisors, accountants.                                                                                                                                                                          |
| [theme-fintech-trust](packages/theme-fintech-trust/README.md)                   | `capell-app/theme-fintech-trust`          | Premium | A premium Capell theme for fintech infrastructure, identity verification / kyb.                                                                                                                                                      |
| [theme-fitness-wellness](packages/theme-fitness-wellness/README.md)             | `capell-app/theme-fitness-wellness`       | Premium | A premium Capell theme for boutique gyms, fitness studios.                                                                                                                                                                           |
| [theme-healthcare](packages/theme-healthcare/README.md)                         | `capell-app/theme-healthcare`             | Premium | A premium, appointment-led theme for private clinics and healthcare groups — service discovery, clinician profiles, care pathways, locations, and a booking-ready enquiry panel, all WCAG-minded and brand-tunable in Theme Studio.  |
| [theme-inertia-bookings](packages/theme-inertia-bookings/README.md)             | `capell-app/theme-inertia-bookings`       | Premium | Premium Inertia booking-business theme for services, clinics, consultants, classes, and appointments.                                                                                                                                |
| [theme-inertia-bookings-react](packages/theme-inertia-bookings-react/README.md) | `capell-app/theme-inertia-bookings-react` | Premium | React components for Theme Inertia Bookings.                                                                                                                                                                                         |
| [theme-inertia-bookings-vue](packages/theme-inertia-bookings-vue/README.md)     | `capell-app/theme-inertia-bookings-vue`   | Premium | Vue components for Theme Inertia Bookings.                                                                                                                                                                                           |
| [theme-knowledge](packages/theme-knowledge/README.md)                           | `capell-app/theme-knowledge`              | Premium | A premium knowledge-base and documentation theme for Capell — sidebar-navigated articles, in-page table of contents, prominent search, and readable long-form layouts out of the box.                                                |
| [theme-law-firm](packages/theme-law-firm/README.md)                             | `capell-app/theme-law-firm`               | Premium | A premium Capell theme for law firms, barristers' chambers.                                                                                                                                                                          |
| [theme-liquid-glass](packages/theme-liquid-glass/README.md)                     | `capell-app/theme-liquid-glass`           | Premium | A free glass interface theme for modern Capell sites with translucent panels, sharp content rhythm, and three token-driven presets.                                                                                                  |
| [theme-local-services](packages/theme-local-services/README.md)                 | `capell-app/theme-local-services`         | Premium | A conversion-first Capell theme for local trades, clinics, and service businesses — built around quote requests, service-area coverage, and click-to-call trust.                                                                     |
| [theme-manufacturing](packages/theme-manufacturing/README.md)                   | `capell-app/theme-manufacturing`          | Premium | A premium Capell theme for industrial manufacturers, smart factories.                                                                                                                                                                |
| [theme-minimal-fashion](packages/theme-minimal-fashion/README.md)               | `capell-app/theme-minimal-fashion`        | Premium | A premium Capell theme for outdoor commerce, field storytelling, repair-led product language, and environmental action.                                                                                                              |
| [theme-newsroom-magazine](packages/theme-newsroom-magazine/README.md)           | `capell-app/theme-newsroom-magazine`      | Premium | A premium Capell theme for news and magazine publications, editorial and opinion sites.                                                                                                                                              |
| [theme-nonprofit](packages/theme-nonprofit/README.md)                           | `capell-app/theme-nonprofit`              | Premium | A premium charity, NGO, and civic theme that moves visitors from your mission to a clear support action — donate, volunteer, or follow — with impact proof, live campaign progress, and transparent annual-report sections built in. |
| [theme-outdoor-mission](packages/theme-outdoor-mission/README.md)               | `capell-app/theme-outdoor-mission`        | Premium | A premium Capell theme for outdoor commerce, field storytelling, repair-led product language, and environmental action.                                                                                                              |
| [theme-packaging-supplier](packages/theme-packaging-supplier/README.md)         | `capell-app/theme-packaging-supplier`     | Premium | A premium Capell theme for sustainable packaging suppliers, food & produce packaging.                                                                                                                                                |
| [theme-personal-dev](packages/theme-personal-dev/README.md)                     | `capell-app/theme-personal-dev`           | Premium | A premium Capell theme for developer personal sites, writers & bloggers.                                                                                                                                                             |
| [theme-podcast-show](packages/theme-podcast-show/README.md)                     | `capell-app/theme-podcast-show`           | Premium | A premium Capell theme for podcasts and audio shows, interview series.                                                                                                                                                               |
| [theme-portfolio](packages/theme-portfolio/README.md)                           | `capell-app/theme-portfolio`              | Premium | A premium portfolio theme for creators and consultants — turn selected work into outcome-driven case studies, sell your services and media kit, and grow your audience from one polished site.                                       |
| [theme-product-studio](packages/theme-product-studio/README.md)                 | `capell-app/theme-product-studio`         | Premium | A premium Capell theme for web & product studios, engineering agencies.                                                                                                                                                              |
| [theme-property-developer](packages/theme-property-developer/README.md)         | `capell-app/theme-property-developer`     | Premium | A premium Capell theme for new-build developers, housebuilders.                                                                                                                                                                      |
| [theme-quant-trading](packages/theme-quant-trading/README.md)                   | `capell-app/theme-quant-trading`          | Premium | A premium Capell theme for quant trading firms, algorithmic strategy products.                                                                                                                                                       |
| [theme-recruitment-jobs](packages/theme-recruitment-jobs/README.md)             | `capell-app/theme-recruitment-jobs`       | Premium | A premium Capell theme for recruitment agencies, job boards.                                                                                                                                                                         |
| [theme-restaurant](packages/theme-restaurant/README.md)                         | `capell-app/theme-restaurant`             | Premium | A premium hospitality theme for menu-led restaurants, bars, private dining venues, and event-led dining businesses.                                                                                                                  |
| [theme-robotics-hardware](packages/theme-robotics-hardware/README.md)           | `capell-app/theme-robotics-hardware`      | Premium | A premium Capell theme for robotics products, hardware and deep-tech devices.                                                                                                                                                        |
| [theme-saas](packages/theme-saas/README.md)                                     | `capell-app/theme-saas`                   | Premium | A conversion-focused premium theme for software and subscription products — hero, feature proof, pricing comparison, calculator, docs, and demo-request sections that turn a Capell site into a product-led landing experience.      |
| [theme-travel-tourism](packages/theme-travel-tourism/README.md)                 | `capell-app/theme-travel-tourism`         | Premium | A premium Capell theme for tour operators, travel agencies.                                                                                                                                                                          |

## Install Pattern

Install packages from the host Capell application:

```bash
composer require capell-app/<package>
```

Then run the package install command listed in that package README when it owns migrations, settings, generated pages, demo data, or external setup.

### Migration Publishing

Package migrations must be published through Capell's migration publisher, not Laravel `vendor:publish` migration tags.

Use `Capell\Core\Actions\Install\PublishPackageMigrationsAction` from package install actions. For command-line publishing, use `php artisan capell:publish-migrations --items=capell-app/<package>`.

Do not register or run tags such as `capell-foo-migrations`. Laravel publish tags can stamp files with the current date when copied into a host app, which breaks Capell's canonical package migration ordering and can leave duplicate app migrations behind during fresh installs.

The root architecture tests enforce this rule:

```bash
vendor/bin/pest tests/Packages/Arch/PackageMigrationPublishContractTest.php --compact
vendor/bin/pest tests/Packages/Arch/PackageMigrationLoadingTest.php --compact
```

## Package Screenshots

Use the full capture path when the screenshot demo app needs to be prepared or refreshed:

```bash
npm run screenshots:capture -- --only layout-builder --runner /path/to/capell-screenshot-runner --app /path/to/prepared-demo-app
```

For day-to-day recaptures against an already installed, migrated, seeded, compiled, and running demo app, use reuse mode:

```bash
npm run screenshots:capture:reuse -- --only layout-builder --runner /path/to/capell-screenshot-runner --app /path/to/prepared-demo-app
```

`--reuse-app` skips package setup commands, frontend CSS injection, and the runner preflight so captures do not spend time rebuilding or reseeding the app.

### Install Ordering Notes

- Install `capell-app/layout-builder` before `capell-app/blog`.
- Install `capell-app/layout-builder`, then `capell-app/foundation-theme`, before any premium theme package.
- Install `capell-app/migration-assistant` before `capell-app/wordpress-importer`.

## Working In This Repository

Use package-level checks while editing:

```bash
vendor/bin/pest packages/<package>/tests --configuration=phpunit.xml
```

Use Pest groups when the failing area is known:

```bash
COMPOSER=composer.local.json PEST_GROUP=blog composer run test:group
COMPOSER=composer.local.json PEST_GROUP=feature composer run test:group
COMPOSER=composer.local.json PEST_GROUP=blog composer run test:debug:group
```

Useful groups include package names such as `blog`, suite names such as `unit`, `feature`, `integration`, and `arch`, plus `package` for all package tests and `workspace` for shared root tests. `test:debug:group` runs without parallelism and stops on the first defect so large failure sets are easier to inspect.

Use broader checks before integration:

```bash
COMPOSER=composer.local.json composer test
COMPOSER=composer.local.json composer preflight
COMPOSER=composer.local.json composer preflight:all
```

Run coverage and mutation testing as explicit deep checks:

```bash
COMPOSER=composer.local.json composer coverage
COMPOSER=composer.local.json composer test:mutate
```

Do not run `php artisan` in this repository. Testbench provides the Laravel context for package tests.

Named test doubles must be real PSR-4 fixture classes, not classes declared inside Pest files. See [Writing Tests](docs/writing-tests.md).

## Docker Harness

Use Docker when you need a clean shell for agent, CI, or local verification. This is a CLI package-development harness, not the `capell-app.test` application runtime.

Start the services:

```bash
./capell up
```

Run common checks inside the PHP 8.4 container:

```bash
./capell composer install
./capell test
./capell analyze
./capell npm install
./capell npm run eslint
```

Use `./capell run <command>` for one-off app container commands without starting dependent services, or `./capell compose <args>` when you need direct Docker Compose access.

Store private Composer auth once in the mounted Composer config:

```bash
./capell composer config --global github-oauth.github.com <github-token>
```

Composer stores that token in your host `~/.config/composer/auth.json`, mounted into the container at `/home/capell/.config/composer/auth.json`.

The harness provides MariaDB, Redis, Mailpit, Composer, Node 22, and the PHP extensions used by the package test suite. It mounts the sibling Capell package directory at `/home/capell/packages/capell` so local path repositories continue to work when `capell-4` and `capell-packages-4` live beside each other.

## Contributing Through Split Repositories

Invited collaborators may open PRs against individual package repositories such as `capell-app/address` or `capell-app/blog`. Those repositories contain a generated workflow that copies the split PR contents into `packages/<package>` here and opens or updates a matching PR against `capell-app/capell-packages:4.x`.

Review the forwarded monorepo PR for tests, code review, and merge. Close the split PR after the monorepo PR is merged. The split repository will be updated again by the tag-based monorepo split workflow.

The forwarding workflow requires an Actions secret named `SPLIT_PR_FORWARD_TOKEN` or `ACCESS_TOKEN` with permission to read the split repo, push branches to this repository, create PRs, and comment on PRs.

## Documentation

- Per-package READMEs live at `packages/<package>/README.md`.
- Deeper package docs live under `packages/<package>/docs/` when the package needs API, database, workflow, or design notes.
- Screenshot generation is manifest-driven where `packages/<package>/docs/screenshots.json` exists.
- External docs: [docs.capell.app](https://docs.capell.app).

## License

Proprietary unless an individual package states otherwise.
