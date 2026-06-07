@php
    $heading ??= $section->heading ?? null;
    $summary ??= $section->summary ?? null;
    $campaignsEnabled = $campaignStudioAvailable ?? false;
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
                    {{ $summary ?? ($campaignsEnabled ? __('capell-theme-nonprofit::generic.campaigns_connected') : __('capell-theme-nonprofit::generic.campaigns_static')) }}
                </p>
            </div>
        @endisset

        <div class="mt-10 grid gap-4 md:grid-cols-[1.1fr_0.9fr_0.9fr]">
            @foreach ($campaignItems as $campaign)
                @php
                    $progress = max(0, min(100, (int) ($campaign['progress'] ?? $campaign['percent'] ?? 0)));
                    $goal = $campaign['goal'] ?? $campaign['target'] ?? null;
                    $raised = $campaign['raised'] ?? $campaign['current'] ?? null;
                    $action = $campaign['action'] ?? null;
                @endphp

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
                    @if ($campaignsEnabled && ($progress > 0 || $goal || $raised || $action))
                        <div class="mt-5">
                            @if ($progress > 0)
                                <div
                                    class="{{ $loop->first ? 'bg-white/15' : 'nonprofit-bg-primary-soft' }} h-3 overflow-hidden"
                                    role="progressbar"
                                    aria-label="{{ __('capell-theme-nonprofit::generic.campaign_progress_label') }}"
                                    aria-valuemin="0"
                                    aria-valuemax="100"
                                    aria-valuenow="{{ $progress }}"
                                >
                                    <div
                                        class="nonprofit-bg-accent h-full"
                                        style="width: {{ $progress }}%"
                                    ></div>
                                </div>
                            @endif

                            @if ($goal || $raised)
                                <p
                                    class="{{ $loop->first ? 'nonprofit-text-on-dark-muted' : 'nonprofit-text-muted' }} mt-3 text-xs font-black tracking-[0.12em] uppercase"
                                >
                                    {{ collect([$raised, $goal])->filter()->join(' / ') }}
                                </p>
                            @endif

                            @if (is_array($action) && ($action['url'] ?? null) && ($action['label'] ?? null))
                                <a
                                    href="{{ $action['url'] }}"
                                    class="{{ $loop->first ? 'nonprofit-text-ink bg-white' : 'nonprofit-bg-primary-deep nonprofit-text-on-dark' }} mt-4 inline-flex px-4 py-2 text-sm font-black"
                                >
                                    {{ $action['label'] }}
                                </a>
                            @endif
                        </div>
                    @endif
                </article>
            @endforeach
        </div>
    </div>
</section>
