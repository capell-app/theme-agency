#!/usr/bin/env node
// Normalize a theme's screenshots.json to the canonical foundation demo surfaces.
//
// Some themes delegate their demo entirely to the shared
// `ThemeDemoPageInstaller` (their InstallXThemeDemoAction just calls
// `ThemeDemoPageInstaller::run(...)`), which seeds 7 base surfaces at
// deterministic routes: /theme-<key>, /theme-<key>-directory, -detail,
// -contact, -empty, -404, -cta. A few of these themes still ship STALE
// manifests pointing at bespoke routes (e.g. /dog-walkers-homepage-layout) or
// admin targets that the demo never creates, so every such entry 404s during
// capture. This rewrites those manifests to the real seeded foundation routes
// (mirroring the already-correct api-platform/agency manifests), preserving the
// manifest meta. Run the viewport-matrix generator afterwards to expand them.
//
// Usage: node scripts/normalize-foundation-theme-manifests.mjs theme-dog-walkers theme-portfolio ...

import { readFileSync, writeFileSync, existsSync } from 'node:fs'
import { join, dirname } from 'node:path'
import { fileURLToPath } from 'node:url'

const root = join(dirname(fileURLToPath(import.meta.url)), '..')

// Canonical foundation base surfaces: [surfaceId, routeSuffix, titleSuffix].
// routeSuffix '' = the homepage route /theme-<key>; not-found maps to -404.
const SURFACES = [
    ['homepage', '', 'Homepage'],
    ['directory', '-directory', 'Directory'],
    ['detail', '-detail', 'Detail Article'],
    ['contact', '-contact', 'Contact'],
    ['empty', '-empty', 'Empty State'],
    ['not-found', '-404', 'Page Not Found'],
    ['cta', '-cta', 'Call To Action'],
]

function displayName(key) {
    const overrides = { saas: 'SaaS', 'aeo-analytics': 'AEO Analytics' }
    return overrides[key]
        ?? key.split('-').map((w) => w.charAt(0).toUpperCase() + w.slice(1)).join(' ')
}

function baseEntries(pkg, key, name) {
    return SURFACES.map(([surface, routeSuffix, titleSuffix]) => ({
        id: `${key}-${surface}`,
        title: `${name} ${titleSuffix}`,
        surface: 'frontend',
        targetType: 'frontend-url',
        target: `/theme-${key}${routeSuffix}`,
        url: `/screenshot-fixtures/${pkg}/${key}-${surface}`,
        screenshotPath: `packages/${pkg}/docs/screenshots/${key}-${surface}.png`,
        // Non-required so the pre-capture validator treats the not-yet-captured
        // PNGs as warnings (these themes are being re-pointed from stale bespoke
        // routes, so no canonical foundation PNG exists yet). Capture still
        // produces them; restore `required` on the homepage afterwards if needed.
        required: false,
        user: false,
        notes: `${name} ${titleSuffix} seeded demo surface`,
        useCase: `A buyer reviews the ${name} ${titleSuffix.toLowerCase()} surface before selecting ${pkg}.`,
        package: pkg,
        scenario: 'frontend-page',
        waitFor: '.site-theme-shell',
        colorSchemes: ['light'],
    }))
}

const targets = process.argv.slice(2)
if (targets.length === 0) {
    console.error('Pass one or more theme package slugs (e.g. theme-dog-walkers).')
    process.exitCode = 1
}

for (const pkg of targets) {
    const key = pkg.replace(/^theme-/, '')
    const manifestPath = join(root, 'packages', pkg, 'docs/screenshots.json')
    if (!existsSync(manifestPath)) {
        console.log(`${pkg}: no manifest, skipped`)
        continue
    }

    const manifest = JSON.parse(readFileSync(manifestPath, 'utf8'))
    const entriesKey = Array.isArray(manifest.screenshots) ? 'screenshots' : 'entries'
    const name = displayName(key)

    const next = { ...manifest, [entriesKey]: baseEntries(pkg, key, name) }
    writeFileSync(manifestPath, `${JSON.stringify(next, null, 2)}\n`)
    console.log(`${pkg}: rewrote to ${SURFACES.length} canonical foundation surfaces`)
}
