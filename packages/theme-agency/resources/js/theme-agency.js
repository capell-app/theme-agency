const initializedProofCarousels = new WeakSet()

const prefersReducedMotion = () =>
    window.matchMedia('(prefers-reduced-motion: reduce)').matches

const setButtonState = (button, disabled) => {
    button.classList.toggle('hidden', disabled)
    button.setAttribute('aria-disabled', disabled ? 'true' : 'false')
}

const updateProofCarousel = (track, previousButton, nextButton, status) => {
    const canScroll = track.scrollWidth > track.clientWidth + 1
    const atStart = track.scrollLeft <= 2
    const atEnd = track.scrollLeft >= track.scrollWidth - track.clientWidth - 2

    setButtonState(previousButton, !canScroll || atStart)
    setButtonState(nextButton, !canScroll || atEnd)

    if (status) {
        status.textContent = canScroll
            ? status.getAttribute('data-carousel-scrollable-label')
            : status.getAttribute('data-carousel-static-label')
    }
}

const initializeProofCarousel = (carousel) => {
    if (initializedProofCarousels.has(carousel)) {
        return
    }

    const track = carousel.querySelector('[data-carousel-track]')
    const previousButton = carousel.querySelector('[data-carousel-prev]')
    const nextButton = carousel.querySelector('[data-carousel-next]')
    const status = carousel.querySelector('[data-carousel-status]')

    if (!track || !previousButton || !nextButton) {
        return
    }

    initializedProofCarousels.add(carousel)

    const step = () => Math.max(260, Math.floor(track.clientWidth * 0.82))

    const scroll = (direction) => {
        track.scrollBy({
            left: step() * direction,
            behavior: prefersReducedMotion() ? 'auto' : 'smooth',
        })
    }

    previousButton.addEventListener('click', () => scroll(-1))
    nextButton.addEventListener('click', () => scroll(1))

    track.addEventListener(
        'scroll',
        () => updateProofCarousel(track, previousButton, nextButton, status),
        { passive: true },
    )

    window.addEventListener('resize', () =>
        updateProofCarousel(track, previousButton, nextButton, status),
    )

    updateProofCarousel(track, previousButton, nextButton, status)
}

const initializeAgencyTheme = () => {
    document
        .querySelectorAll('[data-carousel="proof"]')
        .forEach(initializeProofCarousel)
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeAgencyTheme, {
        once: true,
    })
} else {
    initializeAgencyTheme()
}
