@php
    $heading ??= $section->heading ?? null;
    $summary ??= $section->summary ?? null;
    $campaignItems = $section->items ?? [
        [
            'label' => __('capell-theme-nonprofit::generic.campaign_card_awareness_label'),
            'title' => __('capell-theme-nonprofit::generic.campaign_card_awareness_title'),
            'summary' => __('capell-theme-nonprofit::generic.campaign_card_awareness'),
        ],
        [
            'label' => __('capell-theme-nonprofit::generic.campaign_card_donor_label'),
            'title' => __('capell-theme-nonprofit::generic.campaign_card_donor_title'),
            'summary' => __('capell-theme-nonprofit::generic.campaign_card_donor'),
        ],
        [
            'label' => __('capell-theme-nonprofit::generic.campaign_card_community_label'),
            'title' => __('capell-theme-nonprofit::generic.campaign_card_community_title'),
            'summary' => __('capell-theme-nonprofit::generic.campaign_card_community'),
        ],
    ];
@endphp

<section class="theme-section theme-section-campaigns bg-white">
    <div class="mx-auto max-w-5xl px-6 py-14">
        @isset($heading)
            <div class="grid gap-5 md:grid-cols-[0.7fr_1fr] md:items-end">
                <div>
                    <p
                        class="nonprofit-text-accent-strong text-xs font-black tracking-[0.18em] uppercase"
                    >
                        {{ __('capell-theme-nonprofit::generic.campaigns_label') }}
                    </p>
                    <h2
                        class="nonprofit-text-ink mt-4 text-4xl font-black tracking-tight"
                    >
                        {{ $heading }}
                    </h2>
                </div>
                <p class="max-w-2xl text-lg text-slate-600 md:justify-self-end">
                    {{ $summary ?? (($campaignStudioAvailable ?? false) ? __('capell-theme-nonprofit::generic.campaigns_connected') : __('capell-theme-nonprofit::generic.campaigns_static')) }}
                </p>
            </div>
        @endisset

        <div class="mt-10 grid gap-4 md:grid-cols-[1.1fr_0.9fr_0.9fr]">
            @foreach ($campaignItems as $campaign)
                <article
                    class="{{ $loop->first ? 'nonprofit-bg-primary-deep text-white' : 'nonprofit-bg-surface-warm nonprofit-text-ink' }} nonprofit-border-accent border p-5"
                >
                    <p
                        class="{{ $loop->first ? 'nonprofit-text-accent' : 'nonprofit-text-accent-strong' }} text-xs font-black tracking-[0.18em] uppercase"
                    >
                        {{ $campaign['label'] }}
                    </p>
                    <h3 class="mt-3 text-xl font-black">
                        {{ $campaign['title'] ?? __('capell-theme-nonprofit::generic.campaigns_label') }}
                    </h3>
                    <p
                        class="{{ $loop->first ? 'nonprofit-text-on-dark-muted' : 'text-slate-600' }} mt-3 text-sm leading-6"
                    >
                        {{ $campaign['summary'] ?? $campaign['copy'] ?? __('capell-theme-nonprofit::generic.campaigns_static') }}
                    </p>
                </article>
            @endforeach
        </div>
    </div>
</section>
