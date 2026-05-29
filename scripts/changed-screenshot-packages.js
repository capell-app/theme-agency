const fs = require('fs')

const paths = fs
    .readFileSync(0, 'utf8')
    .split(/\r?\n/)
    .map((line) => line.trim())
    .filter(Boolean)

const packages = new Set()

for (const filePath of paths) {
    const monorepoMatch = filePath.match(
        /^packages\/([^/]+)\/docs\/screenshots\.json$/,
    )

    if (monorepoMatch !== null) {
        packages.add(monorepoMatch[1])

        continue
    }

    if (
        filePath === 'docs/screenshots.json' &&
        process.env.CAPELL_PACKAGE_SLUG
    ) {
        packages.add(process.env.CAPELL_PACKAGE_SLUG)
    }
}

for (const packageName of [...packages].sort()) {
    console.log(packageName)
}
