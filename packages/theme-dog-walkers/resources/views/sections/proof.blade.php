@php
    $proofs = $section->items ?? __('capell-theme-dog-walkers::generic.proof_defaults');
    $mobileLayout = $section->mobileLayout ?? 'carousel';
    $useMobileCarousel = $mobileLayout !== 'stack';
@endphp

<section class="theme-section theme-section-proof bg-[#06120f] text-white">
    <div class="mx-auto max-w-5xl px-6 py-14">
        <div class="grid gap-6 md:grid-cols-[0.7fr_1fr] md:items-end">
            <div>
                <p
                    class="text-xs font-black tracking-[0.18em] text-[#fb923c] uppercase"
                >
                    {{ __('capell-theme-dog-walkers::generic.proof_label') }}
                </p>
                <h2 class="mt-4 text-4xl font-black tracking-tight text-white">
                    {{ $heading ?? $section->heading }}
                </h2>
            </div>

            @if (($summary ?? $section->summary ?? null) !== null)
                <p
                    class="max-w-2xl text-lg leading-8 text-slate-300 md:justify-self-end"
                >
                    {{ $summary ?? $section->summary }}
                </p>
            @endif
        </div>

        <div class="@container mt-8">
            <x-capell::section.repeatable-carousel
                carousel-id="dog-walkers-proof"
                @class(['@2xl:hidden' => $useMobileCarousel])
                :enabled="$useMobileCarousel"
            >
                @foreach ($proofs as $index => $proof)
                    <article
                        @class([
                            'overflow-hidden border border-white/15 bg-white/8 transition hover:-translate-y-1 hover:border-[#fb923c] hover:bg-white/12',
                            'swiper-slide h-auto' => $useMobileCarousel,
                        ])
                    >
                        <div class="p-5">
                            <p
                                class="text-xs font-black tracking-[0.18em] text-[#99f6e4] uppercase"
                            >
                                {{ $proof['label'] ?? $proof['name'] ?? __('capell-theme-dog-walkers::generic.proof_signal') }}
                            </p>
                            <p class="mt-3 text-4xl font-black text-white">
                                {{ $proof['metric'] ?? '' }}
                            </p>
                            <p class="mt-3 text-sm leading-6 text-slate-300">
                                {{ $proof['summary'] ?? $proof['description'] ?? '' }}
                            </p>
                            <div
                                class="mt-5 flex flex-wrap gap-2 text-xs font-black"
                            >
                                <span
                                    class="bg-[#ccfbf1] px-3 py-1 text-[#115e59]"
                                >
                                    {{ __('capell-theme-dog-walkers::generic.arrival_signal') }}
                                </span>
                                <span
                                    class="bg-[#ffedd5] px-3 py-1 text-[#9a3412]"
                                >
                                    {{ __('capell-theme-dog-walkers::generic.completion_signal') }}
                                </span>
                            </div>
                        </div>

                        @if ($loop->first)
                            <div
                                class="border-t border-white/10 bg-[#0b211b] p-5 sm:border-t-0 sm:border-l"
                            >
                                <div
                                    class="flex items-center justify-between gap-4"
                                >
                                    <p
                                        class="text-xs font-black text-[#99f6e4] uppercase"
                                    >
                                        {{ __('capell-theme-dog-walkers::generic.route_board_signal') }}
                                    </p>
                                    <span
                                        class="rounded-full bg-[#fb923c] px-2 py-1 text-xs font-black text-[#17211c]"
                                    >
                                        {{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}
                                    </span>
                                </div>

                                <div
                                    class="mt-5 grid grid-cols-[1fr_2rem_1fr] items-center gap-2"
                                    aria-hidden="true"
                                >
                                    <span class="h-4 bg-[#99f6e4]"></span>
                                    <span
                                        class="h-4 rounded-full bg-[#fb923c]"
                                    ></span>
                                    <span class="h-4 bg-white/25"></span>
                                </div>

                                <div
                                    class="mt-4 grid grid-cols-3 gap-2"
                                    aria-hidden="true"
                                >
                                    <span class="h-12 bg-white/15"></span>
                                    <span class="h-12 bg-white/25"></span>
                                    <span class="h-12 bg-white/15"></span>
                                </div>

                                <p
                                    class="mt-5 text-xs font-black text-[#fb923c] uppercase"
                                >
                                    {{ __('capell-theme-dog-walkers::generic.coverage_signal') }}
                                </p>
                            </div>
                        @endif
                    </article>
                @endforeach
            </x-capell::section.repeatable-carousel>

            @if ($useMobileCarousel)
                <div class="hidden gap-4 @2xl:grid @2xl:grid-cols-3">
                    @foreach ($proofs as $index => $proof)
                        <article
                            class="{{ $loop->first ? '@2xl:col-span-2 @2xl:grid @2xl:grid-cols-[0.9fr_1.1fr]' : '' }} overflow-hidden border border-white/15 bg-white/8 transition hover:-translate-y-1 hover:border-[#fb923c] hover:bg-white/12"
                        >
                            <div class="p-5">
                                <p
                                    class="text-xs font-black tracking-[0.18em] text-[#99f6e4] uppercase"
                                >
                                    {{ $proof['label'] ?? $proof['name'] ?? __('capell-theme-dog-walkers::generic.proof_signal') }}
                                </p>
                                <p class="mt-3 text-4xl font-black text-white">
                                    {{ $proof['metric'] ?? '' }}
                                </p>
                                <p
                                    class="mt-3 text-sm leading-6 text-slate-300"
                                >
                                    {{ $proof['summary'] ?? $proof['description'] ?? '' }}
                                </p>
                                <div
                                    class="mt-5 flex flex-wrap gap-2 text-xs font-black"
                                >
                                    <span
                                        class="bg-[#ccfbf1] px-3 py-1 text-[#115e59]"
                                    >
                                        {{ __('capell-theme-dog-walkers::generic.arrival_signal') }}
                                    </span>
                                    <span
                                        class="bg-[#ffedd5] px-3 py-1 text-[#9a3412]"
                                    >
                                        {{ __('capell-theme-dog-walkers::generic.completion_signal') }}
                                    </span>
                                </div>
                            </div>

                            @if ($loop->first)
                                <div
                                    class="border-t border-white/10 bg-[#0b211b] p-5 @2xl:border-t-0 @2xl:border-l"
                                >
                                    <div
                                        class="flex items-center justify-between gap-4"
                                    >
                                        <p
                                            class="text-xs font-black text-[#99f6e4] uppercase"
                                        >
                                            {{ __('capell-theme-dog-walkers::generic.route_board_signal') }}
                                        </p>
                                        <span
                                            class="rounded-full bg-[#fb923c] px-2 py-1 text-xs font-black text-[#17211c]"
                                        >
                                            {{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}
                                        </span>
                                    </div>

                                    <div
                                        class="mt-5 grid grid-cols-[1fr_2rem_1fr] items-center gap-2"
                                        aria-hidden="true"
                                    >
                                        <span class="h-4 bg-[#99f6e4]"></span>
                                        <span
                                            class="h-4 rounded-full bg-[#fb923c]"
                                        ></span>
                                        <span class="h-4 bg-white/25"></span>
                                    </div>

                                    <div
                                        class="mt-4 grid grid-cols-3 gap-2"
                                        aria-hidden="true"
                                    >
                                        <span class="h-12 bg-white/15"></span>
                                        <span class="h-12 bg-white/25"></span>
                                        <span class="h-12 bg-white/15"></span>
                                    </div>

                                    <p
                                        class="mt-5 text-xs font-black text-[#fb923c] uppercase"
                                    >
                                        {{ __('capell-theme-dog-walkers::generic.coverage_signal') }}
                                    </p>
                                </div>
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>
