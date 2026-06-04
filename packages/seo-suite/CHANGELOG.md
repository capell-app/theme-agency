# Changelog

All notable changes to `capell-app/seo-suite` will be documented in this file.

## Unreleased

- Wired page SEO reports to account for every `SeoCheckKeyEnum` check using existing canonical, schema, social, internal-link, broken-link, redirect, translation, sitemap, AI Discovery, and Search Console report data.
- Added anonymous AI Discovery output safety coverage for `llms.txt`, page Markdown, and `robots.txt`.
- Scoped Prism AI circuit breakers per provider and guarded missing token-usage telemetry.
- Resolved default PageSpeed digest recipients through the user role relation when available.
- Corrected the manifest settings, permissions, supported integrations, PageSpeed capabilities, and cache invalidation metadata.
- Prepared package metadata and documentation for ongoing Capell 4.x package work.

## 2026-06-03

- Updated marketplace and Composer listing copy for SEO Suite positioning.
- Promoted existing package screenshots into the marketplace manifest.
- Replaced the placeholder extension health check with diagnostics for storage tables, AI Discovery routes, SEO services, schema templates, and AI crawler policy configuration.
