@php
    $heading = data_get($section, 'heading', __('capell-theme-case-study-platform::sections.credits_tools.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-case-study-platform::sections.credits_tools.summary'));
    $items = data_get($section, 'items', []);

    $credits = collect(data_get($section, 'credits', []));
    $tools = collect(data_get($section, 'tools', []));

    if ($credits->isEmpty() && $tools->isEmpty() && is_iterable($items)) {
        $credits = collect([
            ['role' => __('capell-theme-case-study-platform::sections.credits_tools.default_role'), 'name' => data_get(collect($items)->first(), 'summary', '')],
        ]);
    }
@endphp

<section
    id="credits-tools"
    class="csp-section"
>
    <div class="csp-section-inner">
        <p class="csp-kicker">
            {{ __('capell-theme-case-study-platform::sections.credits_tools.heading') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="csp-lede">{{ $summary }}</p>

        @if (is_iterable($items) && collect($items)->isNotEmpty())
            <div class="csp-credits-grid">
                @foreach ($items as $item)
                    <article class="csp-credits-card">
                        <h3>
                            {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                        </h3>
                        <p style="margin: 0; color: var(--csp-muted)">
                            {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                        </p>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>
