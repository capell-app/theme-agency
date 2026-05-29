const fs = require('fs')

const paths = fs
    .readFileSync(0, 'utf8')
    .split(/\r?\n/)
    .map((line) => line.trim())
    .filter(Boolean)

const packages = new Set()

for (const filePath of paths) {
    const packageMatch = filePath.match(
        /^packages\/([^/]+)\/docs\/screenshots\.json$/,
    )

    if (packageMatch !== null) {
        packages.add(packageMatch[1])
    }
}

for (const packageName of [...packages].sort()) {
    console.log(packageName)
}
