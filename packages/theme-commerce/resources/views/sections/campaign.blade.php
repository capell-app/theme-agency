@php
    $items = $section->items ?? $section->offers ?? [];
@endphp

<section class="theme-section theme-section-campaign bg-white">
    <div class="mx-auto max-w-6xl px-6 py-16">
        <div class="grid gap-8 lg:grid-cols-[0.72fr_1.28fr] lg:items-start">
            <div>
                <p
                    class="text-xs font-black tracking-[0.16em] text-[var(--retail-primary)] uppercase"
                >
                    {{ __('capell-theme-commerce::generic.campaign_label') }}
                </p>
                <h2
                    class="mt-3 text-4xl font-black tracking-normal text-[var(--retail-ink)]"
                >
                    {{ $section->heading }}
                </h2>
                @if ($section->summary)
                    <p class="mt-4 text-base leading-7 text-stone-600">
                        {{ $section->summary }}
                    </p>
                @endif
            </div>

            <div class="grid gap-3 md:grid-cols-2">
                @forelse ($items as $item)
                    <article
                        class="rounded-2xl border border-[var(--retail-line)] bg-[var(--retail-surface)] p-5 text-[var(--retail-ink)]"
                    >
                        <p
                            class="text-xs font-black text-[var(--retail-primary)] uppercase"
                        >
                            {{ $item['phase'] ?? $item['type'] ?? __('capell-theme-commerce::generic.campaign_ready') }}
                        </p>
                        <h3 class="mt-3 text-2xl font-black">
                            {{ $item['title'] ?? '' }}
                        </h3>
                        @if (! empty($item['summary']))
                            <p class="mt-3 text-sm leading-6 text-stone-600">
                                {{ $item['summary'] }}
                            </p>
                        @endif

                        @if (($item['code'] ?? null) || ($item['discount'] ?? null))
                            <p
                                class="mt-4 inline-flex rounded-full bg-white px-3 py-1 text-sm font-black text-[var(--retail-accent)]"
                            >
                                {{ $item['code'] ?? $item['discount'] }}
                            </p>
                        @endif
                    </article>
                @empty
                    <div
                        class="rounded-2xl border border-dashed border-[var(--retail-line)] p-6 md:col-span-2"
                    >
                        <p
                            class="text-sm font-black text-[var(--retail-primary)] uppercase"
                        >
                            {{ __('capell-theme-commerce::generic.campaign_empty_title') }}
                        </p>
                        <p class="mt-3 text-sm leading-6 text-stone-600">
                            {{ __('capell-theme-commerce::generic.campaign_empty_summary') }}
                        </p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</section>
