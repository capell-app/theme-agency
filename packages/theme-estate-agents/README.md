# Theme Estate Agents

Theme Estate Agents is an **Available**, **No schema impact** Capell theme in the **Capell Themes** product group. It ships as `capell-app/theme-estate-agents` and extends these surfaces: frontend, console.

Theme Estate Agents gives property teams a premium agency website built around search intent, featured listings, vendor valuations, local-area confidence, agent credibility, and viewing requests. It keeps property copy and enquiry content portable while the package owns the visual rhythm: search bands, listing ledgers, valuation CTAs, area guides, agent proof, and viewing forms.

## Package

- Composer package: `capell-app/theme-estate-agents`
- Product group: `Capell Themes`
- Manifest extends: `default`
- Runtime extends: `default`
- Theme key: `estate-agents`
- Tier: `premium`
- Bundle: `themes`
- Schema impact: none
- Cache tags: `theme-estate-agents`
- Commands: `capell:theme-estate-agents-demo`

## Best Fit

- Estate agencies and lettings teams with buyer and vendor journeys.
- Property consultants who need valuation, local guide, and viewing paths.
- Branch networks that want a premium property brochure without theme-owned records.

## Optional Integrations

- Search for public listing discovery.
- Address for location context and area pages.
- Form Builder for valuations, appraisals, and viewing requests.
- Blog and SEO Suite for market reports, area guides, and findability.

## Development

Run package tests from the repository root:

```bash
vendor/bin/pest packages/theme-estate-agents/tests
```

Use `COMPOSER=composer.local.json composer preflight` before committing package wiring changes.
