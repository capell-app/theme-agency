import fs from 'node:fs/promises'
import { chromium } from 'playwright'

const [payloadPath, outputPath] = process.argv.slice(2)

if (!payloadPath || !outputPath) {
    console.error(
        'Usage: node generate-critical-css.mjs <payload-json> <output-css>',
    )

    throw new Error('Missing required critical CSS generator arguments.')
}

const payload = JSON.parse(await fs.readFile(payloadPath, 'utf8'))
const viewports =
    Array.isArray(payload.viewports) && payload.viewports.length > 0
        ? payload.viewports
        : [{ width: 1440, height: 900 }]
const eligibleStylesheetPaths = Array.isArray(payload.eligible_stylesheet_paths)
    ? payload.eligible_stylesheet_paths.filter(
          (path) => typeof path === 'string' && path.length > 0,
      )
    : []
const renderOptions =
    payload.render_options &&
    typeof payload.render_options === 'object' &&
    !Array.isArray(payload.render_options)
        ? payload.render_options
        : {}
const waitStrategy = ['load', 'domcontentloaded', 'networkidle'].includes(
    renderOptions.wait_strategy,
)
    ? renderOptions.wait_strategy
    : 'networkidle'
const foldMultiplier =
    typeof renderOptions.fold_multiplier === 'number' &&
    renderOptions.fold_multiplier > 0
        ? renderOptions.fold_multiplier
        : 1
const extraFoldPixels =
    Number.isInteger(renderOptions.extra_fold_pixels) &&
    renderOptions.extra_fold_pixels > 0
        ? renderOptions.extra_fold_pixels
        : 0
const maxInlineCssBytes =
    Number.isInteger(payload.max_inline_css_bytes) &&
    payload.max_inline_css_bytes > 0
        ? payload.max_inline_css_bytes
        : null

const browser = await chromium.launch()
const criticalRules = new Set()

try {
    for (const viewport of viewports) {
        const page = await browser.newPage({ viewport })
        await page.goto(payload.url, { waitUntil: waitStrategy })

        const rules = await page.evaluate(
            (options) => {
                const { extraFoldPixels, foldMultiplier, stylesheetPaths } =
                    options
                const collectedRules = []
                const themeDeclarations = new Map()
                const viewportHeight =
                    (window.innerHeight ||
                        document.documentElement.clientHeight) *
                        foldMultiplier +
                    extraFoldPixels
                const viewportWidth =
                    window.innerWidth || document.documentElement.clientWidth

                const normalizePath = (path) => {
                    try {
                        return new URL(path, window.location.href).pathname
                    } catch {
                        return path
                    }
                }

                const eligiblePaths = stylesheetPaths.map(normalizePath)

                const stylesheetIsEligible = (stylesheet) => {
                    if (
                        stylesheet.ownerNode instanceof HTMLStyleElement &&
                        stylesheet.ownerNode.matches('style[data-critical-css]')
                    ) {
                        return false
                    }

                    if (eligiblePaths.length === 0) {
                        return true
                    }

                    if (!stylesheet.href) {
                        return true
                    }

                    const stylesheetPath = normalizePath(stylesheet.href)

                    return eligiblePaths.some(
                        (path) =>
                            stylesheetPath === path ||
                            stylesheetPath.endsWith(path),
                    )
                }

                const elementIsAboveFold = (element) => {
                    const rectangle = element.getBoundingClientRect()

                    return (
                        rectangle.width > 0 &&
                        rectangle.height > 0 &&
                        rectangle.top < viewportHeight &&
                        rectangle.bottom > 0 &&
                        rectangle.left < viewportWidth * 2 &&
                        rectangle.right > viewportWidth * -1
                    )
                }

                const selectorIsAboveFold = (selectorText) => {
                    for (const selector of selectorText.split(',')) {
                        const normalizedSelector = selector.trim()

                        if (
                            normalizedSelector === '' ||
                            normalizedSelector.includes('::')
                        ) {
                            continue
                        }

                        try {
                            const elements = Array.from(
                                document.querySelectorAll(normalizedSelector),
                            )

                            if (
                                elements.length > 0 &&
                                /(contents|hidden|invisible|translate|fixed|absolute)/.test(
                                    normalizedSelector,
                                )
                            ) {
                                return true
                            }

                            if (
                                [':root', 'html', 'body'].includes(
                                    normalizedSelector,
                                )
                            ) {
                                return true
                            }

                            for (const element of elements) {
                                if (elementIsAboveFold(element)) {
                                    return true
                                }
                            }
                        } catch {
                            continue
                        }
                    }

                    return false
                }

                const collectThemeDeclarations = (rule) => {
                    for (const property of Array.from(rule.style ?? [])) {
                        if (!property.startsWith('--')) {
                            continue
                        }

                        themeDeclarations.set(
                            property,
                            rule.style.getPropertyValue(property),
                        )
                    }
                }

                const collectNestedRules = (rules, context = {}) => {
                    const nestedRules = []

                    for (const nestedRule of Array.from(rules ?? [])) {
                        nestedRules.push(...collectRule(nestedRule, context))
                    }

                    return nestedRules
                }

                const collectRule = (rule, context = {}) => {
                    if (rule instanceof CSSStyleRule) {
                        if (context.layerName === 'theme') {
                            collectThemeDeclarations(rule)

                            return []
                        }

                        if (selectorIsAboveFold(rule.selectorText)) {
                            return [rule.cssText]
                        }

                        return []
                    }

                    if (
                        rule instanceof CSSFontFaceRule ||
                        rule instanceof CSSKeyframesRule
                    ) {
                        return [rule.cssText]
                    }

                    if (
                        typeof CSSLayerBlockRule !== 'undefined' &&
                        rule instanceof CSSLayerBlockRule
                    ) {
                        const layerName = rule.name

                        const nestedRules = collectNestedRules(rule.cssRules, {
                            ...context,
                            layerName,
                        })

                        if (nestedRules.length === 0) {
                            return []
                        }

                        if (layerName === 'theme') {
                            return []
                        }

                        return nestedRules.map((nestedRule) =>
                            layerName === ''
                                ? `@layer { ${nestedRule} }`
                                : `@layer ${layerName} { ${nestedRule} }`,
                        )
                    }

                    if (rule instanceof CSSMediaRule) {
                        if (!window.matchMedia(rule.conditionText).matches) {
                            return []
                        }

                        const nestedRules = collectNestedRules(
                            rule.cssRules,
                            context,
                        )

                        if (nestedRules.length > 0) {
                            return [
                                `@media ${rule.conditionText} { ${nestedRules.join(' ')} }`,
                            ]
                        }

                        return []
                    }

                    if (rule instanceof CSSSupportsRule) {
                        const nestedRules = collectNestedRules(
                            rule.cssRules,
                            context,
                        )

                        if (nestedRules.length > 0) {
                            return [
                                `@supports ${rule.conditionText} { ${nestedRules.join(' ')} }`,
                            ]
                        }

                        return []
                    }

                    if (rule.cssRules) {
                        return collectNestedRules(rule.cssRules, context)
                    }

                    return []
                }

                for (const stylesheet of Array.from(document.styleSheets)) {
                    if (!stylesheetIsEligible(stylesheet)) {
                        continue
                    }

                    try {
                        for (const rule of Array.from(
                            stylesheet.cssRules ?? [],
                        )) {
                            collectedRules.push(...collectRule(rule))
                        }
                    } catch {
                        // Cross-origin stylesheets cannot be inspected by the browser.
                    }
                }

                const usedThemeVariables = new Set()
                const findUsedThemeVariables = (text) => {
                    for (const match of text.matchAll(/var\((--[^),\s]+)/g)) {
                        usedThemeVariables.add(match[1])
                    }
                }

                for (const rule of collectedRules) {
                    findUsedThemeVariables(rule)
                }

                let previousSize = -1

                while (previousSize !== usedThemeVariables.size) {
                    previousSize = usedThemeVariables.size

                    for (const variable of Array.from(usedThemeVariables)) {
                        const value = themeDeclarations.get(variable)

                        if (typeof value === 'string') {
                            findUsedThemeVariables(value)
                        }
                    }
                }

                const themeRule = Array.from(usedThemeVariables)
                    .filter((variable) => themeDeclarations.has(variable))
                    .map(
                        (variable) =>
                            `${variable}: ${themeDeclarations.get(variable)};`,
                    )
                    .join(' ')

                return themeRule === ''
                    ? collectedRules
                    : [`:root { ${themeRule} }`, ...collectedRules]
            },
            {
                extraFoldPixels,
                foldMultiplier,
                stylesheetPaths: eligibleStylesheetPaths,
            },
        )

        for (const rule of rules) {
            criticalRules.add(rule)
        }

        await page.close()
    }
} finally {
    await browser.close()
}

await fs.mkdir(new URL('.', `file://${outputPath}`).pathname, {
    recursive: true,
})
const outputRules = []
let outputBytes = 0

for (const rule of Array.from(criticalRules)) {
    const ruleBytes = Buffer.byteLength(`${rule}\n`, 'utf8')

    if (
        maxInlineCssBytes !== null &&
        outputRules.length > 0 &&
        outputBytes + ruleBytes > maxInlineCssBytes
    ) {
        continue
    }

    outputRules.push(rule)
    outputBytes += ruleBytes
}

const output = outputRules.join('\n') + '\n'

await fs.writeFile(outputPath, output)
