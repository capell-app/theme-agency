{{--
    lightbox-carousel-viewer: the shared-lightbox dialog for the "lightbox
    reel" mechanic. Renders the Foundation lightbox.js contract's Alpine
    dialog (`x-data="lightbox"`, registered fleet-wide by
    theme-foundation's capell-frontend.js) skinned in First Light's own
    hairline/paper tokens rather than the framework default. Any `.lightbox`
    trigger on the page (curation-feed-grid, best-of-views-carousel) opens
    this same dialog and cycles Next/Previous within its `data-group`.
--}}

<div
    id="lightbox-carousel-viewer"
    class="mcf-section mcf-lightbox-section"
    data-widget="lightbox-carousel-viewer"
    data-variant="default"
    x-data="lightbox"
    @lightbox.window="lightbox(event)"
    @keyup.escape.window="close()"
    @keyup.left.window="loadPrevious()"
    @keyup.right.window="loadNext()"
>
    <div
        class="mcf-lightbox-dialog"
        x-show="currentUrl"
        x-cloak
        x-ref="lightboxDialog"
        role="dialog"
        aria-modal="true"
        aria-label="{{ __('capell-theme-first-light::sections.lightbox.aria_label') }}"
        tabindex="-1"
        @click="
            if ($event.target === $el) {
                close()
            }
        "
    >
        <div class="mcf-lightbox-frame">
            <button
                type="button"
                class="mcf-lightbox-close"
                aria-label="{{ __('capell-theme-first-light::sections.lightbox.close') }}"
                @click="close()"
            >
                &times;
            </button>

            <img
                class="mcf-lightbox-media"
                x-show="currentType !== 'video'"
                :src="currentUrl"
                :alt="currentTitle"
            />

            <video
                class="mcf-lightbox-media"
                playsinline
                autoplay
                controls
                x-show="currentType === 'video'"
                preload="none"
                :src="currentUrl"
            ></video>

            <div
                class="mcf-lightbox-controls"
                x-show="total() > 1"
            >
                <button
                    type="button"
                    class="mcf-lightbox-nav"
                    aria-label="{{ __('capell-theme-first-light::sections.lightbox.previous') }}"
                    @click.prevent="loadPrevious()"
                >
                    &lsaquo;
                </button>
                <span
                    class="mcf-lightbox-caption"
                    x-text="currentTitle"
                ></span>
                <button
                    type="button"
                    class="mcf-lightbox-nav"
                    aria-label="{{ __('capell-theme-first-light::sections.lightbox.next') }}"
                    @click.prevent="loadNext()"
                >
                    &rsaquo;
                </button>
            </div>
        </div>
    </div>
</div>
