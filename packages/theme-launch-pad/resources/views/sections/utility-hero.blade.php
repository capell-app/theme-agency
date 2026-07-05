@php
    $heading = data_get($section, 'heading', __('capell-theme-launch-pad::sections.utility_hero.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-launch-pad::sections.utility_hero.summary'));
    $searchAction = data_get($section, 'searchUrl', data_get($section, 'action', '#content-listing'));
    $linkLabel = data_get($section, 'linkLabel', __('capell-theme-launch-pad::sections.utility_hero.link_label'));
    $linkUrl = data_get($section, 'linkUrl', '#newsletter');
@endphp

<section
    id="utility-hero"
    class="lga-section lga-section-field"
>
    <div class="lga-section-inner">
        <p class="lga-eyebrow">
            {{ __('capell-theme-launch-pad::sections.utility_hero.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="lga-lede">{{ $summary }}</p>

        <form
            method="get"
            action="{{ $searchAction }}"
            class="lga-search-form"
            role="search"
        >
            <label
                for="lga-utility-search"
                class="sr-only"
            >
                {{ __('capell-theme-launch-pad::sections.utility_hero.search_label') }}
            </label>
            <input
                id="lga-utility-search"
                class="lga-search-input"
                name="q"
                type="search"
                placeholder="{{ __('capell-theme-launch-pad::sections.utility_hero.search_placeholder') }}"
            />
            <button
                class="lga-button"
                type="submit"
            >
                {{ __('capell-theme-launch-pad::sections.utility_hero.submit_label') }}
            </button>
        </form>

        <p class="lga-lede">
            <a href="{{ $linkUrl }}">{{ $linkLabel }}</a>
        </p>
    </div>
</section>
