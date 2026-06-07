@php
    $heading ??= $section->heading ?? null;
    $summary ??= $section->summary ?? null;
    $storyItems = $section->items ?? [
        ['type' => __('capell-theme-nonprofit::generic.story_type_volunteer')],
        ['type' => __('capell-theme-nonprofit::generic.story_type_donor')],
        ['type' => __('capell-theme-nonprofit::generic.story_type_community')],
    ];
@endphp

<section class="theme-section theme-section-stories bg-white">
    <div class="mx-auto max-w-5xl px-6 py-14">
        @isset($heading)
            <div class="grid gap-5 md:grid-cols-[0.7fr_1fr] md:items-end">
                <div>
                    <p
                        class="nonprofit-text-accent-strong text-xs font-black tracking-[0.18em] uppercase"
                    >
                        {{ __('capell-theme-nonprofit::generic.story_cards_label') }}
                    </p>
                    <h2
                        class="nonprofit-text-ink mt-4 text-4xl font-black tracking-tight"
                    >
                        {{ $heading }}
                    </h2>
                </div>
                <p class="max-w-2xl text-lg text-slate-600 md:justify-self-end">
                    {{ $summary ?? (($blogAvailable ?? false) ? __('capell-theme-nonprofit::generic.stories_connected') : __('capell-theme-nonprofit::generic.stories_static')) }}
                </p>
            </div>
        @endisset

        <div class="mt-10 grid gap-4 md:grid-cols-[1.2fr_0.9fr_0.9fr]">
            @foreach ($storyItems as $story)
                <article
                    class="{{ $loop->first ? 'nonprofit-bg-primary-deep text-white' : 'nonprofit-text-ink bg-white' }} border border-slate-200 p-5 shadow-sm"
                >
                    <p
                        class="{{ $loop->first ? 'nonprofit-text-accent' : 'nonprofit-text-primary' }} text-xs font-black tracking-[0.18em] uppercase"
                    >
                        {{ $story['type'] ?? __('capell-theme-nonprofit::generic.story_signal') }}
                    </p>
                    <h3 class="mt-3 text-lg font-black">
                        {{ $story['title'] ?? __('capell-theme-nonprofit::generic.story_card_title') }}
                    </h3>
                    <p
                        class="{{ $loop->first ? 'nonprofit-text-on-dark-muted' : 'text-slate-600' }} mt-3 text-sm leading-6"
                    >
                        {{ $story['summary'] ?? __('capell-theme-nonprofit::generic.story_card_summary') }}
                    </p>
                    <p
                        class="{{ $loop->first ? 'nonprofit-text-accent' : 'nonprofit-text-accent-strong' }} mt-5 text-xs font-black"
                    >
                        {{ $story['meta'] ?? __('capell-theme-nonprofit::generic.story_meta_signal') }}
                    </p>
                </article>
            @endforeach
        </div>
    </div>
</section>
