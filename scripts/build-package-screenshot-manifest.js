const fs = require('fs')
const path = require('path')

const root = process.cwd()
const packagesPath = path.join(root, 'packages')
const outputPath = path.join(root, 'docs/package-screenshot-manifest.json')

const requirements = [
    'Install the package under test.',
    'Composer require any package-level composerRequires before seeding demo data.',
    'Run package install, setup, and demo commands from capell.json in that order when present.',
    'Authenticate as an admin user with the required role or permission.',
    'Resolve admin-surface targets through Filament resource/page URLs when possible.',
    'Resolve frontend-url targets through seeded demo routes or package route names.',
    'Execute package-level browserTests when declared by the source screenshots manifest.',
]

const packageNames = fs
    .readdirSync(packagesPath, { withFileTypes: true })
    .filter((entry) => entry.isDirectory())
    .map((entry) => entry.name)
    .sort()

const entries = []
const browserTests = []
const packages = []

for (const packageName of packageNames) {
    const screenshotsPath = path.join(
        packagesPath,
        packageName,
        'docs/screenshots.json',
    )

    if (!fs.existsSync(screenshotsPath)) {
        continue
    }

    const packageManifest = JSON.parse(fs.readFileSync(screenshotsPath, 'utf8'))
    const capellManifestPath = path.join(
        packagesPath,
        packageName,
        'capell.json',
    )
    const capellManifest = fs.existsSync(capellManifestPath)
        ? JSON.parse(fs.readFileSync(capellManifestPath, 'utf8'))
        : {}
    const commands = capellManifest.commands ?? {}
    const composerName = packageManifest.composerName ?? null
    const packageEntries = packageManifest.entries ?? []
    const packageBrowserTests = packageManifest.browserTests ?? []

    packages.push({
        package: packageManifest.package ?? packageName,
        composerName,
        installCommand: commands.install ?? null,
        setupCommand: commands.setup ?? null,
        demoCommand: commands.demo ?? null,
        demoParams: Array.isArray(commands.demoParams)
            ? commands.demoParams
            : [],
        doctorCommand: commands.doctor ?? null,
        entryCount: packageEntries.length,
        browserTestCount: packageBrowserTests.length,
    })

    for (const entry of packageEntries) {
        entries.push({
            ...entry,
            package: entry.package ?? packageManifest.package ?? packageName,
            composerName: entry.composerName ?? composerName,
        })
    }

    for (const browserTest of packageBrowserTests) {
        browserTests.push({
            ...browserTest,
            package:
                browserTest.package ?? packageManifest.package ?? packageName,
            composerName: browserTest.composerName ?? composerName,
        })
    }
}

const manifest = {
    generatedFor: 'capell-docs-deployment',
    source: 'packages/*/docs/screenshots.json',
    outputRoot: 'public/docs/screenshots/packages',
    requirements,
    packages,
    entries,
}

if (browserTests.length > 0) {
    manifest.browserTests = browserTests
}

fs.writeFileSync(outputPath, `${JSON.stringify(manifest, null, 4)}\n`)

console.log(
    `Wrote ${entries.length} screenshot entries from ${packageNames.length} packages to ${path.relative(root, outputPath)}.`,
)
