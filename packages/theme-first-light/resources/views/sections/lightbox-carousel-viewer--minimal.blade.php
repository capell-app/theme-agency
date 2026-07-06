{{--
    lightbox-carousel-viewer, `minimal` variant: edge-to-edge frameless
    presentation with no dialog chrome beyond the close/Next/Previous
    controls -- for placements that want the capture itself to be the whole
    screen, no paper-card frame around it. Same Alpine `x-data="lightbox"`
    contract and `.lightbox` trigger discovery as the default variant.
--}}

<div
    id="lightbox-carousel-viewer"
    class="mcf-section mcf-lightbox-section"
    data-widget="lightbox-carousel-viewer"
    data-variant="minimal"
    x-data="lightbox"
    @lightbox.window="lightbox(event)"
    @keyup.escape.window="close()"
    @keyup.left.window="loadPrevious()"
    @keyup.right.window="loadNext()"
>
    <div
        class="mcf-lightbox-dialog mcf-lightbox-dialog-minimal"
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
        <button
            type="button"
            class="mcf-lightbox-close mcf-lightbox-close-minimal"
            aria-label="{{ __('capell-theme-first-light::sections.lightbox.close') }}"
            @click="close()"
        >
            &times;
        </button>

        <img
            class="mcf-lightbox-media mcf-lightbox-media-minimal"
            x-show="currentType !== 'video'"
            :src="currentUrl"
            :alt="currentTitle"
        />

        <video
            class="mcf-lightbox-media mcf-lightbox-media-minimal"
            playsinline
            autoplay
            controls
            x-show="currentType === 'video'"
            preload="none"
            :src="currentUrl"
        ></video>

        <div
            class="mcf-lightbox-controls mcf-lightbox-controls-minimal"
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
