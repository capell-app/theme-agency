@php
    $heading = data_get($section, 'heading', __('capell-theme-deep-bench::sections.filters.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-deep-bench::sections.filters.summary'));
    $filters = data_get($section, 'items', [
        ['title' => __('capell-theme-deep-bench::sections.filters.all'), 'meta' => '1,200', 'active' => true],
        ['title' => __('capell-theme-deep-bench::sections.filters.product'), 'meta' => '324'],
        ['title' => __('capell-theme-deep-bench::sections.filters.brand'), 'meta' => '287'],
        ['title' => __('capell-theme-deep-bench::sections.filters.motion'), 'meta' => '142'],
        ['title' => __('capell-theme-deep-bench::sections.filters.frontend'), 'meta' => '253'],
        ['title' => __('capell-theme-deep-bench::sections.filters.illustration'), 'meta' => '96'],
        ['title' => __('capell-theme-deep-bench::sections.filters.studios'), 'meta' => '98'],
    ]);
@endphp

<section
    id="role-filters"
    class="pfd-section"
>
    <div class="pfd-section-inner">
        <p class="pfd-eyebrow">
            {{ data_get($section, 'eyebrow', __('capell-theme-deep-bench::sections.filters.eyebrow')) }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="pfd-lede">{{ $summary }}</p>

        <ul class="pfd-pillrow">
            @foreach ($filters as $filter)
                @php
                    $filterUrl = data_get($filter, 'url', data_get($filter, 'href', '#portfolio-grid'));
                    $filterCount = data_get($filter, 'meta', data_get($filter, 'count'));
                    $filterActive = (bool) data_get($filter, 'active', $loop->first);
                @endphp

                <li>
                    <a
                        class="pfd-pill {{ $filterActive ? 'pfd-pill-active' : '' }}"
                        href="{{ $filterUrl }}"
                    >
                        {{ data_get($filter, 'title', data_get($filter, 'label', '')) }}
                        @if (filled($filterCount))
                            <span class="pfd-pill-count">
                                {{ $filterCount }}
                            </span>
                        @endif
                    </a>
                </li>
            @endforeach
        </ul>

        <p class="pfd-pill-note">
            {{ data_get($section, 'note', __('capell-theme-deep-bench::sections.filters.note')) }}
        </p>
    </div>
</section>
