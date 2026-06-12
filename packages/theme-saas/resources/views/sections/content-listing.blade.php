@if (in_array($section->variant ?? null, ['gallery', 'pathways', 'spotlight'], true))
    @include('capell-foundation-theme::theme.sections.content-listing', ['section' => $section])
@else
    <section class="saas-directory bg-white">
        <div class="px-6">
            <div class="grid gap-4 md:grid-cols-[0.75fr_1fr] md:items-end">
                <div>
                    <p
                        class="text-xs font-black tracking-[0.18em] text-cyan-700 uppercase"
                    >
                        {{ __('capell-theme-saas::generic.resource_pipeline_label') }}
                    </p>
                    <h2
                        class="mt-4 text-4xl font-black tracking-tight text-slate-950"
                    >
                        {{ $section->heading }}
                    </h2>
                </div>
                @if ($section->summary)
                    <p class="max-w-2xl text-lg md:justify-self-end">
                        {{ $section->summary }}
                    </p>
                @endif
            </div>

            <div
                class="theme-carousel relative mt-10"
                data-carousel="saas-directory"
            >
                <div
                    class="flex snap-x snap-mandatory [scrollbar-width:none] gap-4 overflow-x-auto pr-6 pb-2 md:grid md:grid-cols-3 md:gap-6 [&::-webkit-scrollbar]:hidden"
                    data-carousel-track
                >
                    @foreach ($section->items as $item)
                        <a
                            href="{{ $item['url'] ?? '#' }}"
                            class="group min-w-[260px] snap-start overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:border-blue-300 hover:shadow-xl md:min-w-0"
                        >
                            <span
                                class="block bg-slate-950 p-4 text-white"
                                aria-hidden="true"
                            >
                                <span class="flex items-center justify-between">
                                    <span
                                        class="h-2 w-20 rounded-full bg-cyan-300"
                                    ></span>
                                    <span
                                        class="h-2 w-8 rounded-full bg-blue-500"
                                    ></span>
                                </span>
                                <span class="mt-7 block space-y-2">
                                    <span
                                        class="block h-2 rounded-full bg-white/45"
                                    ></span>
                                    <span
                                        class="block h-2 w-2/3 rounded-full bg-white/25"
                                    ></span>
                                </span>
                                <span class="mt-5 grid grid-cols-3 gap-2">
                                    <span
                                        class="h-8 rounded-md bg-cyan-400/30"
                                    ></span>
                                    <span
                                        class="h-8 rounded-md bg-blue-500/40"
                                    ></span>
                                    <span
                                        class="h-8 rounded-md bg-white/10"
                                    ></span>
                                </span>
                            </span>

                            <span class="block p-6">
                                @if ($item['type'] ?? null)
                                    <p
                                        class="mb-4 text-xs font-black tracking-widest text-cyan-700 uppercase"
                                    >
                                        {{ $item['type'] }}
                                    </p>
                                @endif

                                <h3
                                    class="text-xl font-black group-hover:text-blue-700"
                                >
                                    {{ $item['title'] }}
                                </h3>
                                <p class="mt-3 text-sm">
                                    {{ $item['summary'] ?? '' }}
                                </p>
                                <span
                                    class="mt-5 inline-flex rounded-full bg-slate-950 px-3 py-1.5 text-xs font-black text-white"
                                >
                                    {{ __('capell-theme-saas::generic.open_resource_label') }}
                                </span>
                            </span>
                        </a>
                    @endforeach

                    @if (empty($section->items))
                        <div
                            class="saas-empty-pipeline rounded-lg p-6 md:col-span-3"
                        >
                            <div
                                class="grid gap-6 md:grid-cols-[0.85fr_1.15fr] md:items-center"
                            >
                                <div>
                                    <p
                                        class="text-xs font-black tracking-[0.18em] text-cyan-300 uppercase"
                                    >
                                        {{ __('capell-theme-saas::generic.empty_pipeline_label') }}
                                    </p>
                                    <p
                                        class="mt-3 max-w-md text-lg font-black text-white"
                                    >
                                        {{ __('capell-theme-saas::generic.empty_resource_pipeline') }}
                                    </p>
                                </div>
                                <div
                                    class="grid gap-3 rounded-lg border border-white/10 bg-white/[0.04] p-4"
                                    aria-hidden="true"
                                >
                                    <span
                                        class="h-2 w-28 rounded-full bg-cyan-300"
                                    ></span>
                                    <span class="grid grid-cols-3 gap-2">
                                        <span
                                            class="h-16 rounded-md bg-cyan-300/20"
                                        ></span>
                                        <span
                                            class="h-16 rounded-md bg-emerald-300/20"
                                        ></span>
                                        <span
                                            class="h-16 rounded-md bg-amber-300/20"
                                        ></span>
                                    </span>
                                    <span
                                        class="h-2 w-2/3 rounded-full bg-white/25"
                                    ></span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <button
                    type="button"
                    class="theme-carousel-button carousel-prev absolute top-1/2 left-2 hidden -translate-y-1/2 rounded-full border border-slate-200 bg-white p-2 text-sm font-semibold text-slate-700 shadow-md"
                    aria-label="{{ __('capell-theme-saas::generic.carousel_previous') }}"
                    data-carousel-prev
                >
                    ‹
                </button>
                <button
                    type="button"
                    class="theme-carousel-button carousel-next absolute top-1/2 right-2 hidden -translate-y-1/2 rounded-full border border-slate-200 bg-white p-2 text-sm font-semibold text-slate-700 shadow-md"
                    aria-label="{{ __('capell-theme-saas::generic.carousel_next') }}"
                    data-carousel-next
                >
                    ›
                </button>
            </div>
        </div>
    </section>
@endif
