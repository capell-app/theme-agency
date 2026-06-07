const initializedCarousels = new WeakSet()

const prefersReducedMotion = () =>
    window.matchMedia('(prefers-reduced-motion: reduce)').matches

const setCarouselButtonState = (button, disabled) => {
    button.setAttribute('aria-disabled', disabled ? 'true' : 'false')
    button.classList.toggle('opacity-40', disabled)
    button.classList.toggle('pointer-events-none', disabled)
}

const updateCarousel = (track, previousButton, nextButton, status) => {
    const canScroll = track.scrollWidth > track.clientWidth + 1
    const atStart = track.scrollLeft <= 2
    const atEnd = track.scrollLeft >= track.scrollWidth - track.clientWidth - 2

    setCarouselButtonState(previousButton, !canScroll || atStart)
    setCarouselButtonState(nextButton, !canScroll || atEnd)

    if (status) {
        status.textContent = canScroll
            ? status.getAttribute('data-carousel-scrollable-label')
            : status.getAttribute('data-carousel-static-label')
    }
}

const initializeCarousel = (carousel) => {
    if (initializedCarousels.has(carousel)) {
        return
    }

    const track = carousel.querySelector('[data-carousel-track]')
    const previousButton = carousel.querySelector('[data-carousel-prev]')
    const nextButton = carousel.querySelector('[data-carousel-next]')
    const status = carousel.querySelector('[data-carousel-status]')

    if (!track || !previousButton || !nextButton) {
        return
    }

    initializedCarousels.add(carousel)

    const step = () => Math.max(240, Math.floor(track.clientWidth * 0.82))

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
        () => updateCarousel(track, previousButton, nextButton, status),
        { passive: true },
    )

    window.addEventListener('resize', () =>
        updateCarousel(track, previousButton, nextButton, status),
    )

    updateCarousel(track, previousButton, nextButton, status)
}

const initializeCommerceTheme = () => {
    document.querySelectorAll('[data-carousel]').forEach(initializeCarousel)
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeCommerceTheme, {
        once: true,
    })
} else {
    initializeCommerceTheme()
}
