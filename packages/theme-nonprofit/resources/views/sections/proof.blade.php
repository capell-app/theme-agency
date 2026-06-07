@php
    $items = $section->items ?? [];
@endphp

<section
    class="theme-section theme-section-proof nonprofit-bg-primary-deep text-white"
>
    <div class="mx-auto max-w-6xl px-6 py-16 lg:py-20">
        <div class="grid gap-8 lg:grid-cols-[0.64fr_1.36fr] lg:items-start">
            <div>
                <p class="nonprofit-text-accent text-xs font-black uppercase">
                    {{ __('capell-theme-nonprofit::generic.proof_label') }}
                </p>
                <h2 class="mt-4 text-4xl font-black tracking-tight">
                    {{ $section->heading }}
                </h2>
                @if ($section->summary ?? null)
                    <p
                        class="nonprofit-text-on-dark-muted mt-4 max-w-md text-base leading-7"
                    >
                        {{ $section->summary }}
                    </p>
                @endif
            </div>

            <div class="grid gap-4 md:grid-cols-4">
                @foreach ($items as $item)
                    <article
                        class="{{ $loop->first ? 'md:col-span-4' : 'md:col-span-2' }} border border-white/10 bg-white/[0.06]"
                    >
                        <div
                            class="{{ $loop->first ? 'md:grid-cols-[0.44fr_1fr_0.42fr]' : 'md:grid-cols-[0.72fr_1fr]' }} grid"
                        >
                            <div class="nonprofit-bg-primary-dark p-5">
                                <p
                                    class="nonprofit-text-accent text-xs font-black uppercase"
                                >
                                    {{ __('capell-theme-nonprofit::generic.supporter_metric_label') }}
                                </p>
                                <p
                                    class="mt-5 font-mono text-5xl font-black text-white"
                                >
                                    {{ $item['metric'] ?? str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                </p>
                            </div>

                            <div class="p-5">
                                <p
                                    class="nonprofit-text-accent text-xs font-black uppercase"
                                >
                                    {{ $item['name'] ?? $item['title'] ?? __('capell-theme-nonprofit::generic.impact_signal') }}
                                </p>
                                <p
                                    class="nonprofit-text-on-dark-muted mt-4 text-sm leading-7"
                                >
                                    {{ $item['summary'] ?? $item['description'] ?? '' }}
                                </p>
                            </div>

                            @if ($loop->first)
                                <div
                                    class="hidden border-l border-white/10 p-5 md:block"
                                    aria-hidden="true"
                                >
                                    <div class="space-y-3">
                                        <span
                                            class="nonprofit-bg-accent block h-2 w-20"
                                        ></span>
                                        <span
                                            class="block h-2 w-28 bg-white/25"
                                        ></span>
                                        <span
                                            class="nonprofit-bg-primary block h-2 w-16"
                                        ></span>
                                    </div>
                                    <div class="mt-8 grid grid-cols-3 gap-2">
                                        <span class="h-9 bg-white/15"></span>
                                        <span
                                            class="nonprofit-bg-accent h-9"
                                        ></span>
                                        <span
                                            class="nonprofit-bg-primary h-9 opacity-70"
                                        ></span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
</section>
