@php
    $heading = data_get($section, 'heading', __('capell-theme-editorial-serif::sections.editorial_statement.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-editorial-serif::sections.editorial_statement.summary'));
    $items = collect(data_get($section, 'items', data_get($section, 'principles', [])));
@endphp

<section
    id="editorial-statement"
    class="eser-section"
>
    <div class="eser-section-inner">
        <p class="eser-eyebrow">
            {{ __('capell-theme-editorial-serif::sections.editorial_statement.eyebrow') }}
        </p>
        <h2>{{ $heading }}</h2>
        <hr class="eser-heading-rule" />
        @if (filled($summary))
            <p class="eser-summary eser-dropcap">{{ $summary }}</p>
        @endif

        @if ($items->isNotEmpty())
            <div class="eser-list">
                @foreach ($items as $item)
                    <article class="eser-list-item">
                        <h3 class="eser-list-title">
                            {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                        </h3>
                        <p class="eser-list-body">
                            {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                        </p>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>
