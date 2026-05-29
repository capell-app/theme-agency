<section class="theme-section theme-section-campaigns bg-white">
    <div class="mx-auto max-w-5xl px-6 py-14">
        @isset($heading)
            <div class="grid gap-5 md:grid-cols-[0.7fr_1fr] md:items-end">
                <div>
                    <p
                        class="text-xs font-black tracking-[0.18em] text-[#b45309] uppercase"
                    >
                        {{ __('capell-theme-nonprofit::generic.campaigns_label') }}
                    </p>
                    <h2
                        class="mt-4 text-4xl font-black tracking-tight text-[#132014]"
                    >
                        {{ $heading }}
                    </h2>
                </div>
                <p class="max-w-2xl text-lg text-slate-600 md:justify-self-end">
                    {{ $campaignStudioAvailable ?? false ? __('capell-theme-nonprofit::generic.campaigns_connected') : __('capell-theme-nonprofit::generic.campaigns_static') }}
                </p>
            </div>
        @endisset

        <div class="mt-10 grid gap-4 md:grid-cols-[1.1fr_0.9fr_0.9fr]">
            @foreach ([['label' => 'Campaigns', 'title' => 'Awareness', 'copy' => __('capell-theme-nonprofit::generic.campaign_card_awareness')], ['label' => 'Outreach', 'title' => 'Donor Path', 'copy' => __('capell-theme-nonprofit::generic.campaign_card_donor')], ['label' => 'Retention', 'title' => 'Community', 'copy' => __('capell-theme-nonprofit::generic.campaign_card_community')]] as $campaign)
                <article
                    class="{{ $loop->first ? 'bg-[#12351f] text-white' : 'bg-[#fff8e7] text-[#132014]' }} border border-[#facc15] p-5"
                >
                    <p
                        class="{{ $loop->first ? 'text-[#fde047]' : 'text-[#b45309]' }} text-xs font-black tracking-[0.18em] uppercase"
                    >
                        {{ $campaign['label'] }}
                    </p>
                    <h3 class="mt-3 text-xl font-black">
                        {{ $campaign['title'] }}
                    </h3>
                    <p
                        class="{{ $loop->first ? 'text-emerald-50' : 'text-slate-600' }} mt-3 text-sm leading-6"
                    >
                        {{ $campaign['copy'] }}
                    </p>
                </article>
            @endforeach
        </div>
    </div>
</section>
