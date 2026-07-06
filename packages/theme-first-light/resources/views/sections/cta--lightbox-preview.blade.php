{{--
    cta, `lightbox-preview` variant realising next-item-lightbox-cta: pairs
    the theme's standard call-to-action copy with a small "next capture"
    thumbnail that opens straight into the shared lightbox reel (same
    `curation-feed` group as curation-feed-grid / best-of-views-carousel), so
    a reader who has just closed the dialog is invited straight back in
    rather than only offered a text link.
--}}

@php
    $actions = collect(data_get($section, 'actions', []))
        ->filter(fn (mixed $action): bool => filled(data_get($action, 'label')))
        ->values();
    $nextImage = data_get($section, 'nextImage', data_get($section, 'next_image'));
    $nextTitle = data_get($section, 'nextTitle', data_get($section, 'next_title', __('capell-theme-first-light::sections.cta.next_title')));
@endphp

<section
    class="mcf-section"
    data-widget="next-item-lightbox-cta"
    data-variant="lightbox-preview"
>
    <div class="mcf-section-inner mcf-cta-lightbox-row">
        <div>
            <p class="mcf-kicker">
                {{ data_get($section, 'kicker', __('capell-theme-first-light::sections.cta.kicker')) }}
            </p>
            <h2>
                {{ data_get($section, 'heading', __('capell-theme-first-light::sections.cta.heading')) }}
            </h2>
            <p class="mcf-lede">
                {{ data_get($section, 'summary', __('capell-theme-first-light::sections.cta.summary')) }}
            </p>
            <div class="mcf-actions">
                @if ($actions->isNotEmpty())
                    @foreach ($actions as $action)
                        <a
                            class="mcf-button {{ data_get($action, 'style') === 'secondary' ? 'mcf-button-secondary' : '' }}"
                            href="{{ data_get($action, 'url', '/') }}"
                        >
                            {{ data_get($action, 'label') }}
                        </a>
                    @endforeach
                @else
                    <a
                        class="mcf-button"
                        href="{{ data_get($section, 'url', '#newsletter') }}"
                    >
                        {{ data_get($section, 'label', __('capell-theme-first-light::sections.cta.button')) }}
                    </a>
                @endif
            </div>
        </div>

        @if (filled($nextImage))
            <a
                href="{{ $nextImage }}"
                class="lightbox mcf-cta-next-trigger"
                data-lightbox="{{ $nextImage }}"
                data-group="curation-feed"
                data-type="image"
                data-title="{{ $nextTitle }}"
                aria-label="{{ __('capell-theme-first-light::sections.cta.next_aria', ['title' => $nextTitle]) }}"
            >
                <img
                    src="{{ $nextImage }}"
                    alt="{{ $nextTitle }}"
                    loading="lazy"
                    decoding="async"
                    class="mcf-cta-next-media"
                />
                <span
                    class="mcf-tiny mcf-cta-next-caption"
                    >{{ $nextTitle }}</span
                >
            </a>
        @endif
    </div>
</section>
