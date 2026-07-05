@php
    $items = collect(data_get($section, 'items', [
        ['title' => __('capell-theme-ink-press::sections.lead.house_title'), 'summary' => __('capell-theme-ink-press::sections.lead.house_summary')],
        ['title' => __('capell-theme-ink-press::sections.lead.studio_title'), 'summary' => __('capell-theme-ink-press::sections.lead.studio_summary')],
        ['title' => __('capell-theme-ink-press::sections.lead.city_title'), 'summary' => __('capell-theme-ink-press::sections.lead.city_summary')],
    ]));
    $lead = $items->first();
    $briefs = $items->slice(1);
@endphp

<section
    id="top-stories"
    class="dnews-section"
>
    <div class="dnews-section-inner">
        <div class="dnews-section-head">
            <p class="dnews-kicker">
                {{ __('capell-theme-ink-press::sections.lead.kicker') }}
            </p>
            <h2>
                {{ data_get($section, 'heading', __('capell-theme-ink-press::sections.lead.heading')) }}
            </h2>
            <p class="dnews-lede">
                {{ data_get($section, 'summary', __('capell-theme-ink-press::sections.lead.summary')) }}
            </p>
        </div>

        <div class="dnews-lead-grid">
            @if ($lead !== null)
                @php
                    $leadUrl = data_get($lead, 'url', data_get($lead, 'href'));
                @endphp

                <article class="dnews-lead-story">
                    <p class="dnews-meta">
                        {{ __('capell-theme-ink-press::sections.lead.lead_label') }}
                    </p>
                    <h3>
                        @if ($leadUrl !== null)
                            <a
                                class="dnews-title-link"
                                href="{{ $leadUrl }}"
                            >
                                {{ data_get($lead, 'title', data_get($lead, 'name', '')) }}
                            </a>
                        @else
                            {{ data_get($lead, 'title', data_get($lead, 'name', '')) }}
                        @endif
                    </h3>
                    <p>{{ data_get($lead, 'summary', '') }}</p>
                </article>
            @endif

            <div class="dnews-lead-aside">
                @foreach ($briefs as $brief)
                    @php
                        $briefUrl = data_get($brief, 'url', data_get($brief, 'href'));
                    @endphp

                    <article>
                        <p class="dnews-meta">
                            {{ str_pad((string) ($loop->iteration + 1), 2, '0', STR_PAD_LEFT) }} · {{ __('capell-theme-ink-press::sections.lead.brief_label') }}
                        </p>
                        <h4>
                            @if ($briefUrl !== null)
                                <a
                                    class="dnews-title-link"
                                    href="{{ $briefUrl }}"
                                >
                                    {{ data_get($brief, 'title', data_get($brief, 'name', '')) }}
                                </a>
                            @else
                                {{ data_get($brief, 'title', data_get($brief, 'name', '')) }}
                            @endif
                        </h4>
                        <p>{{ data_get($brief, 'summary', '') }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
</section>
