@php
    $heading = data_get($section, 'heading', __('capell-theme-field-guide::sections.filter_hero.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-field-guide::sections.filter_hero.summary'));
    $chips = collect(data_get($section, 'items', __('capell-theme-field-guide::sections.filter_hero.chips')))
        ->filter(fn (mixed $chip): bool => filled(data_get($chip, 'label', data_get($chip, 'title'))))
        ->values();
@endphp

<section
    id="filter-hero"
    class="fga-section"
>
    <div class="fga-section-inner fga-section-inner-tight">
        <search class="fga-search-band">
            <div class="fga-section-head-copy">
                <p class="fga-kicker">
                    {{ __('capell-theme-field-guide::sections.filter_hero.kicker') }}
                </p>
                <h2>{{ $heading }}</h2>
                <p class="fga-lede">{{ $summary }}</p>
            </div>

            <form
                class="fga-search-form"
                method="get"
                action="{{ data_get($section, 'action', '#latest-designs') }}"
                role="search"
            >
                <label
                    class="sr-only"
                    for="fga-capture-search"
                >
                    {{ __('capell-theme-field-guide::sections.filter_hero.search_label') }}
                </label>
                <input
                    id="fga-capture-search"
                    name="q"
                    type="search"
                    placeholder="{{ __('capell-theme-field-guide::sections.filter_hero.search_placeholder') }}"
                />
                <button
                    class="fga-button"
                    type="submit"
                >
                    {{ __('capell-theme-field-guide::sections.filter_hero.search_button') }}
                </button>
            </form>

            <div class="fga-search-hints">
                <span class="fga-mono-note">
                    {{ __('capell-theme-field-guide::sections.filter_hero.hint_label') }}
                </span>
                <ul class="fga-chip-row">
                    @foreach ($chips as $chip)
                        <li>
                            <a
                                class="fga-chip fga-chip-{{ data_get($chip, 'facet', 'type') }}"
                                href="{{ data_get($chip, 'url', '#taxonomy-navigation') }}"
                            >
                                {{ data_get($chip, 'label', data_get($chip, 'title', '')) }}
                                @if (filled(data_get($chip, 'count')))
                                    <span class="fga-chip-count">
                                        {{ data_get($chip, 'count') }}
                                    </span>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </search>
    </div>
</section>
