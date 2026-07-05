@php
    $heading = data_get($section, 'heading', __('capell-theme-ink-press::sections.live.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-ink-press::sections.live.summary'));
    $updates = data_get($section, 'items', [
        ['title' => __('capell-theme-ink-press::sections.live.sequence_title'), 'summary' => __('capell-theme-ink-press::sections.live.sequence_summary')],
        ['title' => __('capell-theme-ink-press::sections.live.caption_title'), 'summary' => __('capell-theme-ink-press::sections.live.caption_summary')],
    ]);
@endphp

<section
    id="live-brief"
    class="dnews-section dnews-section-dark"
>
    <div class="dnews-section-inner dnews-split">
        <div>
            <div class="dnews-section-head dnews-section-head-flush">
                <p class="dnews-kicker">
                    {{ __('capell-theme-ink-press::sections.live.kicker') }}
                </p>
                <h2>{{ $heading }}</h2>
                <p class="dnews-lede">{{ $summary }}</p>
            </div>
            <div class="dnews-actions">
                <a
                    class="dnews-button"
                    href="{{ data_get($section, 'url', '#live-brief') }}"
                >
                    {{ data_get($section, 'label', __('capell-theme-ink-press::sections.live.button')) }}
                </a>
            </div>
        </div>

        <div class="dnews-feed">
            @foreach ($updates as $update)
                <article class="dnews-feed-item">
                    <span
                        class="dnews-feed-marker"
                        aria-hidden="true"
                    ></span>
                    <h3>
                        {{ data_get($update, 'title', data_get($update, 'name', '')) }}
                    </h3>
                    <p>{{ data_get($update, 'summary', '') }}</p>
                    <p class="dnews-meta">
                        {{ data_get($update, 'meta', data_get($update, 'time', __('capell-theme-ink-press::sections.live.update_label'))) }}
                    </p>
                </article>
            @endforeach
        </div>
    </div>
</section>
