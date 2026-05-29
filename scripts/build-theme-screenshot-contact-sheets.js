const { spawnSync } = require('child_process')
const fs = require('fs')
const os = require('os')
const path = require('path')

const THEME_ORDER = [
    'agency',
    'commerce',
    'corporate',
    'default',
    'education',
    'healthcare',
    'knowledge',
    'local-services',
    'nonprofit',
    'portfolio',
    'saas',
]

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

function readOption(argv, flag, fallback = '') {
    for (let index = 0; index < argv.length; index += 1) {
        const value = readFlagValue(argv, index, flag)

        if (value !== null) {
            return value || fallback
        }
    }

    return fallback
}

function collectOptions(argv, flag) {
    const values = []

    for (let index = 0; index < argv.length; index += 1) {
        const value = readFlagValue(argv, index, flag)

        if (value === null) {
            continue
        }

        if (value !== '') {
            values.push(value)
        }

        if (argv[index] === flag) {
            index += 1
        }
    }

    return values
}

function fail(message) {
    console.error(message)
    process.exitCode = 1
}

function ensureSafeOutputDirectory(repoPath, outputDirectory) {
    const fixturesPath = path.resolve(
        repoPath,
        'tests/Packages/Fixtures/theme-demo-layout-screenshots',
    )
    const resolvedOutputDirectory = path.resolve(outputDirectory)

    if (
        resolvedOutputDirectory === fixturesPath ||
        resolvedOutputDirectory.startsWith(`${fixturesPath}${path.sep}`)
    ) {
        throw new Error(
            `Refusing to write contact sheets inside screenshot fixtures: ${resolvedOutputDirectory}`,
        )
    }

    fs.mkdirSync(resolvedOutputDirectory, { recursive: true })

    return resolvedOutputDirectory
}

function screenshotFixtureDirectory(repoPath) {
    return path.resolve(
        repoPath,
        'tests/Packages/Fixtures/theme-demo-layout-screenshots',
    )
}

function collectScreenshots(repoPath, requestedSurfaces) {
    const fixturesPath = screenshotFixtureDirectory(repoPath)
    const requestedSurfaceSet = new Set(requestedSurfaces)
    const screenshotsBySurface = new Map()

    for (const themeKey of THEME_ORDER) {
        const themeDirectory = path.join(fixturesPath, themeKey)

        if (!fs.existsSync(themeDirectory)) {
            continue
        }

        const filenames = fs
            .readdirSync(themeDirectory)
            .filter((filename) => filename.endsWith('.png'))
            .sort()

        for (const filename of filenames) {
            const prefix = `${themeKey}-`
            const marker = '-type-'

            if (!filename.startsWith(prefix) || !filename.includes(marker)) {
                continue
            }

            const surface = filename.slice(
                prefix.length,
                filename.indexOf(marker),
            )

            if (
                requestedSurfaceSet.size > 0 &&
                !requestedSurfaceSet.has(surface)
            ) {
                continue
            }

            if (!screenshotsBySurface.has(surface)) {
                screenshotsBySurface.set(surface, [])
            }

            screenshotsBySurface.get(surface).push({
                themeKey,
                path: path.join(themeDirectory, filename),
            })
        }
    }

    return [...screenshotsBySurface.entries()].sort(([left], [right]) =>
        left.localeCompare(right),
    )
}

function buildContactSheet(surface, screenshots, outputDirectory, tempRoot) {
    const surfaceTempDirectory = path.join(tempRoot, surface)
    fs.mkdirSync(surfaceTempDirectory, { recursive: true })

    const inputPaths = screenshots.map((screenshot) => {
        const linkPath = path.join(
            surfaceTempDirectory,
            `${screenshot.themeKey}.png`,
        )

        fs.symlinkSync(screenshot.path, linkPath)

        return linkPath
    })
    const outputPath = path.join(outputDirectory, `${surface}.jpg`)
    const args = [
        '-font',
        '/System/Library/Fonts/Supplemental/Arial.ttf',
        '-label',
        '%t',
        ...inputPaths,
        '-thumbnail',
        '360x275',
        '-geometry',
        '360x315+18+18',
        '-background',
        '#f4f1ea',
        '-fill',
        '#1c1917',
        outputPath,
    ]
    const result = spawnSync('montage', args, { stdio: 'pipe' })

    if (result.status !== 0) {
        throw new Error(
            [
                `Failed to build contact sheet for ${surface}.`,
                result.stderr.toString().trim(),
            ]
                .filter(Boolean)
                .join('\n'),
        )
    }

    return outputPath
}

function main() {
    const argv = process.argv.slice(2)
    const repoPath = path.resolve(readOption(argv, '--repo', process.cwd()))
    const outputDirectory = ensureSafeOutputDirectory(
        repoPath,
        readOption(
            argv,
            '--out',
            path.join(os.tmpdir(), 'capell-theme-audit/contact-sheets'),
        ),
    )
    const surfaces = collectOptions(argv, '--surface')
    const screenshotsBySurface = collectScreenshots(repoPath, surfaces)

    if (screenshotsBySurface.length === 0) {
        fail('No theme screenshot fixtures matched the requested surfaces.')

        return
    }

    const tempRoot = fs.mkdtempSync(
        path.join(os.tmpdir(), 'capell-theme-contact-sheets-'),
    )

    try {
        for (const [surface, screenshots] of screenshotsBySurface) {
            const outputPath = buildContactSheet(
                surface,
                screenshots,
                outputDirectory,
                tempRoot,
            )

            console.log(`${surface}: ${outputPath}`)
        }
    } finally {
        fs.rmSync(tempRoot, { recursive: true, force: true })
    }
}

try {
    main()
} catch (error) {
    fail(error instanceof Error ? error.message : String(error))
}
