@php
    $heading = data_get($section, 'heading', __('capell-theme-editorial-serif::sections.proof.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-editorial-serif::sections.proof.summary'));
    $items = collect(data_get($section, 'items', []));
@endphp

<section
    id="proof"
    class="eser-section eser-section-shade"
>
    <div class="eser-section-inner">
        <p class="eser-eyebrow">
            {{ __('capell-theme-editorial-serif::sections.proof.eyebrow') }}
        </p>
        <h2>{{ $heading }}</h2>
        <hr class="eser-heading-rule" />
        @if (filled($summary))
            <p class="eser-summary">{{ $summary }}</p>
        @endif

        @if ($items->isNotEmpty())
            <div class="eser-list">
                @foreach ($items as $item)
                    <article class="eser-list-item">
                        <h3 class="eser-list-title">
                            {{ data_get($item, 'title', data_get($item, 'value', '')) }}
                        </h3>
                        <p class="eser-list-body">
                            {{ data_get($item, 'summary', data_get($item, 'label', '')) }}
                        </p>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>
