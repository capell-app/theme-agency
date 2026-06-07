@php
    $heading = $section->heading ?? ($heading ?? null);
    $summary = $section->summary ?? ($summary ?? __('capell-theme-nonprofit::generic.hero_summary'));
    $eyebrow = $section->eyebrow ?? ($eyebrow ?? __('capell-theme-nonprofit::generic.hero_label'));
    $actions = $section->actions ?? ($actions ?? []);
    $primaryAction = $actions[0] ?? [
        'label' => __('capell-theme-nonprofit::generic.hero_primary_action'),
        'url' => '#donate',
    ];
    $secondaryAction = $actions[1] ?? [
        'label' => __('capell-theme-nonprofit::generic.hero_secondary_action'),
        'url' => '#volunteer',
    ];
    $imageUrl = $section->mediaUrl ?? ($imageUrl ?? ($image ?? null));
    $imageAlt = $section->mediaAlt ?? ($imageAlt ?? ($heading ?? __('capell-theme-nonprofit::generic.hero_image_alt')));
    $campaignTitle = $section->campaignTitle ?? __('capell-theme-nonprofit::generic.hero_campaign_title');
    $campaignValue = $section->campaignValue ?? __('capell-theme-nonprofit::generic.hero_campaign_value');
    $campaignProgressValue = $section->campaignProgress ?? (int) preg_replace('/[^0-9]/', '', (string) $campaignValue);
    $campaignProgress = max(0, min(100, (int) $campaignProgressValue));
    $campaignDisplayValue = is_string($campaignValue) && $campaignValue !== '' ? $campaignValue : $campaignProgress . '%';
@endphp

<section
    class="theme-section theme-section-hero nonprofit-bg-surface-warm overflow-hidden"
>
    @isset($heading)
        <div
            class="mx-auto grid max-w-6xl gap-0 px-6 py-16 lg:grid-cols-[0.92fr_1.08fr] lg:py-20"
        >
            <div
                class="bg-white px-6 py-8 shadow-xl md:px-8 md:py-10 lg:relative lg:z-10 lg:self-center"
            >
                <p
                    class="nonprofit-text-accent-strong text-xs font-black tracking-[0.18em] uppercase"
                >
                    {{ $eyebrow }}
                </p>
                <h1
                    class="nonprofit-text-ink mt-5 max-w-3xl text-5xl leading-tight font-black tracking-normal"
                >
                    {{ $heading }}
                </h1>
                @if ($summary)
                    <p class="mt-5 max-w-2xl text-lg leading-8 text-slate-600">
                        {{ $summary }}
                    </p>
                @endif

                <div class="mt-7 flex flex-wrap gap-3">
                    <a
                        href="{{ $primaryAction['url'] ?? '#donate' }}"
                        class="nonprofit-bg-primary-deep inline-flex px-5 py-3 text-sm font-black text-white"
                    >
                        {{ $primaryAction['label'] ?? __('capell-theme-nonprofit::generic.hero_primary_action') }}
                    </a>
                    <a
                        href="{{ $secondaryAction['url'] ?? '#volunteer' }}"
                        class="nonprofit-border-accent nonprofit-bg-accent-soft nonprofit-text-ink inline-flex border px-5 py-3 text-sm font-black"
                    >
                        {{ $secondaryAction['label'] ?? __('capell-theme-nonprofit::generic.hero_secondary_action') }}
                    </a>
                </div>

                <div
                    class="nonprofit-border-accent-soft mt-8 grid border-y sm:grid-cols-3"
                >
                    <div class="py-4 sm:pr-4">
                        <p
                            class="nonprofit-text-accent-strong text-xs font-black tracking-[0.14em] uppercase"
                        >
                            {{ __('capell-theme-nonprofit::generic.hero_metric_reach_label') }}
                        </p>
                        <p class="nonprofit-text-ink mt-2 text-3xl font-black">
                            {{ __('capell-theme-nonprofit::generic.hero_metric_reach_value') }}
                        </p>
                    </div>
                    <div
                        class="nonprofit-border-accent-soft border-t py-4 sm:border-t-0 sm:border-l sm:px-4"
                    >
                        <p
                            class="nonprofit-text-accent-strong text-xs font-black tracking-[0.14em] uppercase"
                        >
                            {{ __('capell-theme-nonprofit::generic.hero_metric_action_label') }}
                        </p>
                        <p class="nonprofit-text-ink mt-2 text-3xl font-black">
                            {{ __('capell-theme-nonprofit::generic.hero_metric_action_value') }}
                        </p>
                    </div>
                    <div
                        class="nonprofit-border-accent-soft border-t py-4 sm:border-t-0 sm:border-l sm:pl-4"
                    >
                        <p
                            class="nonprofit-text-accent-strong text-xs font-black tracking-[0.14em] uppercase"
                        >
                            {{ __('capell-theme-nonprofit::generic.hero_metric_trust_label') }}
                        </p>
                        <p class="nonprofit-text-ink mt-2 text-3xl font-black">
                            {{ __('capell-theme-nonprofit::generic.hero_metric_trust_value') }}
                        </p>
                    </div>
                </div>
            </div>

            <div
                class="nonprofit-bg-primary-deep relative overflow-hidden p-5 text-white md:p-7 lg:-ml-6"
            >
                <div
                    class="nonprofit-border-accent absolute -top-12 -right-12 h-40 w-40 rounded-full border-[28px]"
                    aria-hidden="true"
                ></div>

                <div class="relative grid gap-5">
                    @if ($imageUrl)
                        <img
                            src="{{ $imageUrl }}"
                            alt="{{ $imageAlt }}"
                            width="1200"
                            height="675"
                            loading="eager"
                            decoding="async"
                            fetchpriority="high"
                            class="aspect-[16/9] w-full object-cover"
                        />
                    @else
                        <div
                            class="nonprofit-bg-primary-dark grid aspect-[16/9] content-end p-5"
                            aria-hidden="true"
                        >
                            <div class="flex items-end justify-between gap-4">
                                <span
                                    class="nonprofit-bg-accent block h-20 w-20 rounded-full"
                                ></span>
                                <span
                                    class="block h-28 w-28 rounded-full border-[18px] border-white/20"
                                ></span>
                            </div>
                            <div class="mt-5 h-3 w-3/4 bg-white/45"></div>
                            <div class="mt-3 h-3 w-1/2 bg-white/25"></div>
                        </div>
                    @endif

                    <div class="border border-white/15 bg-white/8 p-5">
                        <div
                            class="flex flex-wrap items-start justify-between gap-4"
                        >
                            <div>
                                <p
                                    class="nonprofit-text-accent text-xs font-black tracking-[0.18em] uppercase"
                                >
                                    {{ __('capell-theme-nonprofit::generic.hero_campaign_label') }}
                                </p>
                                <p class="mt-2 text-2xl font-black">
                                    {{ $campaignTitle }}
                                </p>
                            </div>
                            <p
                                class="nonprofit-text-accent text-4xl font-black"
                            >
                                {{ $campaignDisplayValue }}
                            </p>
                        </div>
                        <div
                            class="mt-5 h-3 bg-white/15"
                            role="progressbar"
                            aria-valuemin="0"
                            aria-valuemax="100"
                            aria-valuenow="{{ $campaignProgress }}"
                            aria-label="{{ $campaignTitle }}"
                        >
                            <div
                                class="nonprofit-bg-accent h-full"
                                style="width: {{ $campaignProgress }}%"
                            ></div>
                        </div>
                        <div
                            class="nonprofit-text-on-dark-muted mt-5 grid gap-3 text-sm font-bold sm:grid-cols-3"
                        >
                            <span class="bg-white/10 px-3 py-2">
                                {{ __('capell-theme-nonprofit::generic.supporter_step_one') }}
                            </span>
                            <span class="bg-white/10 px-3 py-2">
                                {{ __('capell-theme-nonprofit::generic.supporter_step_two') }}
                            </span>
                            <span class="bg-white/10 px-3 py-2">
                                {{ __('capell-theme-nonprofit::generic.supporter_step_three') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endisset
</section>
