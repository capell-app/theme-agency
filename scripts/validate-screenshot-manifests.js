const fs = require('fs')
const path = require('path')

const root = process.cwd()
const manifestPath = path.join(root, 'docs/package-screenshot-manifest.json')
const allPackageDirs = fs
    .readdirSync(path.join(root, 'packages'), { withFileTypes: true })
    .filter((entry) => entry.isDirectory())
    .map((entry) => entry.name)
const onlyPackages = collectOnlyPackages(process.argv.slice(2))
const packageDirs =
    onlyPackages.size > 0
        ? allPackageDirs.filter((packageName) => onlyPackages.has(packageName))
        : allPackageDirs

const failures = []
const warnings = []

const manifest = JSON.parse(fs.readFileSync(manifestPath, 'utf8'))
const themeEntryFields = [
    'id',
    'title',
    'package',
    'surface',
    'targetType',
    'target',
    'notes',
    'useCase',
    'screenshotPath',
]
const weakThemeEntryPatterns = [
    /page page/i,
    /buyer reviews how/i,
    /Theme .+ preview/i,
    /extension-card/i,
    /generic/i,
]
const manifestPackages = new Set([
    ...(manifest.packages ?? []).map((entry) => entry.package),
    ...(manifest.entries ?? []).map((entry) => entry.package),
])
const manifestPackageEntries = new Map(
    (manifest.packages ?? []).map((entry) => [entry.package, entry]),
)

function readFlagValue(argv, index, flag) {
    const current = argv[index]

    if (current === flag) {
        const nextValue = argv[index + 1] ?? ''

        return nextValue.startsWith('-') ? '' : nextValue
    }

    if (current.startsWith(`${flag}=`)) {
        return current.slice(flag.length + 1)
    }

    return null
}

function collectOnlyPackages(argv) {
    const packages = new Set()

    for (let index = 0; index < argv.length; index += 1) {
        const onlyValue = readFlagValue(argv, index, '--only')

        if (onlyValue !== null) {
            if (onlyValue !== '') {
                packages.add(onlyValue)
            }

            if (argv[index] === '--only') {
                index += 1
            }

            continue
        }

        const onlyFile = readFlagValue(argv, index, '--only-file')

        if (onlyFile === null) {
            continue
        }

        if (onlyFile !== '') {
            for (const packageName of fs
                .readFileSync(onlyFile, 'utf8')
                .split(/\r?\n/)
                .map((line) => line.trim())
                .filter(Boolean)) {
                packages.add(packageName)
            }
        }

        if (argv[index] === '--only-file') {
            index += 1
        }
    }

    return packages
}

function isNonEmptyString(value) {
    return typeof value === 'string' && value.trim() !== ''
}

function relativePath(filePath) {
    return path.relative(root, filePath)
}

function validateThemeManifest(packageName, screenshotsPath, packageManifest) {
    const label = relativePath(screenshotsPath)
    const entries = packageManifest.entries

    if (!isNonEmptyString(packageManifest.composerName)) {
        failures.push(`${label}: composerName is required for theme manifests`)
    }

    if (
        isNonEmptyString(packageManifest.composerName) &&
        !packageManifest.composerName.includes('/')
    ) {
        failures.push(
            `${label}: composerName must be a full Composer package name`,
        )
    }

    if (!Array.isArray(entries)) {
        failures.push(`${label}: theme manifests must declare entries`)

        return
    }

    if (entries.length < 5) {
        failures.push(
            `${label}: theme manifests must declare at least 5 screenshot entries`,
        )
    }

    const frontendEntries = entries.filter(
        (entry) => entry.surface === 'frontend',
    )

    if (frontendEntries.length < 5) {
        failures.push(
            `${label}: theme manifests must declare at least 5 frontend screenshot entries`,
        )
    }

    for (const [index, entry] of entries.entries()) {
        const entryLabel = `${label}: entries[${index}]`

        for (const field of themeEntryFields) {
            if (!isNonEmptyString(entry[field])) {
                failures.push(
                    `${entryLabel}.${field} must be a non-empty string`,
                )
            }
        }

        if (entry.package !== packageName) {
            failures.push(
                `${entryLabel}.package must match directory "${packageName}"`,
            )
        }

        if (
            isNonEmptyString(entry.screenshotPath) &&
            !entry.screenshotPath.startsWith(
                `packages/${packageName}/docs/screenshots/`,
            )
        ) {
            failures.push(
                `${entryLabel}.screenshotPath must start with packages/${packageName}/docs/screenshots/`,
            )
        }

        const searchableText = [
            entry.id,
            entry.title,
            entry.notes,
            entry.useCase,
            entry.screenshotPath,
        ]
            .filter(isNonEmptyString)
            .join(' ')

        for (const pattern of weakThemeEntryPatterns) {
            if (pattern.test(searchableText)) {
                failures.push(
                    `${entryLabel} contains stale or weak screenshot copy matching ${pattern}`,
                )
            }
        }
    }
}

function validateMarketplaceScreenshots(packageName) {
    const capellManifestPath = path.join(
        root,
        'packages',
        packageName,
        'capell.json',
    )
    const capellManifestLabel = relativePath(capellManifestPath)

    if (!fs.existsSync(capellManifestPath)) {
        return
    }

    let capellManifest

    try {
        capellManifest = JSON.parse(fs.readFileSync(capellManifestPath, 'utf8'))
    } catch (error) {
        failures.push(`${capellManifestLabel}: ${error.message}`)

        return
    }

    const screenshots = capellManifest.marketplace?.screenshots

    if (screenshots === undefined) {
        return
    }

    if (!Array.isArray(screenshots)) {
        failures.push(
            `${capellManifestLabel}: marketplace.screenshots must be an array`,
        )

        return
    }

    for (const [index, screenshot] of screenshots.entries()) {
        const screenshotLabel = `${capellManifestLabel}: marketplace.screenshots[${index}]`

        if (typeof screenshot !== 'object' || screenshot === null) {
            failures.push(`${screenshotLabel} must be an object`)

            continue
        }

        if (!isNonEmptyString(screenshot.path)) {
            failures.push(`${screenshotLabel}.path must be a non-empty string`)

            continue
        }

        if (
            path.isAbsolute(screenshot.path) ||
            screenshot.path.includes('\\') ||
            screenshot.path.split('/').includes('..')
        ) {
            failures.push(
                `${screenshotLabel}.path must be a safe package-relative path`,
            )

            continue
        }

        if (
            !screenshot.path.startsWith('docs/assets/marketplace/') &&
            !screenshot.path.startsWith('docs/screenshots/')
        ) {
            failures.push(
                `${screenshotLabel}.path must start with docs/assets/marketplace/ or docs/screenshots/`,
            )
        }

        const absoluteScreenshotPath = path.join(
            root,
            'packages',
            packageName,
            screenshot.path,
        )

        if (!fs.existsSync(absoluteScreenshotPath)) {
            failures.push(
                `${screenshotLabel}.path references missing file ${relativePath(absoluteScreenshotPath)}`,
            )
        }
    }
}

function validatePackageScreenshotOutputs(
    packageName,
    screenshotsPath,
    packageManifest,
) {
    if (!Array.isArray(packageManifest.entries)) {
        return
    }

    for (const [index, entry] of packageManifest.entries.entries()) {
        if (typeof entry !== 'object' || entry === null) {
            continue
        }

        for (const field of ['screenshotPath', 'darkScreenshotPath']) {
            if (!isNonEmptyString(entry[field])) {
                continue
            }

            const outputPath = entry[field]

            if (
                path.isAbsolute(outputPath) ||
                outputPath.includes('\\') ||
                outputPath.split('/').includes('..')
            ) {
                failures.push(
                    `${relativePath(screenshotsPath)}: entries[${index}].${field} must be a safe repository-relative path`,
                )

                continue
            }

            if (
                !outputPath.startsWith(
                    `packages/${packageName}/docs/screenshots/`,
                )
            ) {
                failures.push(
                    `${relativePath(screenshotsPath)}: entries[${index}].${field} must start with packages/${packageName}/docs/screenshots/`,
                )

                continue
            }

            const absoluteOutputPath = path.join(root, outputPath)

            if (fs.existsSync(absoluteOutputPath)) {
                continue
            }

            const message = `${relativePath(screenshotsPath)}: entries[${index}].${field} references missing output ${outputPath}`

            if (entry.required === false) {
                warnings.push(message)

                continue
            }

            failures.push(message)
        }
    }
}

for (const packageName of packageDirs) {
    validateMarketplaceScreenshots(packageName)
}

for (const packageName of packageDirs) {
    const screenshotsPath = path.join(
        root,
        'packages',
        packageName,
        'docs/screenshots.json',
    )

    if (!fs.existsSync(screenshotsPath)) {
        continue
    }

    try {
        const packageManifest = JSON.parse(
            fs.readFileSync(screenshotsPath, 'utf8'),
        )

        if (
            packageManifest.package !== undefined &&
            packageManifest.package !== packageName
        ) {
            failures.push(
                `${screenshotsPath}: package key "${packageManifest.package}" does not match directory "${packageName}"`,
            )
        }

        if (!manifestPackages.has(packageName)) {
            failures.push(
                `${screenshotsPath}: package is missing from docs/package-screenshot-manifest.json`,
            )
        }

        const capellManifestPath = path.join(
            root,
            'packages',
            packageName,
            'capell.json',
        )
        const capellManifest = fs.existsSync(capellManifestPath)
            ? JSON.parse(fs.readFileSync(capellManifestPath, 'utf8'))
            : {}
        const commands = capellManifest.commands ?? {}
        const manifestPackage = manifestPackageEntries.get(packageName)

        if (manifestPackage !== undefined) {
            for (const [manifestField, commandField] of [
                ['installCommand', 'install'],
                ['setupCommand', 'setup'],
                ['demoCommand', 'demo'],
                ['doctorCommand', 'doctor'],
            ]) {
                if (
                    (manifestPackage[manifestField] ?? null) !==
                    (commands[commandField] ?? null)
                ) {
                    failures.push(
                        `${screenshotsPath}: ${manifestField} must match commands.${commandField} from capell.json`,
                    )
                }
            }

            const demoParams = Array.isArray(commands.demoParams)
                ? commands.demoParams
                : []

            if (
                JSON.stringify(manifestPackage.demoParams ?? []) !==
                JSON.stringify(demoParams)
            ) {
                failures.push(
                    `${screenshotsPath}: demoParams must match commands.demoParams from capell.json`,
                )
            }
        }

        if (
            packageManifest.composerRequires !== undefined &&
            !Array.isArray(packageManifest.composerRequires)
        ) {
            failures.push(
                `${screenshotsPath}: composerRequires must be an array when present`,
            )
        }

        for (const requirement of packageManifest.composerRequires ?? []) {
            if (typeof requirement !== 'string' || !requirement.includes('/')) {
                failures.push(
                    `${screenshotsPath}: composerRequires entries must be full Composer package names`,
                )
            }
        }

        if (
            packageManifest.composerRequires !== undefined &&
            !packageManifest.composerRequires.includes(
                packageManifest.composerName,
            )
        ) {
            failures.push(
                `${screenshotsPath}: composerRequires must include composerName`,
            )
        }

        if (
            packageManifest.browserTests !== undefined &&
            !Array.isArray(packageManifest.browserTests)
        ) {
            failures.push(
                `${screenshotsPath}: browserTests must be an array when present`,
            )
        }

        for (const browserTest of packageManifest.browserTests ?? []) {
            if (typeof browserTest.id !== 'string' || browserTest.id === '') {
                failures.push(
                    `${screenshotsPath}: browserTests entries must have an id`,
                )
            }

            if (
                !Array.isArray(browserTest.assertions) ||
                browserTest.assertions.length === 0
            ) {
                failures.push(
                    `${screenshotsPath}: browserTests entries must declare assertions`,
                )
            }
        }

        validatePackageScreenshotOutputs(
            packageName,
            screenshotsPath,
            packageManifest,
        )

        if (packageName.startsWith('theme-')) {
            validateThemeManifest(packageName, screenshotsPath, packageManifest)
        }
    } catch (error) {
        failures.push(`${screenshotsPath}: ${error.message}`)
    }
}

for (const packageName of manifestPackages) {
    if (onlyPackages.size > 0 && !onlyPackages.has(packageName)) {
        continue
    }

    const screenshotsPath = path.join(
        root,
        'packages',
        packageName,
        'docs/screenshots.json',
    )

    if (!fs.existsSync(screenshotsPath)) {
        failures.push(
            `docs/package-screenshot-manifest.json references "${packageName}" but ${screenshotsPath} does not exist`,
        )
    }
}

for (const warning of warnings) {
    console.warn(`Warning: ${warning}`)
}

if (failures.length > 0) {
    throw new Error(
        `Screenshot manifest validation failed:\n${failures.map((failure) => `- ${failure}`).join('\n')}`,
    )
}

console.log('Screenshot manifests are in sync.')
