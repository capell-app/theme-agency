@php
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-ink-press::sections.topics.architecture_title'), 'summary' => __('capell-theme-ink-press::sections.topics.architecture_summary')],
        ['title' => __('capell-theme-ink-press::sections.topics.interiors_title'), 'summary' => __('capell-theme-ink-press::sections.topics.interiors_summary')],
        ['title' => __('capell-theme-ink-press::sections.topics.fashion_title'), 'summary' => __('capell-theme-ink-press::sections.topics.fashion_summary')],
        ['title' => __('capell-theme-ink-press::sections.topics.art_title'), 'summary' => __('capell-theme-ink-press::sections.topics.art_summary')],
    ]);
@endphp

<section
    id="topic-navigation"
    class="dnews-section"
>
    <div class="dnews-section-inner">
        <div class="dnews-section-head">
            <p class="dnews-kicker">
                {{ __('capell-theme-ink-press::sections.topics.kicker') }}
            </p>
            <h2>
                {{ data_get($section, 'heading', __('capell-theme-ink-press::sections.topics.heading')) }}
            </h2>
            @if (data_get($section, 'summary') !== null)
                <p class="dnews-lede">{{ data_get($section, 'summary') }}</p>
            @endif
        </div>

        <div class="dnews-desk">
            @foreach ($items as $item)
                <article>
                    <p class="dnews-meta">
                        {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }} · {{ __('capell-theme-ink-press::sections.topics.desk_label') }}
                    </p>
                    <h3>
                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                    </h3>
                    <p>{{ data_get($item, 'summary', '') }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
