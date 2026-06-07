@php
    $items ??= $section->items ?? [];
    $heading ??= $section->heading ?? __('capell-theme-nonprofit::generic.donation_impact_label');
    $paymentsEnabled = $paymentsAvailable ?? false;
    $campaignsEnabled = $campaignStudioAvailable ?? false;
    $summary ??= $section->summary ?? ($paymentsEnabled ? __('capell-theme-nonprofit::generic.donation_impact_payments') : ($campaignsEnabled ? __('capell-theme-nonprofit::generic.donation_impact_connected') : __('capell-theme-nonprofit::generic.donation_impact_static')));
    $primaryAction = $section->primaryAction ?? ['label' => __('capell-theme-nonprofit::generic.donation_primary_action'), 'url' => '#donate'];
    $secondaryAction = $section->secondaryAction ?? ['label' => __('capell-theme-nonprofit::generic.donation_secondary_action'), 'url' => '#impact'];
@endphp

<section
    class="theme-section theme-section-donation-impact nonprofit-bg-surface"
>
    <div class="mx-auto max-w-6xl px-6 py-16">
        <div class="grid gap-5 md:grid-cols-[0.7fr_1fr] md:items-end">
            <div>
                <p
                    class="nonprofit-text-primary text-xs font-black tracking-[0.16em] uppercase"
                >
                    {{ __('capell-theme-nonprofit::generic.donation_impact_label') }}
                </p>
                <h2 class="nonprofit-text-ink mt-3 text-4xl font-black">
                    {{ $heading }}
                </h2>
            </div>
            <div>
                <p class="nonprofit-text-muted text-base leading-8">
                    {{ $summary }}
                </p>
                @if ($paymentsEnabled)
                    <div class="mt-5 flex flex-wrap gap-3">
                        <a
                            href="{{ $primaryAction['url'] ?? '#donate' }}"
                            class="nonprofit-bg-primary-deep nonprofit-text-on-dark inline-flex px-5 py-3 text-sm font-black"
                        >
                            {{ $primaryAction['label'] ?? __('capell-theme-nonprofit::generic.donation_primary_action') }}
                        </a>
                        <a
                            href="{{ $secondaryAction['url'] ?? '#impact' }}"
                            class="nonprofit-border-accent nonprofit-bg-accent-soft nonprofit-text-accent-strong inline-flex border px-5 py-3 text-sm font-black"
                        >
                            {{ $secondaryAction['label'] ?? __('capell-theme-nonprofit::generic.donation_secondary_action') }}
                        </a>
                    </div>
                @endif
            </div>
        </div>
        <div class="mt-10 grid gap-4 md:grid-cols-3">
            @forelse ($items as $item)
                <article
                    class="nonprofit-border-primary-soft border bg-white p-5"
                >
                    <p
                        class="nonprofit-text-accent text-xs font-black uppercase"
                    >
                        {{ $item['metric'] ?? __('capell-theme-nonprofit::generic.impact_signal') }}
                    </p>
                    <h3 class="nonprofit-text-ink mt-2 text-xl font-black">
                        {{ $item['title'] ?? __('capell-theme-nonprofit::generic.donor_signal') }}
                    </h3>
                    <p class="nonprofit-text-muted mt-2 text-sm leading-6">
                        {{ $item['summary'] ?? __('capell-theme-nonprofit::generic.donation_impact_ready') }}
                    </p>
                </article>
            @empty
                <article
                    class="nonprofit-border-primary-soft border border-dashed bg-white p-6"
                >
                    <h3 class="nonprofit-text-ink text-lg font-black">
                        {{ __('capell-theme-nonprofit::generic.premium_layout_ready') }}
                    </h3>
                    <p class="nonprofit-text-muted mt-2 text-sm">
                        {{ __('capell-theme-nonprofit::generic.premium_layout_empty') }}
                    </p>
                </article>
            @endforelse
        </div>
    </div>
</section>
