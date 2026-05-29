<section class="theme-section theme-section-stories bg-white">
    <div class="mx-auto max-w-5xl px-6 py-14">
        @isset($heading)
            <div class="grid gap-5 md:grid-cols-[0.7fr_1fr] md:items-end">
                <div>
                    <p
                        class="text-xs font-black tracking-[0.18em] text-[#ca8a04] uppercase"
                    >
                        {{ __('capell-theme-nonprofit::generic.story_cards_label') }}
                    </p>
                    <h2
                        class="mt-4 text-4xl font-black tracking-tight text-[#0f172a]"
                    >
                        {{ $heading }}
                    </h2>
                </div>
                <p class="max-w-2xl text-lg text-slate-600 md:justify-self-end">
                    {{ $blogAvailable ?? false ? __('capell-theme-nonprofit::generic.stories_connected') : __('capell-theme-nonprofit::generic.stories_static') }}
                </p>
            </div>
        @endisset

        <div class="mt-10 grid gap-4 md:grid-cols-[1.2fr_0.9fr_0.9fr]">
            @foreach (['Volunteer', 'Donor', 'Community'] as $storyType)
                <article
                    class="{{ $loop->first ? 'bg-[#12351f] text-white' : 'bg-white text-[#0f172a]' }} border border-slate-200 p-5 shadow-sm"
                >
                    <p
                        class="{{ $loop->first ? 'text-[#fde047]' : 'text-[#166534]' }} text-xs font-black tracking-[0.18em] uppercase"
                    >
                        {{ $storyType }}
                    </p>
                    <h3 class="mt-3 text-lg font-black">
                        {{ __('capell-theme-nonprofit::generic.story_card_title') }}
                    </h3>
                    <p
                        class="{{ $loop->first ? 'text-emerald-50' : 'text-slate-600' }} mt-3 text-sm leading-6"
                    >
                        {{ __('capell-theme-nonprofit::generic.story_card_summary') }}
                    </p>
                    <p
                        class="{{ $loop->first ? 'text-[#fde047]' : 'text-[#854d0e]' }} mt-5 text-xs font-black"
                    >
                        {{ __('capell-theme-nonprofit::generic.story_meta_signal') }}
                    </p>
                </article>
            @endforeach
        </div>
    </div>
</section>
