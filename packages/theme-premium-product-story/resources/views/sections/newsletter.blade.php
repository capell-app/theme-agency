<section
    class="product-section"
    style="background: var(--product-field)"
>
    <div class="product-section-inner product-split">
        <div>
            <p class="product-kicker">
                {{ __('capell-theme-premium-product-story::sections.newsletter.kicker') }}
            </p>
            <h2>
                {{ data_get($section, 'heading', __('capell-theme-premium-product-story::sections.newsletter.heading')) }}
            </h2>
            <p class="product-lede">
                {{ data_get($section, 'summary', __('capell-theme-premium-product-story::sections.newsletter.summary')) }}
            </p>
        </div>
        <form
            method="post"
            action="{{ data_get($section, 'action', '/') }}"
            class="product-card"
        >
            <label for="product-newsletter-email">
                {{ __('capell-theme-premium-product-story::sections.newsletter.email_label') }}
            </label>
            <input
                id="product-newsletter-email"
                name="email"
                type="email"
                required
            />
            <button
                class="product-button"
                type="submit"
            >
                {{ __('capell-theme-premium-product-story::sections.newsletter.button') }}
            </button>
        </form>
    </div>
</section>
