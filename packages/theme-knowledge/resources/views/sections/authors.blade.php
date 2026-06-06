@php
    $sectionHeading = $heading ?? ($section->heading ?? null);
    $authorCards = $section->items ?? $items ?? __('capell-theme-knowledge::generic.author_cards');
    $authorCards = is_array($authorCards) ? $authorCards : [];
@endphp

<section class="theme-section theme-section-authors bg-white">
    @if ($sectionHeading)
        <div class="mx-auto max-w-5xl px-6 pt-14">
            <p
                class="text-xs font-black tracking-[0.18em] text-[var(--site-theme-primary)] uppercase"
            >
                {{ __('capell-theme-knowledge::generic.authors_label') }}
            </p>
            <h2 class="mt-4 text-4xl font-black tracking-tight text-[var(--site-theme-foreground)]">
                {{ $sectionHeading }}
            </h2>
        </div>
    @endif

    <div
        class="mx-auto mt-6 grid max-w-5xl gap-4 px-6 pb-14 sm:grid-cols-2 lg:grid-cols-4"
    >
        @foreach ($authorCards as $authorCard)
            @php
                $authorTitle = is_array($authorCard) && is_scalar($authorCard['title'] ?? $authorCard['name'] ?? null) ? (string) ($authorCard['title'] ?? $authorCard['name']) : '';
                $authorSummary = is_array($authorCard) && is_scalar($authorCard['summary'] ?? $authorCard['description'] ?? null) ? (string) ($authorCard['summary'] ?? $authorCard['description']) : '';
                $markerClass = $loop->even ? 'bg-[var(--site-theme-accent)]' : 'bg-[var(--site-theme-primary)]';
            @endphp

            @if ($authorTitle !== '')
                <div class="border border-[var(--site-theme-primary-border)] bg-[var(--site-theme-primary-panel)] p-4">
                    <p
                        class="{{ $markerClass }} h-10 w-10"
                        aria-hidden="true"
                    ></p>
                    <p class="mt-3 font-black text-[var(--site-theme-foreground)]">
                        {{ $authorTitle }}
                    </p>
                    @if ($authorSummary !== '')
                        <p class="mt-1 text-sm text-stone-600">
                            {{ $authorSummary }}
                        </p>
                    @endif
                </div>
            @endif
        @endforeach
    </div>
</section>
