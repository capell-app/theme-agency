@php
    $items = data_get($section, 'items', [
        ['title' => 'SaaS & software', 'summary' => 'Product launches, pricing pages, and free-trial flows.'],
        ['title' => 'Ecommerce & DTC', 'summary' => 'Storefronts and product drops built to convert.'],
        ['title' => 'Startups & fundraising', 'summary' => 'Waitlists and investor-ready one-pagers.'],
        ['title' => 'Mobile & app', 'summary' => 'App-store landing pages tuned for installs.'],
    ]);
@endphp

<section
    id="category-navigation"
    class="lga-section"
>
    <div class="lga-section-inner">
        <p class="lga-eyebrow">
            {{ __('capell-theme-launch-pad::sections.category_navigation.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-launch-pad::sections.category_navigation.heading')) }}
        </h2>
        <p class="lga-lede">
            {{ data_get($section, 'summary', __('capell-theme-launch-pad::sections.category_navigation.summary')) }}
        </p>

        <div class="lga-chip-row">
            @foreach ($items as $item)
                @php
                    $chipUrl = data_get($item, 'url', '#website-examples');
                @endphp

                <a
                    class="lga-chip"
                    href="{{ $chipUrl }}"
                >
                    {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                    <span>
                        {{ data_get($item, 'summary', __('capell-theme-launch-pad::sections.category_navigation.default_summary')) }}
                    </span>
                </a>
            @endforeach
        </div>
    </div>
</section>
