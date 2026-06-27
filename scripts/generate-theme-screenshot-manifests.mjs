#!/usr/bin/env node
// Generate the full viewport matrix for theme screenshot manifests.
//
// Each theme's docs/screenshots.json is *authored* with one canonical entry per
// seeded surface (desktop, light). This generator expands every surface across
// the three runner viewports (desktop / tablet / mobile). The light/dark axis is
// NOT materialised here — the runner derives the `-dark` image at capture time
// from each entry's `colorSchemes`, so storing dark entries would double-count.
//
// The canonical desktop entry keeps its original id + screenshotPath so the
// marketplace gallery keys stay stable; tablet/mobile are added as `-tablet` /
// `-mobile` suffixed variants. The script is idempotent: re-running first strips
// any previously generated viewport variants back to the authored surfaces.
//
// Usage:
//   node scripts/generate-theme-screenshot-manifests.mjs              # all themes
//   node scripts/generate-theme-screenshot-manifests.mjs --package=theme-api-platform
//   node scripts/generate-theme-screenshot-manifests.mjs --dry-run

import { readFileSync, writeFileSync, readdirSync, existsSync } from 'node:fs'
import { join, dirname } from 'node:path'
import { fileURLToPath } from 'node:url'

const root = join(dirname(fileURLToPath(import.meta.url)), '..')
const packagesDir = join(root, 'packages')

// Non-desktop viewports added on top of the authored (desktop) surface.
const EXTRA_VIEWPORTS = ['tablet', 'mobile']
const VIEWPORT_LABEL = {
    desktop: 'Desktop',
    tablet: 'Tablet',
    mobile: 'Mobile',
}
const SCHEMES = ['light', 'dark']

const args = process.argv.slice(2)
const dryRun = args.includes('--dry-run')
const onlyArg = args.find((a) => a.startsWith('--package='))
const only = onlyArg ? onlyArg.split('=')[1] : null

function withViewportSuffix(value, viewport) {
    // insert `-<viewport>` before the file extension; else append.
    return /(\.[a-z0-9]+)$/i.test(value)
        ? value.replace(/(\.[a-z0-9]+)$/i, `-${viewport}$1`)
        : `${value}-${viewport}`
}

// Reduce a manifest's entries back to the authored desktop surfaces, dropping any
// previously generated viewport variants so the expansion is deterministic.
function canonicalSurfaces(entries) {
    return entries
        .filter((entry) => {
            const id = String(entry.id ?? '')
            const viewport = entry.viewport ?? 'desktop'
            const isVariant = EXTRA_VIEWPORTS.some((vp) =>
                id.endsWith(`-${vp}`),
            )
            return viewport === 'desktop' && !isVariant
        })
        .map((entry) => ({ ...entry }))
}

function expandSurface(base) {
    const desktop = {
        ...base,
        viewport: 'desktop',
        fullPage: true,
        colorSchemes: [...SCHEMES],
    }

    const variants = EXTRA_VIEWPORTS.map((viewport) => ({
        ...base,
        id: `${base.id}-${viewport}`,
        title: `${base.title} — ${VIEWPORT_LABEL[viewport]}`,
        url: base.url ? withViewportSuffix(base.url, viewport) : base.url,
        screenshotPath: withViewportSuffix(base.screenshotPath, viewport),
        notes: `${base.notes} (${viewport} viewport)`,
        viewport,
        fullPage: true,
        colorSchemes: [...SCHEMES],
        // Supplementary viewport shots: marked non-required so the pre-capture
        // manifest validator treats their not-yet-captured PNGs as warnings
        // (the canonical desktop entry stays required and gates the gallery key).
        required: false,
    }))

    return [desktop, ...variants]
}

const themeDirs = readdirSync(packagesDir, { withFileTypes: true })
    .filter((d) => d.isDirectory() && d.name.startsWith('theme-'))
    .map((d) => d.name)
    .sort()

let written = 0

for (const dir of themeDirs) {
    if (only && dir !== only) continue

    const manifestPath = join(packagesDir, dir, 'docs/screenshots.json')
    if (!existsSync(manifestPath)) continue

    const manifest = JSON.parse(readFileSync(manifestPath, 'utf8'))
    const entriesKey = Array.isArray(manifest.entries)
        ? 'entries'
        : 'screenshots'
    const entries = manifest[entriesKey]
    if (!Array.isArray(entries)) continue

    const surfaces = canonicalSurfaces(entries)
    const expanded = surfaces.flatMap(expandSurface)
    const next = { ...manifest, [entriesKey]: expanded }
    const serialized = `${JSON.stringify(next, null, 2)}\n`

    console.log(
        `${dir}: ${surfaces.length} surfaces → ${expanded.length} entries (${expanded.length * SCHEMES.length} PNGs)`,
    )

    if (!dryRun) {
        writeFileSync(manifestPath, serialized)
        written += 1
    }
}

console.log(
    dryRun
        ? 'dry-run: no files written'
        : `done: ${written} manifest(s) written`,
)
