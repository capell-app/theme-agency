import fs from 'node:fs'
import path from 'node:path'
import { chromium } from 'playwright'

const [, , manifestPath, resultPath] = process.argv

if (!manifestPath || !resultPath) {
    console.error(
        'Usage: node scripts/capture-theme-demo-layout-screenshots.mjs <manifest.json> <result.json>',
    )
    process.exit(1)
}

const manifest = JSON.parse(fs.readFileSync(manifestPath, 'utf8'))
const screenshotCss = fs.readFileSync(manifest.cssPath, 'utf8')
const browser = await chromium.launch()
const manifestEntries = manifest.entries ?? []
const entries = new Array(manifestEntries.length)
const concurrency = manifest.concurrency ?? 2
let nextEntryIndex = 0

await Promise.all(
    Array.from({ length: Math.min(concurrency, manifestEntries.length) }).map(
        async () => {
            while (nextEntryIndex < manifestEntries.length) {
                const entryIndex = nextEntryIndex
                nextEntryIndex += 1
                entries[entryIndex] = await captureEntry(
                    manifestEntries[entryIndex],
                )
            }
        },
    ),
)

await browser.close()
await fs.promises.mkdir(path.dirname(resultPath), { recursive: true })
fs.writeFileSync(resultPath, JSON.stringify({ entries }, null, 2))

async function captureEntry(entry) {
    const html = withScreenshotStyles(fs.readFileSync(entry.htmlPath, 'utf8'))
    const viewport = entry.viewport ??
        manifest.viewport ?? { width: 1440, height: 1100 }
    const page = await browser.newPage({ viewport })

    await page.setContent(html, { waitUntil: 'load' })
    await page.waitForFunction(() => document.styleSheets.length > 0, null, {
        timeout: 1000,
    })
    await page.evaluate(async () => {
        await Promise.all(
            Array.from(document.images).map(async (image) => {
                if (image.complete && image.naturalWidth > 0) {
                    return
                }

                try {
                    await image.decode()
                } catch {
                    // The result metadata records failed image decodes.
                }
            }),
        )
    })

    await fs.promises.mkdir(path.dirname(entry.screenshotPath), {
        recursive: true,
    })
    await page.screenshot({
        path: entry.screenshotPath,
        fullPage: true,
        animations: 'disabled',
    })

    const imageState = await page.evaluate(() =>
        Array.from(document.images).map((image) => ({
            src: image.currentSrc || image.src,
            loaded:
                image.complete &&
                image.naturalWidth > 0 &&
                image.naturalHeight > 0,
            width: image.naturalWidth,
            height: image.naturalHeight,
        })),
    )
    const blank = await page.evaluate(() => {
        const body = document.body
        const textLength = body?.innerText?.trim().length ?? 0
        const paintedElements = document.querySelectorAll('body *').length

        return textLength === 0 && paintedElements === 0
    })

    await page.close()

    return {
        surface: entry.surface,
        type: entry.type,
        layout: entry.layout,
        screenshotPath: entry.screenshotPath,
        imageCount: imageState.length,
        loadedImageCount: imageState.filter((image) => image.loaded).length,
        blank,
    }
}

function withScreenshotStyles(html) {
    const screenshotHead = `
<base href="https://demo.test/">
<style id="theme-demo-screenshot-tailwind">
${screenshotCss}
</style>`

    if (html.includes('</head>')) {
        return html.replace('</head>', `${screenshotHead}\n</head>`)
    }

    return `<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    ${screenshotHead}
</head>
<body>
${html}
</body>
</html>`
}
