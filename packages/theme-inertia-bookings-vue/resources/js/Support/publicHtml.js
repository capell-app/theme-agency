const blockedAttributePatterns = [
    /^wire:/i,
    /^data-(capell|field|model|permission)/i,
    /^(field_path|model_id|permission)$/i,
]

const blockedTextPatterns = [
    /capell-app\/[a-z0-9-]+/gi,
    /data-capell-authoring/gi,
    /signed[-_]editor/gi,
    /field_path/gi,
    /model_id/gi,
    /permission/gi,
]

function isSafePublicUrl(value) {
    const candidate = String(value).trim()

    if (candidate === '' || /^[\u0000-\u001F\u007F]/.test(candidate)) {
        return false
    }

    if (candidate.startsWith('#')) {
        return /^#[A-Za-z][A-Za-z0-9_-]*$/.test(candidate)
    }

    if (candidate.startsWith('//')) {
        return false
    }

    try {
        const url = new URL(candidate, 'https://capell.invalid')
        const pathSegments = url.pathname
            .toLowerCase()
            .split('/')
            .filter((segment) => segment !== '')

        return (
            ['http:', 'https:'].includes(url.protocol) &&
            !pathSegments.some((segment) =>
                ['admin', 'filament', 'livewire'].includes(segment),
            ) &&
            !url.searchParams.has('signature') &&
            !url.searchParams.has('signed') &&
            !url.searchParams.has('_token')
        )
    } catch {
        return false
    }
}

function stripBlockedText(value) {
    return blockedTextPatterns.reduce(
        (html, pattern) => html.replace(pattern, ''),
        value,
    )
}

export function sanitizePublicHtml(value) {
    if (typeof value !== 'string' || value.trim() === '') {
        return ''
    }

    if (typeof document === 'undefined') {
        return stripBlockedText(value)
            .replace(
                /\s(?:href|src|action)=["'](?:javascript:[^"']*|[^"']*[?&](?:signature|signed|_token)=[^"']*)["']/gi,
                '',
            )
            .replace(
                /\s(?:wire:[\w.-]+|data-(?:capell|field|model|permission)[\w:-]*|field_path|model_id|permission)=["'][^"']*["']/gi,
                '',
            )
    }

    const template = document.createElement('template')
    template.innerHTML = value

    template.content.querySelectorAll('*').forEach((element) => {
        Array.from(element.attributes).forEach((attribute) => {
            if (
                blockedAttributePatterns.some((pattern) =>
                    pattern.test(attribute.name),
                )
            ) {
                element.removeAttribute(attribute.name)
                return
            }

            if (
                ['href', 'src', 'action'].includes(
                    attribute.name.toLowerCase(),
                ) &&
                !isSafePublicUrl(attribute.value)
            ) {
                element.removeAttribute(attribute.name)
            }
        })
    })

    return stripBlockedText(template.innerHTML)
}
