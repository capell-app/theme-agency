@php
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-bold-sport-commerce::sections.campaign.launch_title'), 'summary' => __('capell-theme-bold-sport-commerce::sections.campaign.launch_summary')],
        ['title' => __('capell-theme-bold-sport-commerce::sections.campaign.event_title'), 'summary' => __('capell-theme-bold-sport-commerce::sections.campaign.event_summary')],
        ['title' => __('capell-theme-bold-sport-commerce::sections.campaign.community_title'), 'summary' => __('capell-theme-bold-sport-commerce::sections.campaign.community_summary')],
    ]);
@endphp

<section class="sport-section">
    <div class="sport-section-inner sport-split">
        <div>
            <p class="sport-kicker">
                {{ __('capell-theme-bold-sport-commerce::sections.campaign.kicker') }}
            </p>
            <h2>
                {{ data_get($section, 'heading', __('capell-theme-bold-sport-commerce::sections.campaign.heading')) }}
            </h2>
            <p class="sport-lede">
                {{ data_get($section, 'summary', __('capell-theme-bold-sport-commerce::sections.campaign.summary')) }}
            </p>
        </div>
        <div class="sport-grid">
            @foreach ($items as $item)
                <article class="sport-card">
                    <h3>
                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                    </h3>
                    <p>{{ data_get($item, 'summary', '') }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
