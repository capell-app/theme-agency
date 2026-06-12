const fs = require('fs')
const os = require('os')
const path = require('path')
const { execFileSync } = require('child_process')

const root = process.cwd()
const packagesRoot = path.join(root, 'packages')
const tempRoot = fs.mkdtempSync(
    path.join(os.tmpdir(), 'capell-marketplace-art-'),
)

const palette = {
    black: '#101114',
    slate: '#4b5563',
    lightSlate: '#cbd5e1',
    blue: '#0066ff',
    orange: '#ff6b1a',
    emerald: '#007f5f',
    white: '#ffffff',
    paper: '#f8fafc',
}

const categoryProfiles = [
    {
        match: ['theme', 'frontend', 'inertia'],
        key: 'frontend',
        label: 'Public surface',
    },
    {
        match: [
            'blog',
            'publishing',
            'newsletter',
            'email',
            'document',
            'content',
        ],
        key: 'publishing',
        label: 'Editorial flow',
    },
    {
        match: ['ai', 'agent', 'orchestrator', 'media-ai'],
        key: 'ai',
        label: 'Agent ready',
    },
    {
        match: [
            'analytics',
            'reports',
            'ga4',
            'insights',
            'seo',
            'search',
            'diagnostics',
        ],
        key: 'insight',
        label: 'Signal layer',
    },
    {
        match: ['access', 'password', 'privacy', 'login', 'policy'],
        key: 'security',
        label: 'Control layer',
    },
    {
        match: ['payments', 'shopify', 'commerce'],
        key: 'commerce',
        label: 'Revenue path',
    },
    {
        match: ['bookings', 'events', 'calendar'],
        key: 'calendar',
        label: 'Scheduling',
    },
    {
        match: [
            'api',
            'automation',
            'deployments',
            'cache',
            'optimizer',
            'migration',
        ],
        key: 'ops',
        label: 'Delivery ops',
    },
    {
        match: ['form', 'comments', 'contacts', 'public-actions'],
        key: 'forms',
        label: 'Interaction',
    },
    {
        match: ['layout', 'block', 'sections', 'hero', 'navigation', 'tags'],
        key: 'composition',
        label: 'Page system',
    },
]

const packageNames = collectPackageNames(process.argv.slice(2))

try {
    for (const packageName of packageNames) {
        const packagePath = path.join(packagesRoot, packageName)
        const manifestPath = path.join(packagePath, 'capell.json')

        if (!fs.existsSync(manifestPath)) {
            continue
        }

        const manifest = JSON.parse(fs.readFileSync(manifestPath, 'utf8'))
        const targetPath = resolveTargetPath(packageName, packagePath, manifest)
        const screenshots = collectScreenshots(packagePath, manifest)
        const screenshotData = screenshots
            .slice(0, 2)
            .map((screenshotPath, index) =>
                createScreenshotData(packageName, screenshotPath, index),
            )

        fs.mkdirSync(path.dirname(targetPath), { recursive: true })

        if (targetPath.endsWith('.svg')) {
            const svg = buildSvg(packageName, manifest, screenshotData)

            fs.writeFileSync(targetPath, svg)

            console.log(path.relative(root, targetPath))

            continue
        }

        const sourcePath = path.join(tempRoot, `${packageName}.svg`)
        const svg = buildSvg(packageName, manifest, screenshotData)

        fs.writeFileSync(sourcePath, svg)
        const rasterFormat = targetPath.endsWith('.png') ? 'png' : 'jpeg'

        execFileSync('sips', [
            '-s',
            'format',
            rasterFormat,
            sourcePath,
            '--out',
            targetPath,
        ])

        console.log(path.relative(root, targetPath))
    }
} finally {
    fs.rmSync(tempRoot, { recursive: true, force: true })
}

function collectPackageNames(argv) {
    const requested = new Set()

    for (let index = 0; index < argv.length; index += 1) {
        const value = readFlagValue(argv, index, '--only')

        if (value !== null) {
            if (value !== '') {
                requested.add(value)
            }

            if (argv[index] === '--only') {
                index += 1
            }
        }
    }

    const packageNames = fs
        .readdirSync(packagesRoot, { withFileTypes: true })
        .filter((entry) => entry.isDirectory())
        .map((entry) => entry.name)
        .sort()

    return requested.size === 0
        ? packageNames
        : packageNames.filter((packageName) => requested.has(packageName))
}

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

function resolveTargetPath(packageName, packagePath, manifest) {
    const marketplaceScreenshots = Array.isArray(
        manifest.marketplace?.screenshots,
    )
        ? manifest.marketplace.screenshots
        : []
    const referencedCard = marketplaceScreenshots
        .map((screenshot) => screenshot.path)
        .find((screenshotPath) =>
            /^docs\/assets\/marketplace\/extension-card\.(jpg|jpeg|png|svg)$/i.test(
                screenshotPath ?? '',
            ),
        )

    if (referencedCard !== undefined) {
        return path.join(packagePath, referencedCard)
    }

    const assetsPath = path.join(packagePath, 'docs/assets/marketplace')
    const existingCard = fs.existsSync(assetsPath)
        ? fs
              .readdirSync(assetsPath)
              .find((fileName) =>
                  /^extension-card\.(jpg|jpeg|png|svg)$/i.test(fileName),
              )
        : undefined

    if (existingCard !== undefined) {
        return path.join(assetsPath, existingCard)
    }

    return path.join(assetsPath, 'extension-card.jpg')
}

function collectScreenshots(packagePath, manifest) {
    const marketplaceScreenshots = Array.isArray(
        manifest.marketplace?.screenshots,
    )
        ? manifest.marketplace.screenshots
        : []
    const candidates = marketplaceScreenshots
        .map((screenshot) => screenshot.path)
        .filter((screenshotPath) =>
            screenshotPath?.startsWith('docs/screenshots/'),
        )
        .map((screenshotPath) => path.join(packagePath, screenshotPath))

    const screenshotDirectory = path.join(packagePath, 'docs/screenshots')

    if (fs.existsSync(screenshotDirectory)) {
        for (const fileName of fs.readdirSync(screenshotDirectory).sort()) {
            if (fileName.endsWith('.png')) {
                candidates.push(path.join(screenshotDirectory, fileName))
            }
        }
    }

    return [...new Set(candidates)].filter((screenshotPath) =>
        fs.existsSync(screenshotPath),
    )
}

function createScreenshotData(packageName, screenshotPath, index) {
    const targetPath = path.join(tempRoot, `${packageName}-${index}.jpg`)

    execFileSync('magick', [
        screenshotPath,
        '-resize',
        '620x360^',
        '-gravity',
        'center',
        '-extent',
        '620x360',
        '-strip',
        '-quality',
        '78',
        targetPath,
    ])

    return {
        dataHref: `data:image/jpeg;base64,${fs.readFileSync(targetPath).toString('base64')}`,
    }
}

function buildSvg(packageName, manifest, screenshotData) {
    const title = manifest.displayName ?? titleFromSlug(packageName)
    const summary = manifest.marketplace?.summary ?? manifest.description ?? ''
    const categories = [
        ...(manifest.marketplace?.categories ?? []),
        ...(manifest.surfaces ?? []),
        manifest.product?.bundle,
        manifest.product?.group,
        packageName,
    ]
        .filter(Boolean)
        .map((value) => String(value).toLowerCase())
    const profile = resolveProfile(categories)
    const hash = hashString(packageName)
    const accentOrder = rotate(
        [palette.blue, palette.orange, palette.emerald],
        hash % 3,
    )
    const surfaceText = (manifest.surfaces ?? []).join(' / ') || profile.label
    const tier = String(manifest.product?.tier ?? 'extension')
    const bundle = String(manifest.product?.bundle ?? 'capell')
    const textureId = `texture-${packageName}`
    const lineId = `lines-${packageName}`
    const screenshotOne = screenshotData[0] ?? null
    const screenshotTwo = screenshotData[1] ?? null
    const screenshotHref = (screenshot) => screenshot.dataHref

    return `<svg xmlns="http://www.w3.org/2000/svg" width="1280" height="720" viewBox="0 0 1280 720" role="img" aria-labelledby="title desc">
  <title id="title">${escapeXml(title)} marketplace artwork</title>
  <desc id="desc">${escapeXml(summary || `${title} Capell package artwork`)}</desc>
  <defs>
    <pattern id="${textureId}" width="14" height="14" patternUnits="userSpaceOnUse">
      <circle cx="2" cy="2" r="1.2" fill="${palette.slate}" opacity="0.14"/>
    </pattern>
    <pattern id="${lineId}" width="18" height="18" patternUnits="userSpaceOnUse" patternTransform="rotate(45)">
      <line x1="0" y1="0" x2="0" y2="18" stroke="${palette.black}" stroke-width="1" opacity="0.08"/>
    </pattern>
  </defs>
  <rect width="1280" height="720" fill="${palette.white}"/>
  <rect x="0" y="0" width="1280" height="720" fill="url(#${textureId})"/>
  <rect x="0" y="0" width="1280" height="720" fill="url(#${lineId})"/>
  <g transform="translate(64 58)">
    <text x="0" y="0" fill="${palette.black}" font-family="Helvetica, Arial, sans-serif" font-size="22" font-weight="700">${escapeXml(bundle.toUpperCase())}</text>
    <text x="0" y="50" fill="${palette.black}" font-family="Helvetica, Arial, sans-serif" font-size="52" font-weight="760">${escapeXml(title)}</text>
    ${multilineText(summary, 0, 92, 48, 2, palette.slate, 23, 31)}
    <text x="1152" y="0" fill="${palette.slate}" font-family="Helvetica, Arial, sans-serif" font-size="18" font-weight="700" text-anchor="end">${escapeXml(surfaceText.toUpperCase())}</text>
    <text x="1152" y="31" fill="${palette.slate}" font-family="Helvetica, Arial, sans-serif" font-size="18" font-weight="700" text-anchor="end">${escapeXml(tier.toUpperCase())}</text>
  </g>
  <g transform="translate(64 190)">
    ${showcasePanel({
        x: 0,
        title: 'Starter',
        caption: 'Launch surface',
        accent: accentOrder[0],
        image: screenshotOne === null ? null : screenshotHref(screenshotOne),
        icon: cubeIcon(
            138,
            216,
            124,
            accentOrder[0],
            accentOrder[1],
            profile.key,
            hash,
        ),
        motif: packageMotif(profile.key, 42, 220, 58, accentOrder[0], hash),
    })}
    ${showcasePanel({
        x: 410,
        title: 'Pro',
        caption: profile.label,
        accent: accentOrder[1],
        image: screenshotTwo === null ? null : screenshotHref(screenshotTwo),
        icon: stairsIcon(
            104,
            204,
            170,
            accentOrder[1],
            accentOrder[2],
            profile.key,
            hash,
        ),
        motif: packageMotif(
            profile.key,
            278,
            220,
            58,
            accentOrder[1],
            hash + 17,
        ),
    })}
    ${showcasePanel({
        x: 820,
        title: 'Enterprise',
        caption: 'Scaled network',
        accent: accentOrder[2],
        image: null,
        icon: hexNetworkIcon(
            83,
            165,
            216,
            accentOrder[2],
            accentOrder[0],
            profile.key,
            hash,
        ),
        motif: packageMotif(
            profile.key,
            270,
            70,
            78,
            accentOrder[2],
            hash + 31,
        ),
    })}
  </g>
</svg>
`
}

function showcasePanel({ x, title, caption, accent, image, icon, motif }) {
    const imageMarkup =
        image === null
            ? mockScreen(38, 30, 292, 170, accent)
            : `<rect x="32" y="24" width="304" height="184" fill="${palette.black}"/>
    <rect x="44" y="36" width="280" height="160" fill="${palette.paper}"/>
    <image href="${escapeXml(image)}" x="44" y="36" width="280" height="160" preserveAspectRatio="xMidYMid slice"/>`

    return `<g transform="translate(${x} 0)">
    <rect x="0" y="0" width="360" height="452" fill="${palette.white}" stroke="${palette.black}" stroke-width="3"/>
    <rect x="18" y="18" width="324" height="416" fill="none" stroke="${palette.lightSlate}" stroke-width="2"/>
    ${imageMarkup}
    ${icon}
    ${motif}
    <line x1="38" y1="374" x2="322" y2="374" stroke="${palette.black}" stroke-width="4"/>
    <text x="38" y="411" fill="${palette.black}" font-family="Helvetica, Arial, sans-serif" font-size="28" font-weight="760">${escapeXml(title)}</text>
    <text x="38" y="433" fill="${palette.slate}" font-family="Helvetica, Arial, sans-serif" font-size="14" font-weight="700">${escapeXml(caption.toUpperCase())}</text>
  </g>`
}

function cubeIcon(x, y, size, primary, secondary, profileKey, hash) {
    const inset = Math.round(size * 0.18)
    const variantLine =
        profileKey === 'security' ? 'M82 34 v-12 a22 22 0 0 0 -44 0 v12' : ''

    return `<g transform="translate(${x} ${y})">
    <line x1="${-22}" y1="${size + 28}" x2="${size + 54}" y2="${size + 28}" stroke="${palette.black}" stroke-width="8"/>
    <rect x="0" y="18" width="${size}" height="${size}" fill="${primary}" stroke="${palette.black}" stroke-width="6"/>
    <path d="M${inset} ${18 + inset} L${size - inset} ${18 + inset} L${size - inset} ${18 + size - inset} L${inset} ${18 + size - inset} Z" fill="none" stroke="${palette.white}" stroke-width="5" opacity="0.78"/>
    <path d="M${size / 2} 34 L${size / 2} ${size + 4} M34 ${size / 2 + 18} L${size - 34} ${size / 2 + 18}" stroke="${palette.black}" stroke-width="4" opacity="0.42"/>
    <path d="M${size + 48} ${size + 26} V15" fill="none" stroke="${secondary}" stroke-width="9" stroke-linecap="square"/>
    <path d="M${size + 24} 38 L${size + 48} 10 L${size + 72} 38" fill="none" stroke="${secondary}" stroke-width="9" stroke-linecap="square" stroke-linejoin="miter"/>
    ${variantLine === '' ? fineMarks(size, hash) : `<path d="${variantLine}" fill="none" stroke="${palette.white}" stroke-width="8"/>`}
  </g>`
}

function stairsIcon(x, y, size, primary, secondary, profileKey, hash) {
    const stepWidth = Math.round(size / 3)
    const stepHeight = 42 + (hash % 4) * 4

    return `<g transform="translate(${x} ${y})">
    <rect x="0" y="${stepHeight * 2}" width="${stepWidth + 18}" height="${stepHeight}" fill="${palette.black}"/>
    <rect x="${stepWidth - 6}" y="${stepHeight}" width="${stepWidth + 18}" height="${stepHeight * 2}" fill="${primary}" stroke="${palette.black}" stroke-width="5"/>
    <rect x="${stepWidth * 2 - 12}" y="0" width="${stepWidth + 18}" height="${stepHeight * 3}" fill="${secondary}" stroke="${palette.black}" stroke-width="5"/>
    <path d="M22 ${stepHeight * 2 - 18} H${stepWidth + 28} V${stepHeight - 18} H${stepWidth * 2 + 20} V-18 H${stepWidth * 3 + 38}" fill="none" stroke="${palette.black}" stroke-width="7"/>
    ${packageMotif(profileKey, stepWidth * 2 + 4, stepHeight + 18, 54, palette.white, hash)}
    <circle cx="${stepWidth * 3 + 52}" cy="-18" r="10" fill="${palette.black}"/>
  </g>`
}

function hexNetworkIcon(x, y, size, primary, secondary, profileKey, hash) {
    const points = hexPoints(size / 2, size / 2, size / 2)
    const nodes = networkNodes(size, hash)
    const lines = nodes
        .slice(1)
        .map((node, index) => {
            const previous = nodes[index]

            return `<line x1="${previous[0]}" y1="${previous[1]}" x2="${node[0]}" y2="${node[1]}" stroke="${palette.white}" stroke-width="3" opacity="0.8"/>`
        })
        .join('\n    ')
    const crossLines = nodes
        .filter((_, index) => index % 2 === 0)
        .map((node, index) => {
            const target = nodes[(index * 3 + 5) % nodes.length]

            return `<line x1="${node[0]}" y1="${node[1]}" x2="${target[0]}" y2="${target[1]}" stroke="${secondary}" stroke-width="3" opacity="0.88"/>`
        })
        .join('\n    ')
    const nodeMarkup = nodes
        .map(
            (node, index) =>
                `<circle cx="${node[0]}" cy="${node[1]}" r="${index % 3 === 0 ? 7 : 5}" fill="${index % 2 === 0 ? palette.white : secondary}" stroke="${palette.black}" stroke-width="2"/>`,
        )
        .join('\n    ')

    return `<g transform="translate(${x} ${y})">
    <polygon points="${points}" fill="${primary}" stroke="${palette.black}" stroke-width="7"/>
    ${lines}
    ${crossLines}
    ${nodeMarkup}
    ${packageMotif(profileKey, size / 2 - 27, size / 2 - 27, 54, palette.black, hash)}
  </g>`
}

function mockScreen(x, y, width, height, accent) {
    return `<g transform="translate(${x} ${y})">
    <rect width="${width}" height="${height}" fill="${palette.black}"/>
    <rect x="12" y="12" width="${width - 24}" height="${height - 24}" fill="${palette.paper}"/>
    <rect x="28" y="32" width="${width - 56}" height="18" fill="${accent}"/>
    <rect x="28" y="70" width="${Math.round((width - 56) * 0.58)}" height="14" fill="${palette.slate}"/>
    <rect x="28" y="100" width="${width - 96}" height="12" fill="${palette.lightSlate}"/>
    <rect x="28" y="126" width="${width - 128}" height="12" fill="${palette.lightSlate}"/>
    <rect x="${width - 78}" y="70" width="50" height="68" fill="${palette.white}" stroke="${palette.black}" stroke-width="3"/>
  </g>`
}

function packageMotif(profileKey, x, y, size, color, hash) {
    const stroke = color === palette.white ? palette.black : color
    const fill = color

    switch (profileKey) {
        case 'ai':
            return `<g transform="translate(${x} ${y})">
    <circle cx="${size * 0.18}" cy="${size * 0.28}" r="7" fill="${fill}"/>
    <circle cx="${size * 0.72}" cy="${size * 0.2}" r="7" fill="${fill}"/>
    <circle cx="${size * 0.56}" cy="${size * 0.72}" r="7" fill="${fill}"/>
    <line x1="${size * 0.18}" y1="${size * 0.28}" x2="${size * 0.72}" y2="${size * 0.2}" stroke="${fill}" stroke-width="5"/>
    <line x1="${size * 0.72}" y1="${size * 0.2}" x2="${size * 0.56}" y2="${size * 0.72}" stroke="${fill}" stroke-width="5"/>
    <line x1="${size * 0.56}" y1="${size * 0.72}" x2="${size * 0.18}" y2="${size * 0.28}" stroke="${fill}" stroke-width="5"/>
  </g>`
        case 'security':
            return `<g transform="translate(${x} ${y})">
    <path d="M${size * 0.5} 4 L${size - 6} ${size * 0.22} V${size * 0.55} C${size - 6} ${size * 0.78} ${size * 0.68} ${size - 4} ${size * 0.5} ${size - 2} C${size * 0.32} ${size - 4} 6 ${size * 0.78} 6 ${size * 0.55} V${size * 0.22} Z" fill="${fill}" stroke="${palette.black}" stroke-width="4"/>
    <path d="M${size * 0.33} ${size * 0.48} L${size * 0.45} ${size * 0.6} L${size * 0.68} ${size * 0.36}" fill="none" stroke="${palette.white}" stroke-width="5"/>
  </g>`
        case 'commerce':
            return `<g transform="translate(${x} ${y})">
    <rect x="10" y="18" width="${size - 20}" height="${size * 0.48}" fill="${fill}" stroke="${palette.black}" stroke-width="4"/>
    <path d="M${size * 0.28} 18 C${size * 0.32} 4 ${size * 0.68} 4 ${size * 0.72} 18" fill="none" stroke="${palette.black}" stroke-width="5"/>
    <circle cx="${size * 0.34}" cy="${size * 0.82}" r="7" fill="${palette.black}"/>
    <circle cx="${size * 0.68}" cy="${size * 0.82}" r="7" fill="${palette.black}"/>
  </g>`
        case 'calendar':
            return `<g transform="translate(${x} ${y})">
    <rect x="8" y="12" width="${size - 16}" height="${size - 18}" fill="${palette.white}" stroke="${stroke}" stroke-width="5"/>
    <rect x="8" y="12" width="${size - 16}" height="20" fill="${fill}" stroke="${stroke}" stroke-width="5"/>
    <rect x="22" y="46" width="14" height="14" fill="${stroke}"/>
    <rect x="48" y="46" width="14" height="14" fill="${stroke}"/>
    <rect x="22" y="72" width="40" height="12" fill="${fill}"/>
  </g>`
        case 'insight':
            return `<g transform="translate(${x} ${y})">
    <rect x="10" y="${size * 0.56}" width="12" height="${size * 0.34}" fill="${fill}"/>
    <rect x="${size * 0.36}" y="${size * 0.36}" width="12" height="${size * 0.54}" fill="${fill}"/>
    <rect x="${size * 0.62}" y="${size * 0.18}" width="12" height="${size * 0.72}" fill="${fill}"/>
    <path d="M8 ${size * 0.26} L${size * 0.36} ${size * 0.44} L${size * 0.66} ${size * 0.18} L${size - 8} ${size * 0.28}" fill="none" stroke="${fill}" stroke-width="5"/>
  </g>`
        case 'forms':
            return `<g transform="translate(${x} ${y})">
    <rect x="8" y="8" width="${size - 16}" height="${size - 16}" fill="${palette.white}" stroke="${stroke}" stroke-width="5"/>
    <line x1="24" y1="30" x2="${size - 24}" y2="30" stroke="${fill}" stroke-width="7"/>
    <line x1="24" y1="52" x2="${size - 36}" y2="52" stroke="${stroke}" stroke-width="5"/>
    <line x1="24" y1="74" x2="${size - 46}" y2="74" stroke="${stroke}" stroke-width="5"/>
  </g>`
        case 'ops':
            return `<g transform="translate(${x} ${y})">
    <rect x="6" y="12" width="${size - 12}" height="22" fill="${fill}" stroke="${palette.black}" stroke-width="4"/>
    <rect x="6" y="46" width="${size - 12}" height="22" fill="${palette.white}" stroke="${stroke}" stroke-width="4"/>
    <rect x="6" y="80" width="${size - 12}" height="22" fill="${fill}" stroke="${palette.black}" stroke-width="4"/>
    <circle cx="${size - 20}" cy="23" r="4" fill="${palette.black}"/>
    <circle cx="${size - 20}" cy="57" r="4" fill="${stroke}"/>
    <circle cx="${size - 20}" cy="91" r="4" fill="${palette.black}"/>
  </g>`
        case 'composition':
            return `<g transform="translate(${x} ${y})">
    <rect x="4" y="4" width="${size * 0.42}" height="${size * 0.42}" fill="${fill}" stroke="${palette.black}" stroke-width="4"/>
    <rect x="${size * 0.54}" y="4" width="${size * 0.42}" height="${size * 0.42}" fill="${palette.white}" stroke="${stroke}" stroke-width="4"/>
    <rect x="4" y="${size * 0.54}" width="${size * 0.42}" height="${size * 0.42}" fill="${palette.white}" stroke="${stroke}" stroke-width="4"/>
    <rect x="${size * 0.54}" y="${size * 0.54}" width="${size * 0.42}" height="${size * 0.42}" fill="${fill}" stroke="${palette.black}" stroke-width="4"/>
  </g>`
        case 'frontend':
            return `<g transform="translate(${x} ${y})">
    <path d="M${size * 0.36} ${size * 0.24} L${size * 0.16} ${size * 0.5} L${size * 0.36} ${size * 0.76}" fill="none" stroke="${fill}" stroke-width="7"/>
    <path d="M${size * 0.64} ${size * 0.24} L${size * 0.84} ${size * 0.5} L${size * 0.64} ${size * 0.76}" fill="none" stroke="${fill}" stroke-width="7"/>
    <line x1="${size * 0.48}" y1="${size * 0.78}" x2="${size * 0.58}" y2="${size * 0.22}" stroke="${stroke}" stroke-width="5"/>
  </g>`
        default:
            return `<g transform="translate(${x} ${y}) rotate(${hash % 2 === 0 ? 0 : 45} ${size / 2} ${size / 2})">
    <rect x="14" y="14" width="${size - 28}" height="${size - 28}" fill="${fill}" stroke="${palette.black}" stroke-width="4"/>
    <circle cx="${size / 2}" cy="${size / 2}" r="${size * 0.18}" fill="${palette.white}" stroke="${palette.black}" stroke-width="4"/>
  </g>`
    }
}

function resolveProfile(categories) {
    for (const profile of categoryProfiles) {
        if (
            profile.match.some((term) =>
                categories.some((category) => category.includes(term)),
            )
        ) {
            return profile
        }
    }

    return { key: 'default', label: 'Package layer' }
}

function titleFromSlug(slug) {
    return slug
        .split('-')
        .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
        .join(' ')
}

function multilineText(
    text,
    x,
    y,
    maxChars,
    maxLines,
    fill,
    fontSize,
    lineHeight,
) {
    const lines = wrapText(text, maxChars, maxLines)

    return lines
        .map(
            (line, index) =>
                `<text x="${x}" y="${y + index * lineHeight}" fill="${fill}" font-family="Helvetica, Arial, sans-serif" font-size="${fontSize}">${escapeXml(line)}</text>`,
        )
        .join('\n    ')
}

function wrapText(text, maxChars, maxLines) {
    const words = String(text).replace(/\s+/g, ' ').trim().split(' ')
    const lines = []
    let current = ''

    for (const word of words) {
        const next = current === '' ? word : `${current} ${word}`

        if (next.length <= maxChars) {
            current = next

            continue
        }

        if (current !== '') {
            lines.push(current)
        }

        current = word

        if (lines.length === maxLines) {
            break
        }
    }

    if (current !== '' && lines.length < maxLines) {
        lines.push(current)
    }

    if (lines.length > 0 && words.join(' ').length > lines.join(' ').length) {
        lines[lines.length - 1] =
            `${lines[lines.length - 1].replace(/[,.]$/, '')}...`
    }

    return lines
}

function escapeXml(value) {
    return String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
}

function hashString(value) {
    let hash = 0

    for (let index = 0; index < value.length; index += 1) {
        hash = (hash * 31 + value.charCodeAt(index)) >>> 0
    }

    return hash
}

function rotate(values, offset) {
    return [...values.slice(offset), ...values.slice(0, offset)]
}

function fineMarks(size, hash) {
    const count = 3 + (hash % 3)
    const marks = []

    for (let index = 0; index < count; index += 1) {
        const offset = 28 + index * 18

        marks.push(
            `<line x1="${offset}" y1="${size + 36}" x2="${offset + 18}" y2="${size + 50}" stroke="${palette.black}" stroke-width="4" opacity="0.55"/>`,
        )
    }

    return marks.join('\n    ')
}

function hexPoints(cx, cy, radius) {
    const points = []

    for (let index = 0; index < 6; index += 1) {
        const angle = (Math.PI / 180) * (60 * index - 30)

        points.push(
            `${Math.round(cx + radius * Math.cos(angle))},${Math.round(cy + radius * Math.sin(angle))}`,
        )
    }

    return points.join(' ')
}

function networkNodes(size, hash) {
    const baseNodes = [
        [0.25, 0.28],
        [0.48, 0.18],
        [0.72, 0.3],
        [0.67, 0.58],
        [0.5, 0.78],
        [0.27, 0.64],
        [0.38, 0.46],
        [0.57, 0.42],
        [0.5, 0.56],
    ]

    return baseNodes.map(([x, y], index) => {
        const jitter = ((hash >> (index % 8)) & 3) - 1

        return [
            Math.round(x * size + jitter * 4),
            Math.round(y * size - jitter * 3),
        ]
    })
}
