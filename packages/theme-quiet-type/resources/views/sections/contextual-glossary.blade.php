@php
    $heading = data_get($section, 'heading', __('capell-theme-quiet-type::sections.contextual_glossary.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-quiet-type::sections.contextual_glossary.summary'));
    $terms = collect(data_get($section, 'terms', []));
@endphp

{{--
    contextual-glossary-hover (Part 2 §B) — inline term definitions shown
    on hover/long-press, sourced from a payload term -> definition map. Each
    term is a semantic `<dfn>` carrying `title` as the no-JS/no-CSS-hover
    fallback (works via the browser's native tooltip on hover and, on most
    touch browsers, via long-press) plus a pure-CSS `::after` popover shown
    on `:hover`/`:focus-visible` so sighted mouse/keyboard users get a
    richer, on-brand definition card without any JS at all — keeping this
    widget at 0KB against the §0.4 per-theme JS budget. `tabindex="0"` makes
    the term keyboard-reachable so the `:focus-visible` state is reachable
    without a pointer.

    Default variant renders the full glossary as a run-in list of defined
    terms (each usable inline elsewhere via the same markup shape); the
    `compact` variant (contextual-glossary--compact.blade.php) renders the
    same terms as a dense two-column strip for a shorter aside placement.
--}}
<section
    id="contextual-glossary"
    class="eser-section"
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
            <p class="eser-glossary-run-in">
                @foreach ($terms as $term)
                    @php
                        $termLabel = data_get($term, 'term', '');
                        $definition = data_get($term, 'definition', '');
                    @endphp
                    <dfn
                        class="eser-glossary-term"
                        tabindex="0"
                        title="{{ $definition }}"
                        data-definition="{{ $definition }}"
                        >{{ $termLabel }}</dfn
                    >{{ $loop->last ? '.' : ', ' }}
                @endforeach
            </p>

            <dl class="eser-glossary-list">
                @foreach ($terms as $term)
                    <div class="eser-glossary-list-item">
                        <dt class="eser-glossary-list-term">
                            {{ data_get($term, 'term', '') }}
                        </dt>
                        <dd class="eser-glossary-list-definition">
                            {{ data_get($term, 'definition', '') }}
                        </dd>
                    </div>
                @endforeach
            </dl>
        @endif
    </div>
</section>
