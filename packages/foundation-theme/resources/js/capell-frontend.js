import AlpineFloatingUI from '@awcodes/alpine-floating-ui'
import Tooltip from '@ryangjchandler/alpine-tooltip'

import './utilities/lightbox'
import './blocks/block/carousel'

const inactiveFaqTabClasses = [
    'border',
    'border-stone-200',
    'bg-white',
    'text-gray-600',
]

const setFaqTabState = (tab, isActive) => {
    tab.classList.toggle('bg-stone-800', isActive)
    tab.classList.toggle('text-white', isActive)
    tab.classList.toggle('border-transparent', isActive)

    inactiveFaqTabClasses.forEach((className) => {
        tab.classList.toggle(className, !isActive)
    })
}

const filterFaqCategory = (button) => {
    const category = button.dataset.category
    const section = button.closest('.capell-modern-faq-section') ?? document

    section.querySelectorAll('[data-faq-category-tab]').forEach((tab) => {
        setFaqTabState(tab, tab.dataset.category === category)
    })

    section.querySelectorAll('.faq-item').forEach((item) => {
        const matches = category === 'all' || item.dataset.category === category

        item.hidden = !matches
        item.classList.toggle('is-visible', matches)
    })
}

const updatePricingPlan = (plan, billing) => {
    const priceBlock = plan.querySelector('.plan-price')
    const periodBlock = plan.querySelector('.billing-period')
    const price =
        billing === 'annual'
            ? plan.dataset.priceAnnual
            : plan.dataset.priceMonthly

    if (!priceBlock || !price) {
        return
    }

    priceBlock.textContent = priceBlock.textContent.replace(
        billing === 'annual'
            ? plan.dataset.priceMonthly
            : plan.dataset.priceAnnual,
        price,
    )

    if (periodBlock && price !== 'Custom') {
        periodBlock.textContent = billing === 'annual' ? '/year' : '/month'
    }
}

const toggleBillingCycle = (button) => {
    const section = button.closest('.capell-modern-pricing-table') ?? document
    const grid = section.querySelector('.pricing-grid')

    if (!grid) {
        return
    }

    const billing = grid.dataset.billing === 'monthly' ? 'annual' : 'monthly'

    grid.dataset.billing = billing
    button.classList.toggle('is-annual', billing === 'annual')
    grid.querySelectorAll('.pricing-plan').forEach((plan) =>
        updatePricingPlan(plan, billing),
    )
}

const updateCarouselDots = (carousel, activeIndex) => {
    carousel.querySelectorAll('.carousel-dot').forEach((dot, index) => {
        const isActive = index === activeIndex

        dot.classList.toggle('is-active', isActive)
        dot.classList.toggle('bg-stone-900', isActive)
        dot.classList.toggle('bg-stone-300', !isActive)
    })
}

const goToCarouselSlide = (carousel, slideIndex) => {
    const container = carousel.querySelector('.carousel-container')

    if (!container) {
        return
    }

    container.style.transform = `translateX(${-slideIndex * 100}%)`
    updateCarouselDots(carousel, slideIndex)
}

const slideCarousel = (button) => {
    const carousel = button.closest('.layout-builder-testimonials-carousel')
    const container = carousel?.querySelector('.carousel-container')
    const slides = carousel?.querySelectorAll('.carousel-slide') ?? []

    if (!carousel || !container || slides.length === 0) {
        return
    }

    const currentOffset =
        parseInt(
            container.style.transform?.replace('translateX(', '') ?? '0',
        ) || 0
    const currentIndex = Math.round(-currentOffset / 100)
    const direction = Number(button.dataset.carouselDirection ?? 0)
    const nextIndex = (currentIndex + direction + slides.length) % slides.length

    goToCarouselSlide(carousel, nextIndex)
}

document.addEventListener('click', (event) => {
    const faqButton = event.target.closest('[data-faq-category-tab]')

    if (faqButton) {
        filterFaqCategory(faqButton)

        return
    }

    const billingButton = event.target.closest('[data-billing-toggle]')

    if (billingButton) {
        toggleBillingCycle(billingButton)

        return
    }

    const carouselButton = event.target.closest('[data-carousel-direction]')

    if (carouselButton) {
        slideCarousel(carouselButton)

        return
    }

    const carouselDot = event.target.closest('[data-carousel-slide]')

    if (carouselDot) {
        const carousel = carouselDot.closest(
            '.layout-builder-testimonials-carousel',
        )

        if (carousel) {
            goToCarouselSlide(
                carousel,
                Number(carouselDot.dataset.carouselSlide ?? 0),
            )
        }
    }
})

document.addEventListener('alpine:init', () => {
    window.Alpine.plugin(Tooltip)
    window.Alpine.plugin(AlpineFloatingUI)
})
