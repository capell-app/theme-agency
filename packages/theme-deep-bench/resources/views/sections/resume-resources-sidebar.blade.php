{{--
    resume-resources-sidebar — a compact aside rail of career resources
    (templates, guides, salary data), distinct from the wider
    resume-resources numbered list section: this one is built to sit beside
    the roster grid rather than as its own full-width band.
--}}
@php
    $heading = data_get($section, 'heading', __('capell-theme-deep-bench::sections.resources_sidebar.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-deep-bench::sections.resources_sidebar.summary'));
    $resources = collect(data_get($section, 'items', [
        ['title' => __('capell-theme-deep-bench::sections.resources_sidebar.resume_title'), 'meta' => __('capell-theme-deep-bench::sections.resources_sidebar.resume_meta')],
        ['title' => __('capell-theme-deep-bench::sections.resources_sidebar.case_title'), 'meta' => __('capell-theme-deep-bench::sections.resources_sidebar.case_meta')],
        ['title' => __('capell-theme-deep-bench::sections.resources_sidebar.salary_title'), 'meta' => __('capell-theme-deep-bench::sections.resources_sidebar.salary_meta')],
        ['title' => __('capell-theme-deep-bench::sections.resources_sidebar.rates_title'), 'meta' => __('capell-theme-deep-bench::sections.resources_sidebar.rates_meta')],
    ]))->take(20);
@endphp

<section
    id="resume-resources-sidebar"
    class="pfd-section pfd-section-field"
>
    <div class="pfd-section-inner pfd-resources-sidebar-inner">
        <div>
            <p class="pfd-eyebrow">
                {{ data_get($section, 'eyebrow', __('capell-theme-deep-bench::sections.resources_sidebar.eyebrow')) }}
            </p>
            <h2>{{ $heading }}</h2>
            <p class="pfd-lede">{{ $summary }}</p>
        </div>

        <aside
            class="pfd-resources-sidebar"
            aria-label="{{ $heading }}"
        >
            <ul class="pfd-resources-sidebar-list">
                @foreach ($resources as $resource)
                    @php
                        $resourceUrl = data_get($resource, 'url', data_get($resource, 'href', '#resume-resources-sidebar'));
                    @endphp

                    <li class="pfd-resources-sidebar-item">
                        <a
                            class="pfd-resources-sidebar-link"
                            href="{{ $resourceUrl }}"
                        >
                            <span
                                >{{ data_get($resource, 'title', data_get($resource, 'name', '')) }}</span
                            >
                            <span class="pfd-resources-sidebar-meta">
                                {{ data_get($resource, 'meta', data_get($resource, 'category', '')) }}
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>

            <a
                class="pfd-button pfd-button-secondary"
                href="{{ data_get($section, 'url', '#resume-resources-sidebar') }}"
            >
                {{ data_get($section, 'label', __('capell-theme-deep-bench::sections.resources_sidebar.button')) }}
            </a>
        </aside>
    </div>
</section>
