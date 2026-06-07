const initializedCarousels = new WeakSet()

const updateCarouselButtons = (track, previousButton, nextButton) => {
    const canScroll = track.scrollWidth > track.clientWidth + 1

    previousButton.classList.toggle(
        'hidden',
        !canScroll || track.scrollLeft <= 2,
    )

    nextButton.classList.toggle(
        'hidden',
        !canScroll ||
            track.scrollLeft >= track.scrollWidth - track.clientWidth - 2,
    )
}

const initializeProofCarousel = (carousel) => {
    if (initializedCarousels.has(carousel)) {
        return
    }

    const track = carousel.querySelector('[data-carousel-track]')
    const previousButton = carousel.querySelector('[data-carousel-prev]')
    const nextButton = carousel.querySelector('[data-carousel-next]')

    if (!track || !previousButton || !nextButton) {
        return
    }

    initializedCarousels.add(carousel)

    const step = () => Math.max(280, Math.floor(track.clientWidth * 0.82))

    previousButton.addEventListener('click', () => {
        track.scrollBy({ left: -step(), behavior: 'smooth' })
    })

    nextButton.addEventListener('click', () => {
        track.scrollBy({ left: step(), behavior: 'smooth' })
    })

    track.addEventListener(
        'scroll',
        () => updateCarouselButtons(track, previousButton, nextButton),
        { passive: true },
    )

    window.addEventListener('resize', () =>
        updateCarouselButtons(track, previousButton, nextButton),
    )

    updateCarouselButtons(track, previousButton, nextButton)
}

const showGalleryImage = (imageUrl) => {
    const lightbox = document.querySelector('[data-gallery-lightbox]')
    const imageFrame = document.querySelector('[data-gallery-image-frame]')

    if (!lightbox || !imageFrame || !imageUrl) {
        return
    }

    let lightboxImage = imageFrame.querySelector('img')

    if (!lightboxImage) {
        lightboxImage = document.createElement('img')
        lightboxImage.alt = ''
        lightboxImage.className =
            'max-h-[85vh] w-full max-w-4xl rounded-xl border border-white/20 object-contain'
        imageFrame.appendChild(lightboxImage)
    }

    lightboxImage.src = imageUrl
    lightbox.classList.remove('hidden')
    lightbox.classList.add('flex')
}

const hideGalleryImage = () => {
    const lightbox = document.querySelector('[data-gallery-lightbox]')

    lightbox?.classList.add('hidden')
    lightbox?.classList.remove('flex')
}

const initializeCorporateTheme = () => {
    document
        .querySelectorAll('[data-carousel="proof"]')
        .forEach(initializeProofCarousel)

    document.querySelectorAll('[data-gallery-open]').forEach((trigger) => {
        trigger.addEventListener('click', () => {
            showGalleryImage(trigger.getAttribute('data-gallery-open'))
        })
    })

    document
        .querySelector('[data-gallery-close]')
        ?.addEventListener('click', hideGalleryImage)

    document
        .querySelector('[data-gallery-lightbox]')
        ?.addEventListener('click', (event) => {
            if (event.target === event.currentTarget) {
                hideGalleryImage()
            }
        })

    window.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            hideGalleryImage()
        }
    })
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeCorporateTheme, {
        once: true,
    })
} else {
    initializeCorporateTheme()
}
