@php
    $heading = data_get($section, 'heading', __('capell-theme-quiet-type::sections.contextual_glossary.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-quiet-type::sections.contextual_glossary.summary'));
    $terms = collect(data_get($section, 'terms', []));
@endphp

{{--
    contextual-glossary-hover, compact variant — same hover/long-press
    mechanic as the default treatment (see contextual-glossary.blade.php),
    rendered as a dense two-column term strip rather than a run-in
    paragraph plus full definition list, for a shorter aside placement.
--}}
<section
    id="contextual-glossary"
    class="eser-section eser-section-compact"
>
    <div class="eser-section-inner">
        <p class="eser-eyebrow">
            {{ __('capell-theme-quiet-type::sections.contextual_glossary.eyebrow') }}
        </p>
        <h2>{{ $heading }}</h2>
        <hr class="eser-heading-rule" />
        @if (filled($summary))
            <p class="eser-summary">{{ $summary }}</p>
        @endif

        @if ($terms->isNotEmpty())
            <div class="eser-glossary-strip">
                @foreach ($terms as $term)
                    @php
                        $termLabel = data_get($term, 'term', '');
                        $definition = data_get($term, 'definition', '');
                    @endphp
                    <dfn
                        class="eser-glossary-term eser-glossary-term-strip"
                        tabindex="0"
                        title="{{ $definition }}"
                        data-definition="{{ $definition }}"
                        >{{ $termLabel }}</dfn
                    >
                @endforeach
            </div>
        @endif
    </div>
</section>
