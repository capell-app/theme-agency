<section class="theme-section theme-section-resources bg-white">
    <div class="mx-auto max-w-5xl px-6 py-14">
        @isset($heading)
            <div class="grid gap-5 md:grid-cols-[0.7fr_1fr] md:items-end">
                <div>
                    <p
                        class="text-xs font-black tracking-[0.18em] text-[#14b8a6] uppercase"
                    >
                        {{ __('capell-theme-education::generic.learning_resources_label') }}
                    </p>
                    <h2
                        class="mt-4 text-4xl font-black tracking-tight text-[#0f172a]"
                    >
                        {{ $heading }}
                    </h2>
                </div>
                <p class="max-w-2xl text-lg text-slate-600 md:justify-self-end">
                    {{ $blogAvailable ?? false ? __('capell-theme-education::generic.resources_connected') : __('capell-theme-education::generic.resources_static') }}
                </p>
            </div>
        @endisset

        <div class="mt-10 grid gap-4 md:grid-cols-3">
            @foreach (['Guide', 'Worksheet', 'Replay'] as $resourceType)
                <article class="border border-slate-200 bg-white p-5 shadow-sm">
                    <p
                        class="text-xs font-black tracking-[0.18em] text-[#4338ca] uppercase"
                    >
                        {{ $resourceType }}
                    </p>
                    <h3 class="mt-3 text-lg font-black text-[#0f172a]">
                        {{ __('capell-theme-education::generic.resource_card_title') }}
                    </h3>
                    <p class="mt-3 text-sm leading-6 text-slate-600">
                        {{ __('capell-theme-education::generic.resource_card_summary') }}
                    </p>
                    <p class="mt-5 text-xs font-black text-[#0f766e]">
                        {{ __('capell-theme-education::generic.resource_meta_signal') }}
                    </p>
                </article>
            @endforeach
        </div>
    </div>
</section>
