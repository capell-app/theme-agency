{{--
    next-project-cta — stacked variant: cover image above the copy instead
    of side-by-side, for narrower single-column placements.
--}}
@php
    $kicker = data_get($section, 'kicker', __('capell-theme-open-studio::sections.next_project.kicker'));
    $title = data_get($section, 'title', __('capell-theme-open-studio::sections.next_project.default_title'));
    $summary = data_get($section, 'summary', __('capell-theme-open-studio::sections.next_project.default_summary'));
    $url = data_get($section, 'url', '#project-feed');
    $image = data_get($section, 'image', data_get($section, 'imageUrl'));
    $label = data_get($section, 'label', __('capell-theme-open-studio::sections.next_project.button'));
@endphp

<section
    id="next-project-cta"
    class="csp-section csp-section-dark"
>
    <div class="csp-section-inner">
        <a
            class="csp-next-project csp-next-project-stacked"
            href="{{ $url }}"
        >
            @if (filled($image))
                <img
                    src="{{ $image }}"
                    alt="{{ $title }}"
                    loading="lazy"
                    decoding="async"
                    class="csp-cover csp-next-project-cover"
                />
            @else
                <div
                    class="csp-cover csp-cover-empty csp-next-project-cover"
                    aria-hidden="true"
                ></div>
            @endif

            <div class="csp-next-project-copy">
                <p class="csp-kicker">{{ $kicker }}</p>
                <h2>{{ $title }}</h2>
                <p class="csp-lede">{{ $summary }}</p>
                <span class="csp-button"> {{ $label }} </span>
            </div>
        </a>
    </div>
</section>
