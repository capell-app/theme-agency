<section class="theme-section theme-section-impact bg-[#12351f] text-white">
    <div class="mx-auto max-w-5xl px-6 py-14">
        @isset($heading)
            <p
                class="text-xs font-black tracking-[0.18em] text-[#fde047] uppercase"
            >
                {{ __('capell-theme-nonprofit::generic.impact_label') }}
            </p>
            <h2 class="mt-4 max-w-2xl text-4xl font-black tracking-tight">
                {{ $heading }}
            </h2>
        @endisset

        <div class="mt-10 grid gap-4 md:grid-cols-3">
            @foreach ([['metric' => '12k', 'label' => __('capell-theme-nonprofit::generic.impact_metric_people')], ['metric' => '84%', 'label' => __('capell-theme-nonprofit::generic.impact_metric_progress')], ['metric' => '31', 'label' => __('capell-theme-nonprofit::generic.impact_metric_partners')]] as $item)
                <article class="border border-white/15 bg-white/10 p-6">
                    <p class="text-4xl font-black text-[#fde047]">
                        {{ $item['metric'] }}
                    </p>
                    <p class="mt-3 text-sm leading-6 font-bold text-emerald-50">
                        {{ $item['label'] }}
                    </p>
                </article>
            @endforeach
        </div>
    </div>
</section>
