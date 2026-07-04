@php
    $heading = data_get($section, 'heading', __('capell-theme-portfolio-directory::sections.resources.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-portfolio-directory::sections.resources.summary'));
    $resources = data_get($section, 'items', [
        ['title' => __('capell-theme-portfolio-directory::sections.resources.resume_title'), 'summary' => __('capell-theme-portfolio-directory::sections.resources.resume_summary'), 'meta' => __('capell-theme-portfolio-directory::sections.resources.resume_meta')],
        ['title' => __('capell-theme-portfolio-directory::sections.resources.case_title'), 'summary' => __('capell-theme-portfolio-directory::sections.resources.case_summary'), 'meta' => __('capell-theme-portfolio-directory::sections.resources.case_meta')],
        ['title' => __('capell-theme-portfolio-directory::sections.resources.salary_title'), 'summary' => __('capell-theme-portfolio-directory::sections.resources.salary_summary'), 'meta' => __('capell-theme-portfolio-directory::sections.resources.salary_meta')],
    ]);
@endphp

<section
    id="resume-resources"
    class="pfd-section pfd-section-field"
>
    <div class="pfd-section-inner">
        <p class="pfd-eyebrow">
            {{ data_get($section, 'eyebrow', __('capell-theme-portfolio-directory::sections.resources.eyebrow')) }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="pfd-lede">{{ $summary }}</p>

        <div class="pfd-rail">
            @foreach ($resources as $resource)
                @php
                    $resourceUrl = data_get($resource, 'url', data_get($resource, 'href'));
                @endphp

                <article class="pfd-rail-row">
                    <span
                        class="pfd-rail-number"
                        aria-hidden="true"
                    >
                        {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                    </span>
                    <h3>
                        @if (filled($resourceUrl))
                            <a
                                class="pfd-title-link"
                                href="{{ $resourceUrl }}"
                            >
                                {{ data_get($resource, 'title', data_get($resource, 'name', '')) }}
                            </a>
                        @else
                            {{ data_get($resource, 'title', data_get($resource, 'name', '')) }}
                        @endif
                    </h3>
                    <p>
                        {{ data_get($resource, 'summary', data_get($resource, 'description', '')) }}
                    </p>
                    <span class="pfd-rail-meta">
                        {{ data_get($resource, 'meta', data_get($resource, 'category', '')) }}
                    </span>
                </article>
            @endforeach
        </div>

        <div class="pfd-actions">
            <a
                class="pfd-button pfd-button-secondary"
                href="{{ data_get($section, 'url', '#resume-resources') }}"
            >
                {{ data_get($section, 'label', __('capell-theme-portfolio-directory::sections.resources.button')) }}
            </a>
        </div>
    </div>
</section>
