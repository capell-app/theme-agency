const fs = require('fs')
const os = require('os')
const path = require('path')
const { execFileSync } = require('child_process')

const root = process.cwd()
const packagesRoot = path.join(root, 'packages')
const outputManifestPath = path.join(root, 'docs/marketplace-artwork.json')
const tempRoot = fs.mkdtempSync(
    path.join(os.tmpdir(), 'capell-marketplace-art-'),
)

const palette = {
    ink: '#111827',
    slate: '#475569',
    muted: '#94a3b8',
    line: '#cbd5e1',
    paper: '#f8fafc',
    white: '#ffffff',
    blue: '#0b63f6',
    cyan: '#0ea5e9',
    emerald: '#047857',
    green: '#16a34a',
    orange: '#f97316',
    amber: '#f59e0b',
    rose: '#e11d48',
    violet: '#7c3aed',
    zinc: '#27272a',
}

const variants = [
    {
        key: 'extensionCard',
        label: 'Marketplace card',
        width: 1280,
        height: 720,
        target: 'extension-card',
    },
    {
        key: 'heroDesktop',
        label: 'Desktop hero',
        width: 1600,
        height: 900,
        target: 'hero-desktop.jpg',
    },
    {
        key: 'heroMobile',
        label: 'Mobile hero',
        width: 900,
        height: 1200,
        target: 'hero-mobile.jpg',
    },
    {
        key: 'tileSquare',
        label: 'Square tile',
        width: 1200,
        height: 1200,
        target: 'tile-square.jpg',
    },
    {
        key: 'thumbnail',
        label: 'Compact thumbnail',
        width: 640,
        height: 360,
        target: 'thumbnail.jpg',
    },
]

const packageConcepts = {
    'access-gate': concept(
        'gate',
        'Protect private journeys',
        'Gate member content, downloads, and paid access with clear grants and audit trails.',
        ['Request', 'Grant', 'Audit'],
        [palette.emerald, palette.blue, palette.orange],
    ),
    address: concept(
        'map',
        'One address record',
        'Reuse structured postal data across packages instead of rebuilding location fields.',
        ['Country', 'Address', 'Reuse'],
        [palette.emerald, palette.cyan, palette.amber],
    ),
    'agent-bridge': concept(
        'agent',
        'Safe agent operations',
        'Let agents act through scoped tokens, preview gates, and reviewable audit history.',
        ['Token', 'Preview', 'Confirm'],
        [palette.violet, palette.blue, palette.emerald],
    ),
    'agent-delivery': concept(
        'agent-data',
        'Public AI delivery',
        'Serve clean public page manifests and semantic chunks without scraping or admin leakage.',
        ['Manifest', 'Chunks', 'Answer'],
        [palette.blue, palette.violet, palette.emerald],
    ),
    'ai-orchestrator': concept(
        'ai-core',
        'Govern AI capability',
        'Run provider-backed AI tools from one package-safe orchestration layer.',
        ['Provider', 'Run', 'Govern'],
        [palette.violet, palette.blue, palette.orange],
    ),
    api: concept(
        'json',
        'Published data API',
        'Expose published Capell page data as stable public JSON for trusted consumers.',
        ['Route', 'Payload', 'Cache'],
        [palette.blue, palette.emerald, palette.zinc],
    ),
    'automation-studio': concept(
        'automation',
        'Native automation rules',
        'Connect package events to rule conditions, actions, and audited outcomes.',
        ['Trigger', 'Rule', 'Action'],
        [palette.orange, palette.blue, palette.emerald],
    ),
    'block-library': concept(
        'blocks',
        'Reusable content blocks',
        'Ship typed content blocks that editors assemble and developers render safely.',
        ['Define', 'Compose', 'Render'],
        [palette.blue, palette.emerald, palette.orange],
    ),
    blog: concept(
        'article',
        'Structured publishing',
        'Publish articles, archives, tags, and related content through one reusable editorial model.',
        ['Draft', 'Schedule', 'Publish'],
        [palette.emerald, palette.blue, palette.orange],
    ),
    bookings: concept(
        'calendar',
        'Appointment workflow',
        'Turn availability, requests, confirmations, reminders, and feeds into one booking journey.',
        ['Slot', 'Request', 'Confirm'],
        [palette.emerald, palette.orange, palette.blue],
    ),
    'campaign-studio': concept(
        'campaign',
        'Campaign conversion loop',
        'Build landing variants, place CTAs, and track campaign-attributed conversions.',
        ['Variant', 'CTA', 'Funnel'],
        [palette.orange, palette.rose, palette.blue],
    ),
    comments: concept(
        'conversation',
        'Moderated discussion',
        'Add threaded public discussion with encrypted authors and per-site moderation controls.',
        ['Thread', 'Moderate', 'Publish'],
        [palette.blue, palette.emerald, palette.orange],
    ),
    contacts: concept(
        'contacts',
        'Unified CRM layer',
        'Capture contacts, organisations, leads, and activity from every Capell intake path.',
        ['Capture', 'Enrich', 'Follow up'],
        [palette.emerald, palette.blue, palette.violet],
    ),
    'content-sections': concept(
        'sections',
        'Themeable section set',
        'Give editors polished reusable sections while keeping presentation in package-owned views.',
        ['Choose', 'Configure', 'Reuse'],
        [palette.blue, palette.emerald, palette.orange],
    ),
    'customer-portal': concept(
        'portal',
        'Logged-in customer hub',
        'Unify payments, documents, registrations, support, and gated content in one dashboard.',
        ['Sign in', 'Review', 'Act'],
        [palette.blue, palette.emerald, palette.violet],
    ),
    'dashboard-reports': concept(
        'reports',
        'Content health at a glance',
        'Surface scheduled, expired, stale, and URL-less pages on the admin dashboard.',
        ['Measure', 'Spot', 'Fix'],
        [palette.blue, palette.orange, palette.emerald],
    ),
    'demo-kit': concept(
        'demo',
        'Known demo data',
        'Seed deterministic multi-site demo content so every screenshot and QA run starts clean.',
        ['Seed', 'Showcase', 'Reset'],
        [palette.violet, palette.blue, palette.emerald],
    ),
    deployments: concept(
        'deploy',
        'Reviewed extension installs',
        'Turn extension installs into reviewed repository changes instead of hidden server edits.',
        ['Select', 'Pull request', 'Deploy'],
        [palette.emerald, palette.blue, palette.orange],
    ),
    diagnostics: concept(
        'diagnostics',
        'Operations cockpit',
        'Show system health, queues, config drift, permissions, and package install status.',
        ['Probe', 'Report', 'Repair'],
        [palette.orange, palette.blue, palette.emerald],
    ),
    'document-lifecycle': concept(
        'document',
        'Versioned acceptance',
        'Publish controlled documents and record who accepted which version and when.',
        ['Version', 'Publish', 'Accept'],
        [palette.blue, palette.emerald, palette.orange],
    ),
    'email-studio': concept(
        'email',
        'Auditable email engine',
        'Manage reusable templates, delivery profiles, suppressions, and queued sends per site.',
        ['Template', 'Profile', 'Send'],
        [palette.orange, palette.blue, palette.emerald],
    ),
    events: concept(
        'events',
        'Capacity-aware events',
        'Publish recurring events with venues, RSVPs, calendar feeds, and Event schema.',
        ['Event', 'RSVP', 'Feed'],
        [palette.emerald, palette.orange, palette.blue],
    ),
    'exception-reports': concept(
        'alert',
        'Sanitized exception alerts',
        'Send useful failure context to operators without exposing unsafe details.',
        ['Catch', 'Sanitize', 'Notify'],
        [palette.rose, palette.orange, palette.zinc],
    ),
    experiments: concept(
        'experiment',
        'Server-side experiments',
        'Run weighted A/B tests with targeting, goals, and statistically gated reports.',
        ['Split', 'Measure', 'Decide'],
        [palette.violet, palette.orange, palette.blue],
    ),
    'filament-peek': concept(
        'preview',
        'Private preview links',
        'Preview unsaved page edits on the real theme through expiring signed links.',
        ['Edit', 'Preview', 'Save'],
        [palette.blue, palette.emerald, palette.orange],
    ),
    'form-builder': concept(
        'form',
        'Encrypted intake forms',
        'Build site-scoped forms and capture spam-filtered submissions into a triage inbox.',
        ['Build', 'Submit', 'Reply'],
        [palette.emerald, palette.blue, palette.orange],
    ),
    'theme-foundation': concept(
        'theme-base',
        'Base theme contract',
        'Provide layouts, tokens, assets, sections, and sanitizers every child theme builds on.',
        ['Tokens', 'Layout', 'Theme'],
        [palette.zinc, palette.blue, palette.emerald],
    ),
    'frontend-authoring': concept(
        'authoring',
        'Post-load admin authoring',
        'Let admins edit from the live site while visitors and caches see ordinary HTML.',
        ['Beacon', 'Control', 'Safe HTML'],
        [palette.blue, palette.emerald, palette.orange],
    ),
    'frontend-optimizer': concept(
        'performance',
        'Faster first paint',
        'Extract critical CSS from real pages and defer the assets visitors do not need yet.',
        ['Render', 'Extract', 'Defer'],
        [palette.emerald, palette.blue, palette.orange],
    ),
    'ga4-reports': concept(
        'analytics',
        'GA4 snapshots in admin',
        'Pull daily traffic, top-page, and conversion summaries into the Capell workspace.',
        ['Import', 'Trend', 'Review'],
        [palette.orange, palette.blue, palette.emerald],
    ),
    hero: concept(
        'hero',
        'Theme-ready hero media',
        'Give every Capell theme polished hero, carousel, video, and overlay controls.',
        ['Media', 'Slide', 'Launch'],
        [palette.blue, palette.orange, palette.emerald],
    ),
    'html-cache': concept(
        'cache',
        'Static HTML delivery',
        'Serve public pages from safe HTML cache with dependency-aware invalidation.',
        ['Render', 'Cache', 'Purge'],
        [palette.emerald, palette.blue, palette.orange],
    ),
    inertia: concept(
        'inertia',
        'Shared Inertia bridge',
        'Connect Capell frontend routes to an application-owned Inertia runtime.',
        ['Route', 'Props', 'Render'],
        [palette.blue, palette.violet, palette.emerald],
    ),
    'inertia-react-adapter': concept(
        'react',
        'React runtime adapter',
        'Mount Capell Inertia pages into React components owned by the application.',
        ['Props', 'React', 'Render'],
        [palette.cyan, palette.blue, palette.violet],
    ),
    'inertia-vue-adapter': concept(
        'vue',
        'Vue runtime adapter',
        'Mount Capell Inertia pages into Vue components owned by the application.',
        ['Props', 'Vue', 'Render'],
        [palette.emerald, palette.blue, palette.violet],
    ),
    insights: concept(
        'insights',
        'Privacy-aware analytics',
        'Track page views, clicks, journeys, and consent without third-party scripts.',
        ['Collect', 'Consent', 'Learn'],
        [palette.blue, palette.emerald, palette.orange],
    ),
    'knowledge-base': concept(
        'knowledge',
        'Self-hosted help center',
        'Organise docs into searchable collections with reader feedback and public payloads.',
        ['Organise', 'Search', 'Improve'],
        [palette.blue, palette.emerald, palette.orange],
    ),
    'layout-builder': concept(
        'layout',
        'Composed pages, clean output',
        'Let editors assemble approved widgets while developers keep markup and performance.',
        ['Area', 'Widget', 'HTML'],
        [palette.blue, palette.emerald, palette.orange],
    ),
    'login-audit': concept(
        'audit',
        'Access evidence trail',
        'Track login attempts, sessions, devices, and retention for compliance review.',
        ['Attempt', 'Session', 'Retain'],
        [palette.rose, palette.zinc, palette.blue],
    ),
    'media-ai': concept(
        'media-ai',
        'Provider-backed image edits',
        'Improve, restore, remove, and upscale media through configured AI actions.',
        ['Select', 'Edit', 'Approve'],
        [palette.violet, palette.blue, palette.emerald],
    ),
    'media-library': concept(
        'media',
        'Consistent media backbone',
        'Use one media field, health dashboard, and migration path across Capell packages.',
        ['Upload', 'Reuse', 'Audit'],
        [palette.blue, palette.emerald, palette.orange],
    ),
    'migration-assistant': concept(
        'migration',
        'Previewed imports',
        'Move pages and media into Capell with validation, previews, and rollback reports.',
        ['Map', 'Validate', 'Import'],
        [palette.orange, palette.blue, palette.emerald],
    ),
    navigation: concept(
        'navigation',
        'Managed site menus',
        'Build multilingual per-site menus with active states, cloning, and frontend rendering.',
        ['Link', 'Nest', 'Render'],
        [palette.blue, palette.emerald, palette.orange],
    ),
    newsletter: concept(
        'newsletter',
        'Consent-safe subscriptions',
        'Capture, confirm, segment, and sync subscribers with durable consent evidence.',
        ['Opt in', 'Segment', 'Sync'],
        [palette.emerald, palette.blue, palette.orange],
    ),
    notes: concept(
        'notes',
        'Context on every record',
        'Attach private notes, assignments, and mentions to admin records without losing handoffs.',
        ['Note', 'Assign', 'Resolve'],
        [palette.amber, palette.blue, palette.emerald],
    ),
    'password-policy': concept(
        'password',
        'Stronger admin passwords',
        'Enforce expiry, resets, reuse history, and breach checks from one policy screen.',
        ['Policy', 'Check', 'Reset'],
        [palette.rose, palette.zinc, palette.blue],
    ),
    payments: concept(
        'payments',
        'Checkout and fulfilment',
        'Take payments, subscriptions, donations, and gated-access handoffs through Stripe.',
        ['Checkout', 'Record', 'Fulfil'],
        [palette.emerald, palette.orange, palette.blue],
    ),
    'privacy-center': concept(
        'privacy',
        'Compliance ledger',
        'Unify consent, policy acceptance, retention rules, and data subject requests.',
        ['Consent', 'Request', 'Retain'],
        [palette.emerald, palette.blue, palette.zinc],
    ),
    'public-actions': concept(
        'webhook',
        'Signed public automation',
        'Turn public forms and API calls into signed, retried, audited webhooks.',
        ['Receive', 'Sign', 'Retry'],
        [palette.orange, palette.blue, palette.emerald],
    ),
    'publishing-studio': concept(
        'publishing',
        'Review-first publishing',
        'Edit in isolated workspaces, get sign-off, schedule, publish, and roll back.',
        ['Draft', 'Review', 'Release'],
        [palette.blue, palette.emerald, palette.orange],
    ),
    'record-switcher': concept(
        'switcher',
        'Fast record switching',
        'Jump between editable records from the page heading with keyboard-friendly search.',
        ['Search', 'Switch', 'Continue'],
        [palette.blue, palette.emerald, palette.orange],
    ),
    search: concept(
        'search',
        'Production-grade search',
        'Tune relevance, synonyms, promotions, and zero-result insights for public search.',
        ['Index', 'Query', 'Curate'],
        [palette.blue, palette.emerald, palette.orange],
    ),
    'seo-suite': concept(
        'seo',
        'Search-ready pages',
        'Score SEO, manage schema, fix links, and publish AI-discovery output in the editor.',
        ['Score', 'Structure', 'Discover'],
        [palette.emerald, palette.blue, palette.orange],
    ),
    'shopify-commerce': concept(
        'shopify',
        'Shopify catalog sync',
        'Connect stores, sync products and customers, and keep storefront ownership open.',
        ['Connect', 'Sync', 'Sell'],
        [palette.emerald, palette.orange, palette.blue],
    ),
    'site-discovery': concept(
        'sitemap',
        'Canonical discovery',
        'Generate XML sitemaps, HTML sitemaps, and the public URL registry for SEO.',
        ['Registry', 'Sitemap', 'Crawl'],
        [palette.blue, palette.emerald, palette.orange],
    ),
    'social-feeds': concept(
        'social',
        'Cached social feeds',
        'Place branded social feeds with provider support and cache-safe frontend rendering.',
        ['Provider', 'Cache', 'Display'],
        [palette.orange, palette.blue, palette.emerald],
    ),
    'structured-content-library': concept(
        'structured',
        'Reusable content records',
        'Model testimonials, case studies, team members, FAQs, services, and more once.',
        ['Model', 'Reuse', 'Render'],
        [palette.blue, palette.emerald, palette.orange],
    ),
    tags: concept(
        'tags',
        'Shared taxonomy',
        'Manage multilingual, multi-site tags for pages, articles, events, and package content.',
        ['Define', 'Attach', 'Filter'],
        [palette.emerald, palette.blue, palette.orange],
    ),
    'theme-agency': concept(
        'theme-agency',
        'Agency conversion theme',
        'Turn creative work, campaigns, proof, and lead capture into a polished site.',
        ['Show work', 'Prove', 'Convert'],
        [palette.rose, palette.orange, palette.zinc],
    ),
    'theme-commerce': concept(
        'theme-commerce',
        'Retail discovery theme',
        'Shape product discovery, lookbooks, guides, and buying paths for commerce sites.',
        ['Browse', 'Compare', 'Buy'],
        [palette.orange, palette.emerald, palette.zinc],
    ),
    'theme-corporate': concept(
        'theme-corporate',
        'Trust-led corporate theme',
        'Give B2B and public-sector sites formal hierarchy, proof, and resource paths.',
        ['Explain', 'Prove', 'Contact'],
        [palette.zinc, palette.blue, palette.emerald],
    ),
    'theme-education': concept(
        'theme-education',
        'Course-first education theme',
        'Guide learners from programme discovery to faculty trust and enrolment.',
        ['Discover', 'Trust', 'Enrol'],
        [palette.blue, palette.emerald, palette.orange],
    ),
    'theme-healthcare': concept(
        'theme-healthcare',
        'Appointment-led healthcare theme',
        'Present services, clinicians, pathways, locations, and enquiry routes with care.',
        ['Find care', 'Choose clinician', 'Enquire'],
        [palette.emerald, palette.blue, palette.orange],
    ),
    'theme-inertia-bookings': concept(
        'theme-bookings',
        'Inertia booking theme',
        'Render service booking journeys with app-owned Inertia pages and Capell content.',
        ['Service', 'Slot', 'Request'],
        [palette.emerald, palette.blue, palette.orange],
    ),
    'theme-inertia-bookings-react': concept(
        'react',
        'React booking components',
        'Ship React components for app-owned Inertia booking business themes.',
        ['React', 'Service', 'Book'],
        [palette.cyan, palette.blue, palette.emerald],
    ),
    'theme-inertia-bookings-vue': concept(
        'vue',
        'Vue booking components',
        'Ship Vue components for app-owned Inertia booking business themes.',
        ['Vue', 'Service', 'Book'],
        [palette.emerald, palette.blue, palette.violet],
    ),
    'theme-knowledge': concept(
        'theme-knowledge',
        'Documentation theme',
        'Give long-form docs sidebar navigation, table of contents, and prominent search.',
        ['Navigate', 'Read', 'Search'],
        [palette.blue, palette.emerald, palette.orange],
    ),
    'theme-local-services': concept(
        'theme-services',
        'Local service conversion',
        'Move visitors from service-area proof to quote request and click-to-call action.',
        ['Area', 'Proof', 'Quote'],
        [palette.orange, palette.emerald, palette.blue],
    ),
    'theme-nonprofit': concept(
        'theme-nonprofit',
        'Supporter journey theme',
        'Connect mission, impact proof, campaigns, donations, and volunteer action.',
        ['Mission', 'Impact', 'Support'],
        [palette.emerald, palette.orange, palette.blue],
    ),
    'theme-portfolio': concept(
        'theme-portfolio',
        'Portfolio proof theme',
        'Turn selected work into case studies, service offers, media kits, and audience growth.',
        ['Work', 'Proof', 'Enquire'],
        [palette.rose, palette.zinc, palette.orange],
    ),
    'theme-saas': concept(
        'theme-saas',
        'Product-led SaaS theme',
        'Present features, proof, pricing, docs, and demo requests for software sites.',
        ['Feature', 'Price', 'Demo'],
        [palette.blue, palette.emerald, palette.orange],
    ),
    'translation-manager': concept(
        'translation',
        'Language file workflow',
        'Edit locale keys side by side with missing, stale, override, and AI draft support.',
        ['Compare', 'Draft', 'Save'],
        [palette.violet, palette.blue, palette.emerald],
    ),
    'url-manager': concept(
        'redirect',
        'Recovered traffic',
        'Manage redirects, preserve moved page URLs, and turn repeated 404s into SEO recovery.',
        ['Detect', 'Redirect', 'Recover'],
        [palette.blue, palette.orange, palette.emerald],
    ),
    'welcome-tour': concept(
        'tour',
        'Guided editor onboarding',
        'Introduce sites, pages, media, and settings with dismissible in-product tours.',
        ['Step', 'Guide', 'Resume'],
        [palette.blue, palette.emerald, palette.orange],
    ),
    'wordpress-importer': concept(
        'wordpress',
        'WordPress WXR preview',
        'Preview WordPress posts and pages with metadata ready for Migration Assistant.',
        ['Upload', 'Preview', 'Map'],
        [palette.blue, palette.orange, palette.zinc],
    ),
}

const packageNames = collectPackageNames(process.argv.slice(2))
const outputManifest = {
    generatedFor: 'capell-marketplace-responsive-artwork',
    variants: variants.map(({ key, label, width, height }) => ({
        key,
        label,
        width,
        height,
    })),
    packages: [],
}

try {
    for (const packageName of packageNames) {
        const packagePath = path.join(packagesRoot, packageName)
        const manifestPath = path.join(packagePath, 'capell.json')

        if (!fs.existsSync(manifestPath)) {
            continue
        }

        const manifest = JSON.parse(fs.readFileSync(manifestPath, 'utf8'))
        const assetsPath = path.join(packagePath, 'docs/assets/marketplace')
        const screenshots = collectScreenshots(packagePath, manifest)
        const screenshotData = screenshots
            .slice(0, 3)
            .map((screenshotPath, index) =>
                createScreenshotData(packageName, screenshotPath, index),
            )
        const conceptData = resolvePackageConcept(packageName, manifest)
        const packageAssets = []

        fs.mkdirSync(assetsPath, { recursive: true })

        for (const variant of variants) {
            const targetPath = resolveTargetPath(packagePath, manifest, variant)
            const svg = buildArtworkSvg({
                packageName,
                manifest,
                conceptData,
                screenshotData,
                variant,
            })

            if (targetPath.endsWith('.svg')) {
                fs.writeFileSync(targetPath, svg)
            } else {
                renderJpeg(svg, packageName, variant, targetPath)
            }

            packageAssets.push({
                variant: variant.key,
                path: path
                    .relative(packagePath, targetPath)
                    .split(path.sep)
                    .join('/'),
                width: variant.width,
                height: variant.height,
            })

            console.log(path.relative(root, targetPath))
        }

        outputManifest.packages.push({
            package: packageName,
            displayName: manifest.displayName ?? titleFromSlug(packageName),
            concept: conceptData.headline,
            assets: packageAssets,
        })
    }

    fs.writeFileSync(
        outputManifestPath,
        `${JSON.stringify(outputManifest, null, 4)}\n`,
    )
} finally {
    fs.rmSync(tempRoot, { recursive: true, force: true })
}

function concept(icon, headline, summary, steps, colors) {
    return { icon, headline, summary, steps, colors }
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

function resolveTargetPath(packagePath, manifest, variant) {
    const assetsPath = path.join(packagePath, 'docs/assets/marketplace')

    if (variant.target !== 'extension-card') {
        return path.join(assetsPath, variant.target)
    }

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
        const files = fs
            .readdirSync(screenshotDirectory)
            .filter((fileName) => fileName.endsWith('.png'))
            .sort(
                (left, right) =>
                    scoreScreenshotName(left) - scoreScreenshotName(right),
            )

        for (const fileName of files) {
            candidates.push(path.join(screenshotDirectory, fileName))
        }
    }

    return [...new Set(candidates)].filter((screenshotPath) =>
        fs.existsSync(screenshotPath),
    )
}

function scoreScreenshotName(fileName) {
    let score = 0

    if (fileName.includes('dark')) {
        score += 20
    }

    if (fileName.includes('frontend') || fileName.includes('public')) {
        score -= 8
    }

    if (fileName.includes('admin') || fileName.includes('settings')) {
        score -= 4
    }

    return score
}

function createScreenshotData(packageName, screenshotPath, index) {
    const targetPath = path.join(tempRoot, `${packageName}-${index}.jpg`)

    execFileSync('magick', [
        screenshotPath,
        '-resize',
        '1024x640^',
        '-gravity',
        'center',
        '-extent',
        '1024x640',
        '-strip',
        '-quality',
        '82',
        targetPath,
    ])

    return {
        dataHref: `data:image/jpeg;base64,${fs.readFileSync(targetPath).toString('base64')}`,
    }
}

function resolvePackageConcept(packageName, manifest) {
    const fallback = concept(
        'package',
        `${manifest.displayName ?? titleFromSlug(packageName)} package`,
        manifest.marketplace?.summary ??
            manifest.description ??
            'Installable Capell capability for long-lived Laravel sites.',
        ['Install', 'Use', 'Evolve'],
        [palette.blue, palette.emerald, palette.orange],
    )

    return packageConcepts[packageName] ?? fallback
}

function renderJpeg(svg, packageName, variant, targetPath) {
    const sourcePath = path.join(tempRoot, `${packageName}-${variant.key}.svg`)
    const pngPath = path.join(tempRoot, `${packageName}-${variant.key}.png`)
    const jpgPath = path.join(tempRoot, `${packageName}-${variant.key}.jpg`)

    fs.writeFileSync(sourcePath, svg)
    execFileSync(
        'sips',
        ['-s', 'format', 'png', sourcePath, '--out', pngPath],
        {
            stdio: ['ignore', 'ignore', 'pipe'],
        },
    )
    execFileSync('magick', [
        pngPath,
        '-strip',
        '-sampling-factor',
        '4:2:0',
        '-quality',
        variant.key === 'thumbnail' ? '78' : '84',
        jpgPath,
    ])
    fs.renameSync(jpgPath, targetPath)
}

function buildArtworkSvg({
    packageName,
    manifest,
    conceptData,
    screenshotData,
    variant,
}) {
    const title = manifest.displayName ?? titleFromSlug(packageName)
    const bundle = String(manifest.product?.bundle ?? 'capell')
    const group = String(manifest.product?.group ?? 'Capell Marketplace')
    const width = variant.width
    const height = variant.height
    const vertical = height > width
    const square = width === height
    const compact = variant.key === 'thumbnail'
    const colors = conceptData.colors
    const patternId = `grid-${packageName}-${variant.key}`
    const scene = vertical
        ? buildVerticalScene(
              width,
              height,
              title,
              bundle,
              group,
              conceptData,
              screenshotData,
              colors,
              patternId,
          )
        : square
          ? buildSquareScene(
                width,
                height,
                title,
                bundle,
                group,
                conceptData,
                screenshotData,
                colors,
                patternId,
            )
          : compact
            ? buildThumbnailScene(
                  width,
                  height,
                  title,
                  bundle,
                  conceptData,
                  screenshotData,
                  colors,
                  patternId,
              )
            : buildLandscapeScene(
                  width,
                  height,
                  title,
                  bundle,
                  group,
                  conceptData,
                  screenshotData,
                  colors,
                  patternId,
              )

    return `<svg xmlns="http://www.w3.org/2000/svg" width="${width}" height="${height}" viewBox="0 0 ${width} ${height}" role="img" aria-labelledby="title desc">
  <title id="title">${escapeXml(title)} marketplace artwork</title>
  <desc id="desc">${escapeXml(conceptData.summary)}</desc>
  <defs>
    <pattern id="${patternId}" width="28" height="28" patternUnits="userSpaceOnUse">
      <path d="M28 0H0V28" fill="none" stroke="${palette.line}" stroke-width="1" opacity="0.42"/>
      <circle cx="4" cy="4" r="1.4" fill="${palette.muted}" opacity="0.35"/>
    </pattern>
  </defs>
  ${scene}
</svg>
`
}

function buildLandscapeScene(
    width,
    height,
    title,
    bundle,
    group,
    conceptData,
    screenshots,
    colors,
    patternId,
) {
    const textWidth = Math.round(width * 0.36)
    const artX = textWidth + 88
    const artWidth = width - artX - 64
    const screenshot = screenshots[0] ?? null
    const secondScreenshot = screenshots[1] ?? null

    return `${background(width, height, colors, patternId)}
  <g transform="translate(64 64)">
    ${label(bundle.toUpperCase(), 0, 0, colors[0])}
    ${headlineText(title, 0, 58, Math.min(textWidth, 520), 58)}
    ${bodyText(conceptData.summary, 0, 142, Math.min(textWidth, 520), 2, 27)}
    ${stepRail(conceptData.steps, 0, height - 190, Math.min(textWidth, 520), colors)}
  </g>
  <g transform="translate(${artX} 64)">
    ${showcaseFrame(0, 28, Math.round(artWidth * 0.62), Math.round(height * 0.48), screenshot, colors[0], conceptData)}
    ${showcaseFrame(Math.round(artWidth * 0.45), Math.round(height * 0.22), Math.round(artWidth * 0.44), Math.round(height * 0.36), secondScreenshot, colors[1], conceptData, true)}
    ${domainMotif(conceptData.icon, Math.round(artWidth * 0.08), Math.round(height * 0.48), Math.round(artWidth * 0.55), Math.round(height * 0.28), colors)}
    ${proofStack(Math.round(artWidth * 0.68), Math.round(height * 0.58), Math.round(artWidth * 0.25), colors)}
  </g>
  <text x="${width - 64}" y="${height - 44}" text-anchor="end" fill="${palette.slate}" font-family="Helvetica, Arial, sans-serif" font-size="18" font-weight="700">${escapeXml(group.toUpperCase())}</text>`
}

function buildVerticalScene(
    width,
    height,
    title,
    bundle,
    group,
    conceptData,
    screenshots,
    colors,
    patternId,
) {
    const screenshot = screenshots[0] ?? null
    const secondScreenshot = screenshots[1] ?? null

    return `${background(width, height, colors, patternId)}
  <g transform="translate(56 64)">
    ${label(bundle.toUpperCase(), 0, 0, colors[0])}
    ${headlineText(title, 0, 60, width - 112, 62)}
    ${bodyText(conceptData.summary, 0, 154, width - 112, 3, 27)}
  </g>
  <g transform="translate(56 292)">
    ${showcaseFrame(0, 0, width - 112, 300, screenshot, colors[0], conceptData)}
    ${domainMotif(conceptData.icon, 58, 360, width - 116, 270, colors)}
    ${showcaseFrame(width - 360, 498, 304, 196, secondScreenshot, colors[1], conceptData, true)}
  </g>
  ${stepRail(conceptData.steps, 56, height - 182, width - 112, colors)}
  <text x="${width - 56}" y="${height - 48}" text-anchor="end" fill="${palette.slate}" font-family="Helvetica, Arial, sans-serif" font-size="17" font-weight="700">${escapeXml(group.toUpperCase())}</text>`
}

function buildSquareScene(
    width,
    height,
    title,
    bundle,
    group,
    conceptData,
    screenshots,
    colors,
    patternId,
) {
    const screenshot = screenshots[0] ?? null
    const secondScreenshot = screenshots[1] ?? null

    return `${background(width, height, colors, patternId)}
  <g transform="translate(70 70)">
    ${label(bundle.toUpperCase(), 0, 0, colors[0])}
    ${headlineText(title, 0, 64, width - 140, 62)}
    ${bodyText(conceptData.summary, 0, 152, width - 140, 2, 28)}
  </g>
  <g transform="translate(76 292)">
    ${showcaseFrame(0, 0, 474, 306, screenshot, colors[0], conceptData)}
    ${showcaseFrame(548, 84, 500, 322, secondScreenshot, colors[1], conceptData, true)}
    ${domainMotif(conceptData.icon, 220, 430, 720, 330, colors)}
  </g>
  ${stepRail(conceptData.steps, 76, height - 170, width - 152, colors)}
  <text x="${width - 76}" y="${height - 42}" text-anchor="end" fill="${palette.slate}" font-family="Helvetica, Arial, sans-serif" font-size="17" font-weight="700">${escapeXml(group.toUpperCase())}</text>`
}

function buildThumbnailScene(
    width,
    height,
    title,
    bundle,
    conceptData,
    screenshots,
    colors,
    patternId,
) {
    const screenshot = screenshots[0] ?? null

    return `${background(width, height, colors, patternId)}
  <g transform="translate(34 34)">
    ${label(bundle.toUpperCase(), 0, 0, colors[0], 12)}
    ${headlineText(title, 0, 42, 250, 31)}
    ${bodyText(conceptData.headline, 0, 88, 254, 2, 15)}
  </g>
  <g transform="translate(318 34)">
    ${showcaseFrame(0, 0, 278, 176, screenshot, colors[0], conceptData)}
    ${domainMotif(conceptData.icon, 18, 188, 244, 104, colors)}
  </g>
  ${compactSteps(conceptData.steps, 34, height - 52, colors)}`
}

function background(width, height, colors, patternId) {
    return `<rect width="${width}" height="${height}" fill="${palette.white}"/>
  <rect width="${width}" height="${height}" fill="url(#${patternId})"/>
  <path d="M${width * 0.54} 0H${width}V${height}H${width * 0.68}C${width * 0.54} ${height * 0.72} ${width * 0.48} ${height * 0.36} ${width * 0.54} 0Z" fill="${colors[0]}" opacity="0.08"/>
  <path d="M0 ${height * 0.82}C${width * 0.24} ${height * 0.72} ${width * 0.42} ${height * 0.94} ${width} ${height * 0.78}V${height}H0Z" fill="${colors[1]}" opacity="0.08"/>`
}

function label(text, x, y, color, size = 18) {
    return `<text x="${x}" y="${y}" fill="${color}" font-family="Helvetica, Arial, sans-serif" font-size="${size}" font-weight="800" letter-spacing="0">${escapeXml(text)}</text>`
}

function headlineText(text, x, y, maxWidth, size) {
    const lines = wrapText(
        text,
        Math.max(12, Math.floor(maxWidth / (size * 0.52))),
        2,
    )

    return lines
        .map(
            (line, index) =>
                `<text x="${x}" y="${y + index * size * 1.04}" fill="${palette.ink}" font-family="Helvetica, Arial, sans-serif" font-size="${size}" font-weight="800" letter-spacing="0">${escapeXml(line)}</text>`,
        )
        .join('\n    ')
}

function bodyText(text, x, y, maxWidth, maxLines, size) {
    const lines = wrapText(
        text,
        Math.max(20, Math.floor(maxWidth / (size * 0.48))),
        maxLines,
    )

    return lines
        .map(
            (line, index) =>
                `<text x="${x}" y="${y + index * size * 1.32}" fill="${palette.slate}" font-family="Helvetica, Arial, sans-serif" font-size="${size}" letter-spacing="0">${escapeXml(line)}</text>`,
        )
        .join('\n    ')
}

function stepRail(steps, x, y, width, colors) {
    const columnWidth = width / 3

    return `<g transform="translate(${x} ${y})">
    <line x1="0" y1="34" x2="${width}" y2="34" stroke="${palette.ink}" stroke-width="4"/>
    ${steps
        .map((step, index) => {
            const stepX = index * columnWidth
            const color = colors[index % colors.length]

            return `<g transform="translate(${stepX} 0)">
      <rect x="0" y="0" width="${Math.max(42, columnWidth - 22)}" height="68" fill="${palette.white}" stroke="${palette.ink}" stroke-width="3"/>
      <rect x="12" y="-10" width="28" height="28" fill="${color}" stroke="${palette.ink}" stroke-width="3"/>
      <text x="54" y="28" fill="${palette.ink}" font-family="Helvetica, Arial, sans-serif" font-size="20" font-weight="800">${index + 1}</text>
      <text x="12" y="54" fill="${palette.slate}" font-family="Helvetica, Arial, sans-serif" font-size="16" font-weight="700">${escapeXml(step.toUpperCase())}</text>
    </g>`
        })
        .join('\n    ')}
  </g>`
}

function compactSteps(steps, x, y, colors) {
    return `<g transform="translate(${x} ${y})">
    ${steps
        .map(
            (step, index) => `<g transform="translate(${index * 112} 0)">
      <rect x="0" y="-18" width="18" height="18" fill="${colors[index % colors.length]}"/>
      <text x="26" y="-4" fill="${palette.slate}" font-family="Helvetica, Arial, sans-serif" font-size="12" font-weight="800">${escapeXml(step.toUpperCase())}</text>
    </g>`,
        )
        .join('\n    ')}
  </g>`
}

function showcaseFrame(
    x,
    y,
    width,
    height,
    screenshot,
    accent,
    conceptData,
    small = false,
) {
    const framePad = Math.max(14, Math.round(width * 0.035))
    const innerX = x + framePad
    const innerY = y + framePad
    const innerW = width - framePad * 2
    const innerH = height - framePad * 2
    const content =
        screenshot === null
            ? mockCapellScreen(
                  innerX,
                  innerY,
                  innerW,
                  innerH,
                  accent,
                  conceptData,
              )
            : `<image href="${escapeXml(screenshot.dataHref)}" x="${innerX}" y="${innerY}" width="${innerW}" height="${innerH}" preserveAspectRatio="xMidYMid slice"/>`

    return `<g>
    <rect x="${x}" y="${y}" width="${width}" height="${height}" fill="${palette.ink}"/>
    <rect x="${innerX}" y="${innerY}" width="${innerW}" height="${innerH}" fill="${palette.paper}"/>
    ${content}
    <rect x="${x + width - 82}" y="${y + height - 42}" width="${small ? 58 : 74}" height="${small ? 30 : 38}" fill="${accent}" stroke="${palette.ink}" stroke-width="3"/>
  </g>`
}

function mockCapellScreen(x, y, width, height, accent, conceptData) {
    const rowWidth = Math.round(width * 0.55)
    const panelW = Math.round(width * 0.26)

    return `<g>
    <rect x="${x}" y="${y}" width="${width}" height="${height}" fill="${palette.paper}"/>
    <rect x="${x + 24}" y="${y + 26}" width="${width - 48}" height="24" fill="${accent}"/>
    <rect x="${x + 24}" y="${y + 74}" width="${rowWidth}" height="18" fill="${palette.slate}"/>
    <rect x="${x + 24}" y="${y + 114}" width="${width - 96}" height="14" fill="${palette.line}"/>
    <rect x="${x + 24}" y="${y + 148}" width="${width - 134}" height="14" fill="${palette.line}"/>
    <rect x="${x + width - panelW - 24}" y="${y + 74}" width="${panelW}" height="${height - 118}" fill="${palette.white}" stroke="${palette.ink}" stroke-width="3"/>
    ${smallIcon(conceptData.icon, x + width - panelW, y + height - 74, 44, accent)}
  </g>`
}

function proofStack(x, y, width, colors) {
    return `<g transform="translate(${x} ${y})">
    <rect x="0" y="0" width="${width}" height="36" fill="${colors[0]}" stroke="${palette.ink}" stroke-width="3"/>
    <rect x="18" y="52" width="${width * 0.82}" height="36" fill="${colors[1]}" stroke="${palette.ink}" stroke-width="3"/>
    <rect x="36" y="104" width="${width * 0.68}" height="36" fill="${colors[2]}" stroke="${palette.ink}" stroke-width="3"/>
  </g>`
}

function domainMotif(icon, x, y, width, height, colors) {
    const left = x
    const top = y
    const unit = Math.min(width, height)
    const main = colors[0]
    const secondary = colors[1]
    const tertiary = colors[2]

    return `<g transform="translate(${left} ${top})">
    <rect x="0" y="${height * 0.72}" width="${width}" height="8" fill="${palette.ink}"/>
    <polygon points="${width * 0.52},${height * 0.02} ${width * 0.94},${height * 0.26} ${width * 0.94},${height * 0.74} ${width * 0.52},${height * 0.98} ${width * 0.1},${height * 0.74} ${width * 0.1},${height * 0.26}" fill="${main}" stroke="${palette.ink}" stroke-width="7"/>
    <circle cx="${width * 0.28}" cy="${height * 0.32}" r="${unit * 0.045}" fill="${palette.white}" stroke="${palette.ink}" stroke-width="4"/>
    <circle cx="${width * 0.72}" cy="${height * 0.36}" r="${unit * 0.045}" fill="${secondary}" stroke="${palette.ink}" stroke-width="4"/>
    <circle cx="${width * 0.48}" cy="${height * 0.7}" r="${unit * 0.045}" fill="${palette.white}" stroke="${palette.ink}" stroke-width="4"/>
    <path d="M${width * 0.28} ${height * 0.32} L${width * 0.72} ${height * 0.36} L${width * 0.48} ${height * 0.7} Z" fill="none" stroke="${palette.white}" stroke-width="4"/>
    <rect x="${width * 0.38}" y="${height * 0.36}" width="${width * 0.3}" height="${height * 0.2}" fill="${palette.white}" stroke="${palette.ink}" stroke-width="5"/>
    ${smallIcon(icon, width * 0.43, height * 0.39, unit * 0.18, tertiary)}
  </g>`
}

function smallIcon(icon, x, y, size, color) {
    const stroke = palette.ink
    const center = size / 2

    switch (icon) {
        case 'gate':
            return `<g transform="translate(${x} ${y})"><rect width="${size}" height="${size}" fill="${color}" stroke="${stroke}" stroke-width="4"/><path d="M${size * 0.28} ${size}V${size * 0.38}C${size * 0.28} ${size * 0.12} ${size * 0.72} ${size * 0.12} ${size * 0.72} ${size * 0.38}V${size}" fill="none" stroke="${palette.white}" stroke-width="5"/></g>`
        case 'map':
            return `<g transform="translate(${x} ${y})"><path d="M${center} ${size * 0.92}C${size * 0.18} ${size * 0.54} ${size * 0.14} ${size * 0.12} ${center} ${size * 0.1}C${size * 0.86} ${size * 0.12} ${size * 0.82} ${size * 0.54} ${center} ${size * 0.92}Z" fill="${color}" stroke="${stroke}" stroke-width="4"/><circle cx="${center}" cy="${size * 0.4}" r="${size * 0.15}" fill="${palette.white}" stroke="${stroke}" stroke-width="3"/></g>`
        case 'agent':
        case 'agent-data':
        case 'ai-core':
        case 'media-ai':
            return networkIcon(x, y, size, color)
        case 'json':
        case 'inertia':
        case 'react':
        case 'vue':
            return `<g transform="translate(${x} ${y})"><path d="M${size * 0.35} ${size * 0.18}L${size * 0.14} ${center}L${size * 0.35} ${size * 0.82}" fill="none" stroke="${color}" stroke-width="6"/><path d="M${size * 0.65} ${size * 0.18}L${size * 0.86} ${center}L${size * 0.65} ${size * 0.82}" fill="none" stroke="${color}" stroke-width="6"/><line x1="${size * 0.48}" y1="${size * 0.86}" x2="${size * 0.58}" y2="${size * 0.14}" stroke="${stroke}" stroke-width="4"/></g>`
        case 'automation':
        case 'webhook':
            return flowIcon(x, y, size, color)
        case 'article':
        case 'document':
        case 'publishing':
        case 'wordpress':
            return documentIcon(x, y, size, color)
        case 'calendar':
        case 'events':
        case 'theme-bookings':
            return calendarIcon(x, y, size, color)
        case 'campaign':
            return megaphoneIcon(x, y, size, color)
        case 'conversation':
        case 'social':
            return chatIcon(x, y, size, color)
        case 'contacts':
            return peopleIcon(x, y, size, color)
        case 'reports':
        case 'analytics':
        case 'insights':
        case 'seo':
            return chartIcon(x, y, size, color)
        case 'deploy':
        case 'migration':
            return pipelineIcon(x, y, size, color)
        case 'diagnostics':
        case 'alert':
            return pulseIcon(x, y, size, color)
        case 'email':
        case 'newsletter':
            return mailIcon(x, y, size, color)
        case 'experiment':
            return splitIcon(x, y, size, color)
        case 'preview':
        case 'authoring':
            return eyeIcon(x, y, size, color)
        case 'form':
            return formIcon(x, y, size, color)
        case 'performance':
        case 'cache':
            return boltIcon(x, y, size, color)
        case 'media':
        case 'hero':
            return imageIcon(x, y, size, color)
        case 'knowledge':
            return bookIcon(x, y, size, color)
        case 'layout':
        case 'blocks':
        case 'sections':
        case 'structured':
            return gridIcon(x, y, size, color)
        case 'audit':
        case 'password':
        case 'privacy':
            return shieldIcon(x, y, size, color)
        case 'payments':
        case 'shopify':
        case 'theme-commerce':
            return cardIcon(x, y, size, color)
        case 'navigation':
        case 'sitemap':
            return sitemapIcon(x, y, size, color)
        case 'notes':
        case 'tour':
            return noteIcon(x, y, size, color)
        case 'search':
        case 'redirect':
            return searchIcon(x, y, size, color)
        case 'tags':
            return tagIcon(x, y, size, color)
        case 'translation':
            return translateIcon(x, y, size, color)
        case 'theme-agency':
        case 'theme-corporate':
        case 'theme-education':
        case 'theme-healthcare':
        case 'theme-knowledge':
        case 'theme-local-services':
        case 'theme-nonprofit':
        case 'theme-portfolio':
        case 'theme-saas':
        case 'theme-base':
            return themeIcon(x, y, size, color)
        default:
            return packageIcon(x, y, size, color)
    }
}

function networkIcon(x, y, size, color) {
    return `<g transform="translate(${x} ${y})"><line x1="${size * 0.22}" y1="${size * 0.28}" x2="${size * 0.72}" y2="${size * 0.2}" stroke="${color}" stroke-width="5"/><line x1="${size * 0.72}" y1="${size * 0.2}" x2="${size * 0.55}" y2="${size * 0.76}" stroke="${color}" stroke-width="5"/><line x1="${size * 0.55}" y1="${size * 0.76}" x2="${size * 0.22}" y2="${size * 0.28}" stroke="${color}" stroke-width="5"/><circle cx="${size * 0.22}" cy="${size * 0.28}" r="${size * 0.1}" fill="${palette.white}" stroke="${palette.ink}" stroke-width="4"/><circle cx="${size * 0.72}" cy="${size * 0.2}" r="${size * 0.1}" fill="${color}" stroke="${palette.ink}" stroke-width="4"/><circle cx="${size * 0.55}" cy="${size * 0.76}" r="${size * 0.1}" fill="${palette.white}" stroke="${palette.ink}" stroke-width="4"/></g>`
}

function flowIcon(x, y, size, color) {
    return `<g transform="translate(${x} ${y})"><rect x="0" y="${size * 0.1}" width="${size * 0.34}" height="${size * 0.26}" fill="${color}" stroke="${palette.ink}" stroke-width="4"/><rect x="${size * 0.66}" y="${size * 0.1}" width="${size * 0.34}" height="${size * 0.26}" fill="${palette.white}" stroke="${palette.ink}" stroke-width="4"/><rect x="${size * 0.33}" y="${size * 0.64}" width="${size * 0.34}" height="${size * 0.26}" fill="${color}" stroke="${palette.ink}" stroke-width="4"/><path d="M${size * 0.34} ${size * 0.23}H${size * 0.66}M${size * 0.5} ${size * 0.36}V${size * 0.64}" stroke="${palette.ink}" stroke-width="5"/></g>`
}

function documentIcon(x, y, size, color) {
    return `<g transform="translate(${x} ${y})"><path d="M${size * 0.18} 0H${size * 0.68}L${size * 0.86} ${size * 0.2}V${size}H${size * 0.18}Z" fill="${palette.white}" stroke="${palette.ink}" stroke-width="4"/><path d="M${size * 0.68} 0V${size * 0.2}H${size * 0.86}" fill="none" stroke="${palette.ink}" stroke-width="4"/><line x1="${size * 0.3}" y1="${size * 0.42}" x2="${size * 0.74}" y2="${size * 0.42}" stroke="${color}" stroke-width="5"/><line x1="${size * 0.3}" y1="${size * 0.62}" x2="${size * 0.66}" y2="${size * 0.62}" stroke="${palette.slate}" stroke-width="4"/></g>`
}

function calendarIcon(x, y, size, color) {
    return `<g transform="translate(${x} ${y})"><rect x="${size * 0.08}" y="${size * 0.16}" width="${size * 0.84}" height="${size * 0.74}" fill="${palette.white}" stroke="${palette.ink}" stroke-width="4"/><rect x="${size * 0.08}" y="${size * 0.16}" width="${size * 0.84}" height="${size * 0.22}" fill="${color}" stroke="${palette.ink}" stroke-width="4"/><rect x="${size * 0.24}" y="${size * 0.5}" width="${size * 0.14}" height="${size * 0.14}" fill="${palette.ink}"/><rect x="${size * 0.48}" y="${size * 0.5}" width="${size * 0.14}" height="${size * 0.14}" fill="${color}"/></g>`
}

function megaphoneIcon(x, y, size, color) {
    return `<g transform="translate(${x} ${y})"><path d="M${size * 0.1} ${size * 0.52}L${size * 0.72} ${size * 0.18}V${size * 0.82}Z" fill="${color}" stroke="${palette.ink}" stroke-width="4"/><rect x="${size * 0.08}" y="${size * 0.42}" width="${size * 0.24}" height="${size * 0.24}" fill="${palette.white}" stroke="${palette.ink}" stroke-width="4"/><path d="M${size * 0.34} ${size * 0.64}L${size * 0.48} ${size * 0.96}" stroke="${palette.ink}" stroke-width="5"/></g>`
}

function chatIcon(x, y, size, color) {
    return `<g transform="translate(${x} ${y})"><rect x="${size * 0.06}" y="${size * 0.12}" width="${size * 0.76}" height="${size * 0.54}" fill="${palette.white}" stroke="${palette.ink}" stroke-width="4"/><path d="M${size * 0.32} ${size * 0.66}L${size * 0.2} ${size * 0.9}L${size * 0.52} ${size * 0.66}" fill="${color}" stroke="${palette.ink}" stroke-width="4"/><circle cx="${size * 0.28}" cy="${size * 0.38}" r="${size * 0.06}" fill="${color}"/><circle cx="${size * 0.48}" cy="${size * 0.38}" r="${size * 0.06}" fill="${color}"/></g>`
}

function peopleIcon(x, y, size, color) {
    return `<g transform="translate(${x} ${y})"><circle cx="${size * 0.38}" cy="${size * 0.28}" r="${size * 0.16}" fill="${color}" stroke="${palette.ink}" stroke-width="4"/><circle cx="${size * 0.68}" cy="${size * 0.34}" r="${size * 0.12}" fill="${palette.white}" stroke="${palette.ink}" stroke-width="4"/><path d="M${size * 0.1} ${size * 0.9}C${size * 0.14} ${size * 0.56} ${size * 0.62} ${size * 0.56} ${size * 0.68} ${size * 0.9}" fill="${palette.white}" stroke="${palette.ink}" stroke-width="4"/></g>`
}

function chartIcon(x, y, size, color) {
    return `<g transform="translate(${x} ${y})"><rect x="${size * 0.12}" y="${size * 0.62}" width="${size * 0.14}" height="${size * 0.28}" fill="${color}" stroke="${palette.ink}" stroke-width="3"/><rect x="${size * 0.42}" y="${size * 0.42}" width="${size * 0.14}" height="${size * 0.48}" fill="${palette.white}" stroke="${palette.ink}" stroke-width="3"/><rect x="${size * 0.72}" y="${size * 0.18}" width="${size * 0.14}" height="${size * 0.72}" fill="${color}" stroke="${palette.ink}" stroke-width="3"/><path d="M${size * 0.08} ${size * 0.32}L${size * 0.4} ${size * 0.46}L${size * 0.74} ${size * 0.2}" fill="none" stroke="${palette.ink}" stroke-width="5"/></g>`
}

function pipelineIcon(x, y, size, color) {
    return `<g transform="translate(${x} ${y})"><rect x="${size * 0.06}" y="${size * 0.16}" width="${size * 0.26}" height="${size * 0.26}" fill="${color}" stroke="${palette.ink}" stroke-width="4"/><rect x="${size * 0.38}" y="${size * 0.58}" width="${size * 0.26}" height="${size * 0.26}" fill="${palette.white}" stroke="${palette.ink}" stroke-width="4"/><rect x="${size * 0.7}" y="${size * 0.16}" width="${size * 0.26}" height="${size * 0.26}" fill="${color}" stroke="${palette.ink}" stroke-width="4"/><path d="M${size * 0.32} ${size * 0.29}C${size * 0.46} ${size * 0.28} ${size * 0.44} ${size * 0.71} ${size * 0.38} ${size * 0.71}M${size * 0.64} ${size * 0.71}C${size * 0.74} ${size * 0.7} ${size * 0.68} ${size * 0.3} ${size * 0.7} ${size * 0.29}" fill="none" stroke="${palette.ink}" stroke-width="5"/></g>`
}

function pulseIcon(x, y, size, color) {
    return `<g transform="translate(${x} ${y})"><path d="M0 ${size * 0.58}H${size * 0.22}L${size * 0.34} ${size * 0.24}L${size * 0.5} ${size * 0.82}L${size * 0.62} ${size * 0.46}H${size}" fill="none" stroke="${color}" stroke-width="7"/><circle cx="${size * 0.82}" cy="${size * 0.24}" r="${size * 0.13}" fill="${palette.white}" stroke="${palette.ink}" stroke-width="4"/></g>`
}

function mailIcon(x, y, size, color) {
    return `<g transform="translate(${x} ${y})"><rect x="${size * 0.08}" y="${size * 0.2}" width="${size * 0.84}" height="${size * 0.62}" fill="${palette.white}" stroke="${palette.ink}" stroke-width="4"/><path d="M${size * 0.08} ${size * 0.24}L${size * 0.5} ${size * 0.56}L${size * 0.92} ${size * 0.24}" fill="none" stroke="${color}" stroke-width="5"/></g>`
}

function splitIcon(x, y, size, color) {
    return `<g transform="translate(${x} ${y})"><circle cx="${size * 0.18}" cy="${size * 0.5}" r="${size * 0.1}" fill="${palette.white}" stroke="${palette.ink}" stroke-width="4"/><circle cx="${size * 0.82}" cy="${size * 0.22}" r="${size * 0.1}" fill="${color}" stroke="${palette.ink}" stroke-width="4"/><circle cx="${size * 0.82}" cy="${size * 0.78}" r="${size * 0.1}" fill="${color}" stroke="${palette.ink}" stroke-width="4"/><path d="M${size * 0.28} ${size * 0.5}C${size * 0.5} ${size * 0.5} ${size * 0.54} ${size * 0.22} ${size * 0.72} ${size * 0.22}M${size * 0.28} ${size * 0.5}C${size * 0.5} ${size * 0.5} ${size * 0.54} ${size * 0.78} ${size * 0.72} ${size * 0.78}" fill="none" stroke="${palette.ink}" stroke-width="5"/></g>`
}

function eyeIcon(x, y, size, color) {
    return `<g transform="translate(${x} ${y})"><path d="M${size * 0.08} ${size * 0.5}C${size * 0.28} ${size * 0.18} ${size * 0.72} ${size * 0.18} ${size * 0.92} ${size * 0.5}C${size * 0.72} ${size * 0.82} ${size * 0.28} ${size * 0.82} ${size * 0.08} ${size * 0.5}Z" fill="${palette.white}" stroke="${palette.ink}" stroke-width="4"/><circle cx="${size * 0.5}" cy="${size * 0.5}" r="${size * 0.16}" fill="${color}" stroke="${palette.ink}" stroke-width="4"/></g>`
}

function formIcon(x, y, size, color) {
    return `<g transform="translate(${x} ${y})"><rect x="${size * 0.12}" y="${size * 0.06}" width="${size * 0.76}" height="${size * 0.88}" fill="${palette.white}" stroke="${palette.ink}" stroke-width="4"/><line x1="${size * 0.26}" y1="${size * 0.3}" x2="${size * 0.74}" y2="${size * 0.3}" stroke="${color}" stroke-width="6"/><line x1="${size * 0.26}" y1="${size * 0.5}" x2="${size * 0.68}" y2="${size * 0.5}" stroke="${palette.slate}" stroke-width="5"/><rect x="${size * 0.26}" y="${size * 0.66}" width="${size * 0.28}" height="${size * 0.14}" fill="${color}"/></g>`
}

function boltIcon(x, y, size, color) {
    return `<g transform="translate(${x} ${y})"><path d="M${size * 0.56} 0L${size * 0.14} ${size * 0.56}H${size * 0.48}L${size * 0.34} ${size}L${size * 0.88} ${size * 0.38}H${size * 0.54}Z" fill="${color}" stroke="${palette.ink}" stroke-width="4"/></g>`
}

function imageIcon(x, y, size, color) {
    return `<g transform="translate(${x} ${y})"><rect x="${size * 0.08}" y="${size * 0.14}" width="${size * 0.84}" height="${size * 0.72}" fill="${palette.white}" stroke="${palette.ink}" stroke-width="4"/><circle cx="${size * 0.32}" cy="${size * 0.36}" r="${size * 0.1}" fill="${color}"/><path d="M${size * 0.16} ${size * 0.78}L${size * 0.42} ${size * 0.54}L${size * 0.56} ${size * 0.66}L${size * 0.78} ${size * 0.46}L${size * 0.88} ${size * 0.78}" fill="none" stroke="${palette.ink}" stroke-width="5"/></g>`
}

function bookIcon(x, y, size, color) {
    return `<g transform="translate(${x} ${y})"><path d="M${size * 0.12} ${size * 0.12}H${size * 0.48}C${size * 0.58} ${size * 0.12} ${size * 0.58} ${size * 0.24} ${size * 0.58} ${size * 0.24}V${size * 0.9}C${size * 0.58} ${size * 0.8} ${size * 0.48} ${size * 0.76} ${size * 0.38} ${size * 0.76}H${size * 0.12}Z" fill="${palette.white}" stroke="${palette.ink}" stroke-width="4"/><path d="M${size * 0.58} ${size * 0.24}C${size * 0.62} ${size * 0.16} ${size * 0.7} ${size * 0.12} ${size * 0.84} ${size * 0.12}V${size * 0.76}H${size * 0.7}C${size * 0.62} ${size * 0.76} ${size * 0.58} ${size * 0.82} ${size * 0.58} ${size * 0.9}Z" fill="${color}" stroke="${palette.ink}" stroke-width="4"/></g>`
}

function gridIcon(x, y, size, color) {
    return `<g transform="translate(${x} ${y})"><rect x="0" y="0" width="${size * 0.42}" height="${size * 0.42}" fill="${color}" stroke="${palette.ink}" stroke-width="4"/><rect x="${size * 0.56}" y="0" width="${size * 0.42}" height="${size * 0.42}" fill="${palette.white}" stroke="${palette.ink}" stroke-width="4"/><rect x="0" y="${size * 0.56}" width="${size * 0.42}" height="${size * 0.42}" fill="${palette.white}" stroke="${palette.ink}" stroke-width="4"/><rect x="${size * 0.56}" y="${size * 0.56}" width="${size * 0.42}" height="${size * 0.42}" fill="${color}" stroke="${palette.ink}" stroke-width="4"/></g>`
}

function shieldIcon(x, y, size, color) {
    return `<g transform="translate(${x} ${y})"><path d="M${size * 0.5} 0L${size * 0.9} ${size * 0.18}V${size * 0.52}C${size * 0.9} ${size * 0.76} ${size * 0.68} ${size * 0.92} ${size * 0.5} ${size}C${size * 0.32} ${size * 0.92} ${size * 0.1} ${size * 0.76} ${size * 0.1} ${size * 0.52}V${size * 0.18}Z" fill="${color}" stroke="${palette.ink}" stroke-width="4"/><path d="M${size * 0.32} ${size * 0.5}L${size * 0.45} ${size * 0.63}L${size * 0.72} ${size * 0.34}" fill="none" stroke="${palette.white}" stroke-width="6"/></g>`
}

function cardIcon(x, y, size, color) {
    return `<g transform="translate(${x} ${y})"><rect x="${size * 0.06}" y="${size * 0.24}" width="${size * 0.88}" height="${size * 0.56}" fill="${palette.white}" stroke="${palette.ink}" stroke-width="4"/><rect x="${size * 0.06}" y="${size * 0.36}" width="${size * 0.88}" height="${size * 0.12}" fill="${color}"/><rect x="${size * 0.18}" y="${size * 0.62}" width="${size * 0.24}" height="${size * 0.08}" fill="${palette.ink}"/></g>`
}

function sitemapIcon(x, y, size, color) {
    return `<g transform="translate(${x} ${y})"><rect x="${size * 0.38}" y="0" width="${size * 0.24}" height="${size * 0.2}" fill="${color}" stroke="${palette.ink}" stroke-width="4"/><rect x="0" y="${size * 0.68}" width="${size * 0.24}" height="${size * 0.2}" fill="${palette.white}" stroke="${palette.ink}" stroke-width="4"/><rect x="${size * 0.38}" y="${size * 0.68}" width="${size * 0.24}" height="${size * 0.2}" fill="${color}" stroke="${palette.ink}" stroke-width="4"/><rect x="${size * 0.76}" y="${size * 0.68}" width="${size * 0.24}" height="${size * 0.2}" fill="${palette.white}" stroke="${palette.ink}" stroke-width="4"/><path d="M${size * 0.5} ${size * 0.2}V${size * 0.5}H${size * 0.12}V${size * 0.68}M${size * 0.5} ${size * 0.5}V${size * 0.68}M${size * 0.5} ${size * 0.5}H${size * 0.88}V${size * 0.68}" fill="none" stroke="${palette.ink}" stroke-width="4"/></g>`
}

function noteIcon(x, y, size, color) {
    return `<g transform="translate(${x} ${y})"><rect x="${size * 0.1}" y="${size * 0.08}" width="${size * 0.8}" height="${size * 0.78}" fill="${palette.white}" stroke="${palette.ink}" stroke-width="4"/><path d="M${size * 0.62} ${size * 0.86}L${size * 0.9} ${size * 0.58}" fill="${color}" stroke="${palette.ink}" stroke-width="4"/><line x1="${size * 0.24}" y1="${size * 0.3}" x2="${size * 0.72}" y2="${size * 0.3}" stroke="${color}" stroke-width="5"/><line x1="${size * 0.24}" y1="${size * 0.5}" x2="${size * 0.62}" y2="${size * 0.5}" stroke="${palette.slate}" stroke-width="4"/></g>`
}

function searchIcon(x, y, size, color) {
    return `<g transform="translate(${x} ${y})"><circle cx="${size * 0.42}" cy="${size * 0.42}" r="${size * 0.28}" fill="${palette.white}" stroke="${palette.ink}" stroke-width="5"/><line x1="${size * 0.62}" y1="${size * 0.62}" x2="${size * 0.9}" y2="${size * 0.9}" stroke="${color}" stroke-width="8"/></g>`
}

function tagIcon(x, y, size, color) {
    return `<g transform="translate(${x} ${y})"><path d="M0 ${size * 0.12}H${size * 0.62}L${size} ${size * 0.5}L${size * 0.62} ${size * 0.88}H0Z" fill="${color}" stroke="${palette.ink}" stroke-width="4"/><circle cx="${size * 0.24}" cy="${size * 0.5}" r="${size * 0.09}" fill="${palette.white}" stroke="${palette.ink}" stroke-width="3"/></g>`
}

function translateIcon(x, y, size, color) {
    return `<g transform="translate(${x} ${y})"><rect x="0" y="${size * 0.14}" width="${size * 0.46}" height="${size * 0.54}" fill="${palette.white}" stroke="${palette.ink}" stroke-width="4"/><rect x="${size * 0.38}" y="${size * 0.32}" width="${size * 0.56}" height="${size * 0.54}" fill="${color}" stroke="${palette.ink}" stroke-width="4"/><text x="${size * 0.13}" y="${size * 0.52}" fill="${palette.ink}" font-family="Helvetica, Arial, sans-serif" font-size="${size * 0.34}" font-weight="800">A</text><text x="${size * 0.56}" y="${size * 0.7}" fill="${palette.white}" font-family="Helvetica, Arial, sans-serif" font-size="${size * 0.3}" font-weight="800">文</text></g>`
}

function themeIcon(x, y, size, color) {
    return `<g transform="translate(${x} ${y})"><rect x="${size * 0.08}" y="${size * 0.08}" width="${size * 0.84}" height="${size * 0.84}" fill="${palette.white}" stroke="${palette.ink}" stroke-width="4"/><rect x="${size * 0.2}" y="${size * 0.2}" width="${size * 0.56}" height="${size * 0.16}" fill="${color}"/><rect x="${size * 0.2}" y="${size * 0.48}" width="${size * 0.34}" height="${size * 0.12}" fill="${palette.slate}"/><rect x="${size * 0.62}" y="${size * 0.48}" width="${size * 0.14}" height="${size * 0.28}" fill="${color}"/></g>`
}

function packageIcon(x, y, size, color) {
    return `<g transform="translate(${x} ${y})"><rect x="${size * 0.16}" y="${size * 0.16}" width="${size * 0.68}" height="${size * 0.68}" fill="${color}" stroke="${palette.ink}" stroke-width="4"/><path d="M${size * 0.34} ${size * 0.16}V${size * 0.84}M${size * 0.66} ${size * 0.16}V${size * 0.84}M${size * 0.16} ${size * 0.34}H${size * 0.84}M${size * 0.16} ${size * 0.66}H${size * 0.84}" stroke="${palette.white}" stroke-width="4"/></g>`
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

function titleFromSlug(slug) {
    return slug
        .split('-')
        .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
        .join(' ')
}

function escapeXml(value) {
    return String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
}
