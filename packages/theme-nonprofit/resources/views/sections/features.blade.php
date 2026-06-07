@php
    $features = $section->features ?? $section->items ?? [];
@endphp

<section class="theme-section theme-section-features nonprofit-bg-surface-warm">
    <div class="mx-auto max-w-5xl px-6 py-14">
        <div class="grid gap-6 md:grid-cols-[0.82fr_1fr] md:items-end">
            <div>
                <p
                    class="nonprofit-text-accent-strong text-xs font-black tracking-[0.18em] uppercase"
                >
                    {{ __('capell-theme-nonprofit::generic.impact_paths_label') }}
                </p>
                <h2
                    class="nonprofit-text-ink mt-4 text-4xl font-black tracking-tight"
                >
                    {{ $section->heading }}
                </h2>
            </div>

            @if ($section->summary ?? null)
                <p class="max-w-2xl text-lg text-slate-600 md:justify-self-end">
                    {{ $section->summary }}
                </p>
            @endif
        </div>

        <div class="mt-10 grid gap-5 md:grid-cols-12">
            @foreach ($features as $feature)
                <article
                    class="nonprofit-border-accent group border bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-xl md:col-span-4 even:md:translate-y-8"
                >
                    <div class="nonprofit-bg-primary-deep mb-5 p-4 text-white">
                        <div class="flex items-end justify-between gap-4">
                            <div>
                                <p
                                    class="nonprofit-text-accent text-[0.65rem] font-black tracking-[0.18em] uppercase"
                                >
                                    {{ $feature['type'] ?? __('capell-theme-nonprofit::generic.impact_signal') }}
                                </p>
                                <p class="mt-8 text-4xl font-black">
                                    {{ $loop->iteration * 12 }}%
                                </p>
                            </div>
                            <span
                                class="nonprofit-border-accent block h-16 w-16 rounded-full border-8"
                                aria-hidden="true"
                            ></span>
                        </div>
                    </div>

                    <p
                        class="nonprofit-text-accent-strong text-xs font-black tracking-[0.18em] uppercase"
                    >
                        {{ $feature['type'] ?? __('capell-theme-nonprofit::generic.impact_signal') }}
                    </p>
                    <h3 class="nonprofit-text-ink mt-3 text-lg font-black">
                        {{ $feature['title'] }}
                    </h3>
                    <p class="mt-3 text-sm leading-6 text-slate-600">
                        {{ $feature['description'] ?? $feature['summary'] ?? '' }}
                    </p>
                    <div class="mt-5 flex flex-wrap gap-2 text-xs font-bold">
                        <span
                            class="nonprofit-bg-accent-soft nonprofit-text-accent-strong px-3 py-1"
                        >
                            {{ __('capell-theme-nonprofit::generic.donor_signal') }}
                        </span>
                        <span
                            class="nonprofit-bg-primary-soft nonprofit-text-primary px-3 py-1"
                        >
                            {{ __('capell-theme-nonprofit::generic.action_signal') }}
                        </span>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
