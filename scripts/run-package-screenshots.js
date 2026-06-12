const { spawnSync } = require('child_process')
const fs = require('fs')
const path = require('path')

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

function readOnlyFile(filePath) {
    if (!filePath) {
        return []
    }

    return fs
        .readFileSync(filePath, 'utf8')
        .split(/\r?\n/)
        .map((line) => line.trim())
        .filter(Boolean)
}

function packageMatchesOnly(packageName, only) {
    if (only.length === 0) {
        return true
    }

    return only.some(
        (filter) =>
            filter === packageName || filter.startsWith(`${packageName}:`),
    )
}

function commandParamValue(param) {
    if (param === 'url') {
        return process.env.CAPELL_FRONTEND_URL ?? ''
    }

    if (param === 'languages') {
        return process.env.CAPELL_SCREENSHOT_LANGUAGES ?? ''
    }

    if (param === 'sites') {
        return process.env.CAPELL_SCREENSHOT_SITES ?? ''
    }

    return ''
}

function commandArgs(command, params) {
    const args = ['artisan', command]

    if (Array.isArray(params)) {
        for (const param of params) {
            if (typeof param === 'string' && param !== '') {
                const value = commandParamValue(param)

                if (value !== '') {
                    args.push(`--${param}=${value}`)
                }
            }
        }
    }

    return args
}

function selectedPackageNames(repoPath, only) {
    const packagesPath = path.join(repoPath, 'packages')

    if (!fs.existsSync(packagesPath)) {
        return []
    }

    return fs
        .readdirSync(packagesPath, { withFileTypes: true })
        .filter((entry) => entry.isDirectory())
        .map((entry) => entry.name)
        .filter((packageName) => packageMatchesOnly(packageName, only))
        .sort()
}

function filtersForPackage(packageName, only) {
    if (only.length === 0) {
        return [packageName]
    }

    return only.filter(
        (filter) =>
            filter === packageName || filter.startsWith(`${packageName}:`),
    )
}

function packageFrontendCssFiles(repoPath, packageName) {
    const cssPath = path.join(
        repoPath,
        'packages',
        packageName,
        'resources',
        'css',
    )

    if (!fs.existsSync(cssPath)) {
        return []
    }

    return fs
        .readdirSync(cssPath, { withFileTypes: true })
        .filter((entry) => entry.isFile() && entry.name.endsWith('.css'))
        .map((entry) => path.join(cssPath, entry.name))
        .sort()
}

function cssImportPath(fromFile, importedFile) {
    const relativePath = path
        .relative(path.dirname(fromFile), importedFile)
        .split(path.sep)
        .join('/')

    return relativePath.startsWith('.') ? relativePath : `./${relativePath}`
}

function injectPackageFrontendCss(repoPath, appPath, only) {
    if (!appPath) {
        return
    }

    const frontendCssPath = path.join(
        appPath,
        'resources',
        'css',
        'capell',
        'frontend.css',
    )

    if (!fs.existsSync(frontendCssPath)) {
        return
    }

    const imports = selectedPackageNames(repoPath, only)
        .flatMap((packageName) =>
            packageFrontendCssFiles(repoPath, packageName),
        )
        .map(
            (cssFile) =>
                `@import "${cssImportPath(frontendCssPath, cssFile)}";`,
        )

    if (imports.length === 0) {
        return
    }

    const content = fs.readFileSync(frontendCssPath, 'utf8')
    const missingImports = imports.filter(
        (importLine) => !content.includes(importLine),
    )

    if (missingImports.length === 0) {
        return
    }

    fs.writeFileSync(
        frontendCssPath,
        `${content.trimEnd()}\n\n${missingImports.join('\n')}\n`,
    )
}

function runPackageCommands(repoPath, appPath, only, packages = null) {
    if (!appPath) {
        return
    }

    const packagesPath = path.join(repoPath, 'packages')

    for (const packageName of packages ??
        selectedPackageNames(repoPath, only)) {
        const manifestPath = path.join(packagesPath, packageName, 'capell.json')

        if (!fs.existsSync(manifestPath)) {
            continue
        }

        const manifest = JSON.parse(fs.readFileSync(manifestPath, 'utf8'))
        const commands = manifest.commands ?? {}

        for (const [key, paramsKey] of [
            ['setup', 'setupParams'],
            ['demo', 'demoParams'],
        ]) {
            const command = commands[key]

            if (typeof command !== 'string' || command === '') {
                continue
            }

            const result = spawnSync(
                'php',
                [
                    '-d',
                    'memory_limit=-1',
                    ...commandArgs(command, commands[paramsKey]),
                ],
                {
                    cwd: appPath,
                    env: process.env,
                    stdio: 'inherit',
                },
            )

            if (result.status !== 0) {
                process.exitCode = result.status ?? 1

                return
            }
        }
    }
}

function runRunner(runnerPath, runnerArgs, repoPath) {
    const result = spawnSync('node', runnerArgs, {
        cwd: runnerPath,
        env: {
            ...process.env,
            CAPELL_INSIGHTS_CONSENT_BANNER_ENABLED:
                process.env.CAPELL_INSIGHTS_CONSENT_BANNER_ENABLED ?? 'false',
            CAPELL_INSIGHTS_SCREENSHOT_FIXTURES_ENABLED:
                process.env.CAPELL_INSIGHTS_SCREENSHOT_FIXTURES_ENABLED ??
                'true',
            CAPELL_PACKAGES_REPO: repoPath,
        },
        stdio: 'inherit',
    })

    process.exitCode = result.status ?? 1
}

function runnerArgsForPackage(repoPath, appPath, only, packageName, skipBuild) {
    const args = ['src/cli.mjs', '--repo', repoPath]

    if (appPath) {
        args.push('--app', path.resolve(appPath))
    }

    for (const filter of filtersForPackage(packageName, only)) {
        args.push('--only', filter)
    }

    if (skipBuild) {
        args.push('--skip-build')
    }

    return args
}

function preflightRunnerArgs(repoPath, appPath) {
    const args = ['src/cli.mjs', '--repo', repoPath]

    if (appPath) {
        args.push('--app', appPath)
    }

    args.push('--only', '__capell_preflight__')

    return args
}

function main() {
    const argv = process.argv.slice(2)
    const defaultRunnerPath = path.resolve(
        process.cwd(),
        '../capell-screenshot-runner',
    )
    const configuredRunnerPath = readOption(
        argv,
        '--runner',
        process.env.CAPELL_SCREENSHOT_RUNNER_PATH ||
            (fs.existsSync(path.join(defaultRunnerPath, 'src/cli.mjs'))
                ? defaultRunnerPath
                : ''),
    )
    const runnerPath = path.resolve(configuredRunnerPath)
    const repoPath = path.resolve(
        readOption(
            argv,
            '--repo',
            process.env.CAPELL_PACKAGES_REPO_PATH ?? process.cwd(),
        ),
    )
    const appPath = readOption(
        argv,
        '--app',
        process.env.CAPELL_SCREENSHOT_APP_PATH ?? '',
    )
    const only = [
        ...collectOptions(argv, '--only'),
        ...readOnlyFile(readOption(argv, '--only-file')),
    ]

    if (!configuredRunnerPath) {
        console.error(
            'Set CAPELL_SCREENSHOT_RUNNER_PATH or pass --runner to locate capell-screenshot-runner.',
        )
        process.exitCode = 1

        return
    }

    if (!fs.existsSync(path.join(runnerPath, 'src/cli.mjs'))) {
        console.error(`Screenshot runner was not found at ${runnerPath}.`)
        process.exitCode = 1

        return
    }

    if (argv.includes('--dry-run')) {
        const runnerArgs = ['src/cli.mjs', '--repo', repoPath]

        if (appPath) {
            runnerArgs.push('--app', path.resolve(appPath))
        }

        for (const packageName of only) {
            runnerArgs.push('--only', packageName)
        }

        runnerArgs.push('--dry-run')

        if (argv.includes('--skip-build')) {
            runnerArgs.push('--skip-build')
        }

        runRunner(runnerPath, runnerArgs, repoPath)

        return
    }

    const resolvedAppPath = appPath ? path.resolve(appPath) : ''
    const shouldSkipBuild = argv.includes('--skip-build')
    const shouldReuseApp = argv.includes('--reuse-app')
    const packageNames = selectedPackageNames(repoPath, only)

    if (shouldReuseApp && !resolvedAppPath) {
        console.error(
            'Pass --app or set CAPELL_SCREENSHOT_APP_PATH when using --reuse-app.',
        )
        process.exitCode = 1

        return
    }

    if (!shouldReuseApp) {
        injectPackageFrontendCss(repoPath, resolvedAppPath, only)
    }

    if (!shouldReuseApp && !shouldSkipBuild && resolvedAppPath) {
        runRunner(
            runnerPath,
            preflightRunnerArgs(repoPath, resolvedAppPath),
            repoPath,
        )

        if (process.exitCode) {
            return
        }
    }

    for (const packageName of packageNames) {
        if (!shouldReuseApp) {
            runPackageCommands(repoPath, resolvedAppPath, only, [packageName])

            if (process.exitCode) {
                return
            }
        }

        runRunner(
            runnerPath,
            runnerArgsForPackage(
                repoPath,
                resolvedAppPath,
                only,
                packageName,
                true,
            ),
            repoPath,
        )

        if (process.exitCode) {
            return
        }
    }
}

main()
