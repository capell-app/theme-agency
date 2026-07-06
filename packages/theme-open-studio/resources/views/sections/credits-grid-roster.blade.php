{{--
    credits-grid-roster — a grid of the full project credits/roster (name,
    role, and an optional headshot), distinct from the smaller
    credits-tools summary card so a project's full crew list gets its own
    scannable grid. Capped at fifty roster entries per §0.3.
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

        <div class="csp-roster-grid">
            @foreach ($items as $item)
                @php
                    $photo = data_get($item, 'image', data_get($item, 'imageUrl'));
                    $name = data_get($item, 'name', data_get($item, 'title', ''));
                @endphp

                <article class="csp-roster-card">
                    @if (filled($photo))
                        <img
                            src="{{ $photo }}"
                            alt="{{ $name }}"
                            loading="lazy"
                            decoding="async"
                            class="csp-roster-photo"
                        />
                    @else
                        <div
                            class="csp-roster-photo csp-roster-photo-empty"
                            aria-hidden="true"
                        >
                            {{ mb_substr($name, 0, 1) }}
                        </div>
                    @endif

                    <h3>{{ $name }}</h3>
                    <p class="csp-roster-role">
                        {{ data_get($item, 'role', data_get($item, 'summary', '')) }}
                    </p>
                </article>
            @endforeach
        </div>
    </div>
</section>
