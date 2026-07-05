@php
    $items = data_get($section, 'items', []);
@endphp

<section
    id="partner-blocks"
    class="lga-section"
>
    <div class="lga-section-inner">
        <p class="lga-eyebrow">
            {{ __('capell-theme-launch-pad::sections.partner_blocks.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-launch-pad::sections.partner_blocks.heading')) }}
        </h2>
        <p class="lga-lede">
            {{ data_get($section, 'summary', __('capell-theme-launch-pad::sections.partner_blocks.summary')) }}
        </p>

        <div class="lga-grid">
            @foreach ($items as $item)
                @php
                    $partnerUrl = data_get($item, 'url', '#newsletter');
                @endphp

                <article class="lga-card">
                    <p class="lga-gallery-meta">
                        {{ data_get($item, 'meta', __('capell-theme-launch-pad::sections.partner_blocks.default_meta')) }}
                    </p>
                    <h3>
                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                    </h3>
                    <p>{{ data_get($item, 'summary', '') }}</p>
                    <a
                        class="lga-button lga-button-small lga-button-secondary"
                        href="{{ $partnerUrl }}"
                    >
                        {{ __('capell-theme-launch-pad::sections.partner_blocks.contact_label') }}
                    </a>
                </article>
            @endforeach
        </div>
    </div>
</section>
