@php
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-ink-press::sections.recap.palette_title'), 'summary' => __('capell-theme-ink-press::sections.recap.palette_summary')],
        ['title' => __('capell-theme-ink-press::sections.recap.rooms_title'), 'summary' => __('capell-theme-ink-press::sections.recap.rooms_summary')],
        ['title' => __('capell-theme-ink-press::sections.recap.objects_title'), 'summary' => __('capell-theme-ink-press::sections.recap.objects_summary')],
    ]);
@endphp

<section
    id="missed-it"
    class="dnews-section dnews-section-tint"
>
    <div class="dnews-section-inner">
        <div class="dnews-section-head">
            <p class="dnews-kicker">
                {{ __('capell-theme-ink-press::sections.recap.kicker') }}
            </p>
            <h2>
                {{ data_get($section, 'heading', __('capell-theme-ink-press::sections.recap.heading')) }}
            </h2>
            <p class="dnews-lede">
                {{ data_get($section, 'summary', __('capell-theme-ink-press::sections.recap.summary')) }}
            </p>
        </div>

        <div class="dnews-grid">
            @foreach ($items as $item)
                @php
                    $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                @endphp

                <article class="dnews-card">
                    <p class="dnews-meta">
                        {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }} · {{ __('capell-theme-ink-press::sections.recap.item_label') }}
                    </p>
                    <h3>
                        @if ($itemUrl !== null)
                            <a
                                class="dnews-title-link"
                                href="{{ $itemUrl }}"
                            >
                                {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                            </a>
                        @else
                            {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                        @endif
                    </h3>
                    <p>{{ data_get($item, 'summary', '') }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
