@php
    $items = data_get($section, 'items', [
        ['value' => __('capell-theme-portfolio-directory::sections.proof.profiles_value'), 'label' => __('capell-theme-portfolio-directory::sections.proof.profiles_label')],
        ['value' => __('capell-theme-portfolio-directory::sections.proof.rating_value'), 'label' => __('capell-theme-portfolio-directory::sections.proof.rating_label')],
        ['value' => __('capell-theme-portfolio-directory::sections.proof.lists_value'), 'label' => __('capell-theme-portfolio-directory::sections.proof.lists_label')],
    ]);
@endphp

<section
    id="proof"
    class="pfd-section"
>
    <div class="pfd-section-inner">
        <p class="pfd-eyebrow">
            {{ data_get($section, 'eyebrow', __('capell-theme-portfolio-directory::sections.proof.eyebrow')) }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-portfolio-directory::sections.proof.heading')) }}
        </h2>
        @if (filled(data_get($section, 'summary')))
            <p class="pfd-lede">{{ data_get($section, 'summary') }}</p>
        @endif

        <div class="pfd-stats">
            @foreach ($items as $item)
                <article class="pfd-stat">
                    <span class="pfd-stat-value">
                        {{ data_get($item, 'value', data_get($item, 'title', '')) }}
                    </span>
                    <p>
                        {{ data_get($item, 'label', data_get($item, 'summary', data_get($item, 'quote', ''))) }}
                    </p>
                </article>
            @endforeach
        </div>
    </div>
</section>
