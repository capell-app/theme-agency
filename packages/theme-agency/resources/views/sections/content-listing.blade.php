@if (in_array($section->variant ?? null, ['gallery', 'pathways', 'spotlight'], true))
    @include('capell-foundation-theme::theme.sections.content-listing', ['section' => $section])
@else
    <section class="theme-content-listing mx-auto max-w-7xl px-6 py-20">
        <div class="grid gap-6 lg:grid-cols-[0.65fr_1.35fr] lg:items-end">
            <div>
                <p
                    class="text-sm font-black tracking-[0.24em] text-[var(--theme-primary)] uppercase"
                >
                    {{ __('capell-theme-agency::generic.work_wall') }}
                </p>
                <h2 class="mt-4 text-5xl font-black tracking-tight">
                    {{ $section->heading }}
                </h2>
            </div>
            @if ($section->summary)
                <p class="max-w-2xl text-zinc-400 lg:justify-self-end">
                    {{ $section->summary }}
                </p>
            @endif
        </div>

        <div class="mt-10 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($section->items as $item)
                <a
                    href="{{ $item['url'] ?? '#' }}"
                    class="group overflow-hidden rounded-[1.5rem] bg-white p-4 text-zinc-950 shadow-sm transition hover:-translate-y-1 hover:shadow-2xl"
                >
                    @if (! empty($item['image']) || ! empty($item['imageUrl']))
                        <img
                            src="{{ $item['image'] ?? $item['imageUrl'] }}"
                            alt=""
                            class="aspect-[4/3] w-full rounded-[1.1rem] object-cover transition duration-300 group-hover:scale-[1.025]"
                        />
                    @else
                        <div
                            class="site-brand-gradient aspect-[4/3] rounded-[1.1rem] p-4"
                            aria-hidden="true"
                        >
                            <div
                                class="flex h-full flex-col justify-between rounded-2xl bg-zinc-950/90 p-4"
                            >
                                <span
                                    class="h-3 w-20 rounded-full bg-white"
                                ></span>
                                <div class="grid grid-cols-3 gap-2">
                                    <span
                                        class="h-12 rounded-xl bg-white/15"
                                    ></span>
                                    <span
                                        class="h-12 rounded-xl bg-white/25"
                                    ></span>
                                    <span
                                        class="h-12 rounded-xl bg-white/15"
                                    ></span>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="p-2 pt-5">
                        <p
                            class="text-xs font-black tracking-[0.18em] text-[var(--theme-primary)] uppercase"
                        >
                            {{ $item['type'] ?? __('capell-theme-agency::generic.case_signal') }}
                        </p>
                        <h3
                            class="mt-3 text-xl font-bold group-hover:text-[var(--theme-primary)]"
                        >
                            {{ $item['title'] }}
                        </h3>
                        <p class="mt-2 text-sm leading-6 text-zinc-600">
                            {{ $item['summary'] ?? '' }}
                        </p>
                        <div
                            class="mt-5 flex items-center justify-between text-xs font-black"
                        >
                            <span
                                class="rounded-full bg-zinc-950 px-3 py-1 text-white"
                            >
                                {{ __('capell-theme-agency::generic.case_study_signal') }}
                            </span>
                            <span class="text-zinc-400">
                                {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                            </span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </section>
@endif
