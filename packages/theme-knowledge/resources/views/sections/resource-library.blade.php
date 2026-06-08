<section
    class="theme-section theme-section-resource-library bg-[var(--site-theme-surface)]"
>
    <div class="mx-auto max-w-5xl px-6 py-14">
        @isset($heading)
            <p
                class="text-xs font-black tracking-[0.18em] text-[var(--site-theme-primary)] uppercase"
            >
                {{ __('capell-theme-knowledge::generic.library_label') }}
            </p>
            <h2
                class="mt-4 text-4xl font-black tracking-tight text-[var(--site-theme-foreground)]"
            >
                {{ $heading }}
            </h2>
        @endisset

        <p class="mt-4 max-w-2xl text-slate-600">
            {{ $blogAvailable ?? false ? __('capell-theme-knowledge::generic.resource_library_connected') : __('capell-theme-knowledge::generic.resource_library_static') }}
        </p>

        <div class="mt-8 grid gap-4 md:grid-cols-3">
            @foreach ([
                          [__('capell-theme-knowledge::generic.library_guides'), __('capell-theme-knowledge::generic.library_guides_summary')],
                          [__('capell-theme-knowledge::generic.library_templates'), __('capell-theme-knowledge::generic.library_templates_summary')],
                          [__('capell-theme-knowledge::generic.library_research'), __('capell-theme-knowledge::generic.library_research_summary')],
                      ] as $card)
                <article class="border border-slate-200 bg-white p-5">
                    <p
                        class="text-xs font-black tracking-[0.16em] text-[var(--site-theme-primary)] uppercase"
                    >
                        {{ __('capell-theme-knowledge::generic.library_signal') }}
                    </p>
                    <h3
                        class="mt-3 text-lg font-black text-[var(--site-theme-foreground)]"
                    >
                        {{ $card[0] }}
                    </h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        {{ $card[1] }}
                    </p>
                </article>
            @endforeach
        </div>
    </div>
</section>
