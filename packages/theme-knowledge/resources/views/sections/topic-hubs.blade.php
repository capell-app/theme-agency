<section class="theme-section theme-section-topic-hubs bg-white">
    <div class="mx-auto max-w-5xl px-6 py-14">
        @isset($heading)
            <p
                class="text-xs font-black tracking-[0.18em] text-[#f59e0b] uppercase"
            >
                {{ __('capell-theme-knowledge::generic.topic_hubs_label') }}
            </p>
            <h2 class="mt-4 text-4xl font-black tracking-tight text-[#111827]">
                {{ $heading }}
            </h2>
        @endisset

        <div class="mt-8 grid gap-4 md:grid-cols-4">
            @foreach ([
                          __('capell-theme-knowledge::generic.topic_hub_strategy'),
                          __('capell-theme-knowledge::generic.topic_hub_design'),
                          __('capell-theme-knowledge::generic.topic_hub_operations'),
                          __('capell-theme-knowledge::generic.topic_hub_growth'),
                      ] as $topic)
                <article class="border border-[#dbeafe] bg-[#eff6ff] p-5">
                    <p class="font-mono text-xs font-black text-[#1d4ed8]">
                        {{ __('capell-theme-knowledge::generic.topic_signal') }}
                    </p>
                    <h3 class="mt-3 text-lg font-black text-[#111827]">
                        {{ $topic }}
                    </h3>
                    <span
                        class="mt-5 block h-1 bg-[#f59e0b]"
                        aria-hidden="true"
                    ></span>
                </article>
            @endforeach
        </div>
    </div>
</section>
