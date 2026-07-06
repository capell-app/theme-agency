{{--
    credits-grid-roster — rows variant: a compact list layout (name + role
    on one line) for long rosters or narrower Layout Builder columns, instead
    of the photo-grid default.
--}}
@php
    $heading = data_get($section, 'heading', __('capell-theme-open-studio::sections.credits_roster.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-open-studio::sections.credits_roster.summary'));
    $items = collect(data_get($section, 'items', []))->take(50)->values();

    if ($items->isEmpty()) {
        $items = collect([
            ['name' => __('capell-theme-open-studio::sections.credits_roster.entry_name'), 'role' => __('capell-theme-open-studio::sections.credits_roster.entry_role')],
        ]);
    }
@endphp

<section
    id="credits-grid-roster"
    class="csp-section csp-section-field"
>
    <div class="csp-section-inner">
        <p class="csp-kicker">
            {{ __('capell-theme-open-studio::sections.credits_roster.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="csp-lede">{{ $summary }}</p>

        <ul class="csp-roster-rows">
            @foreach ($items as $item)
                <li class="csp-roster-row">
                    <span class="csp-credit-name">
                        {{ data_get($item, 'name', data_get($item, 'title', '')) }}
                    </span>
                    <span class="csp-credit-role">
                        {{ data_get($item, 'role', data_get($item, 'summary', '')) }}
                    </span>
                </li>
            @endforeach
        </ul>
    </div>
</section>
