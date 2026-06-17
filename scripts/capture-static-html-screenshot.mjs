import fs from 'node:fs'
import path from 'node:path'
import { chromium } from 'playwright'

const [, , htmlPath, screenshotPath, widthValue, heightValue, clickSelector] =
    process.argv

if (!htmlPath || !screenshotPath || !widthValue || !heightValue) {
    throw new Error(
        'Usage: node scripts/capture-static-html-screenshot.mjs <html-path> <screenshot-path> <width> <height> [click-selector]',
    )
}

const browser = await chromium.launch()
const page = await browser.newPage({
    viewport: {
        width: Number.parseInt(widthValue, 10),
        height: Number.parseInt(heightValue, 10),
    },
})

await page.setContent(fs.readFileSync(htmlPath, 'utf8'), {
    waitUntil: 'load',
})

if (clickSelector) {
    await page.click(clickSelector)
}

await fs.promises.mkdir(path.dirname(screenshotPath), { recursive: true })
await page.screenshot({
    path: screenshotPath,
    fullPage: true,
    animations: 'disabled',
})

await browser.close()
