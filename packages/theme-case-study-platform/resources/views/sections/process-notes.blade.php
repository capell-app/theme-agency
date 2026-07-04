@php
    $heading = data_get($section, 'heading', __('capell-theme-case-study-platform::sections.process_notes.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-case-study-platform::sections.process_notes.summary'));
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-case-study-platform::sections.process_notes.constraint_title'), 'summary' => __('capell-theme-case-study-platform::sections.process_notes.constraint_summary')],
        ['title' => __('capell-theme-case-study-platform::sections.process_notes.call_title'), 'summary' => __('capell-theme-case-study-platform::sections.process_notes.call_summary')],
        ['title' => __('capell-theme-case-study-platform::sections.process_notes.result_title'), 'summary' => __('capell-theme-case-study-platform::sections.process_notes.result_summary')],
    ]);
    $itemImage = data_get($section, 'image', data_get($section, 'imageUrl'));
    $itemAlt = data_get($section, 'imageAlt', $heading);
    $label = data_get($section, 'label', __('capell-theme-case-study-platform::sections.process_notes.button'));
    $url = data_get($section, 'url');
@endphp

<section
    id="process-notes"
    class="csp-section"
>
    <div class="csp-section-inner csp-split">
        <div>
            <p class="csp-kicker">
                {{ __('capell-theme-case-study-platform::sections.process_notes.heading') }}
            </p>
            <h2>{{ $heading }}</h2>
            <p class="csp-lede">{{ $summary }}</p>

            @if (filled($url))
                <div class="csp-actions">
                    <a
                        class="csp-button"
                        href="{{ $url }}"
                    >
                        {{ $label }}
                    </a>
                </div>
            @endif

            @if (filled($itemImage))
                <img
                    src="{{ $itemImage }}"
                    alt="{{ $itemAlt }}"
                    loading="lazy"
                    decoding="async"
                    class="csp-cover"
                    style="margin-top: 2rem"
                />
            @endif
        </div>

        <ol class="csp-process-list">
            @foreach ($items as $item)
                <li class="csp-process-item">
                    <h3>
                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                    </h3>
                    <p>
                        {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                    </p>
                </li>
            @endforeach
        </ol>
    </div>
</section>
