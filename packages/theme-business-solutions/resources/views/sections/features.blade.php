<section class="bg-white">
    <div class="business-theme-container">
        <div class="grid gap-8 lg:grid-cols-[0.35fr_0.65fr]">
            <div>
                <p
                    class="mb-3 text-sm font-bold uppercase tracking-[0.16em] text-[var(--theme-primary)]"
                >
                    {{ $profile['industry'] }}
                </p>
                <h2 class="text-3xl font-bold leading-tight sm:text-4xl">
                    {{ $section->heading }}
                </h2>
                @if ($section->summary)
                    <p class="mt-4 text-base leading-7 text-slate-600">
                        {{ $section->summary }}
                    </p>
                @endif
            </div>

            <div class="business-theme-section-grid grid gap-4 sm:grid-cols-2">
                @if ($profile['layout'] === 'catalog-grid')
                    <aside class="business-theme-card p-5">
                        <p
                            class="text-sm font-bold uppercase tracking-[0.14em] text-slate-500"
                        >
                            {{ __('capell-theme-business-solutions::generic.categories') }}
                        </p>
                        <div
                            class="mt-4 grid gap-2 text-sm font-semibold text-slate-600"
                        >
                            @foreach ($section->features as $feature)
                                <a
                                    href="#"
                                    class="hover:bg-[var(--theme-primary)]/10 rounded-[var(--theme-radius-value)] px-3 py-2"
                                >
                                    {{ $feature['title'] }}
                                </a>
                            @endforeach
                        </div>
                    </aside>
                @endif

                <div
                    @class([
                        'grid gap-4',
                        'sm:grid-cols-2' => $profile['layout'] !== 'catalog-grid',
                    ])
                >
                    @foreach ($section->features as $feature)
                        <article class="business-theme-card p-5">
                            <div
                                class="mb-5 h-1.5 w-12 rounded-full bg-[var(--theme-accent)]"
                            ></div>
                            <h3 class="text-xl font-bold">
                                {{ $feature['title'] }}
                            </h3>
                            <p class="mt-3 text-sm leading-6 text-slate-600">
                                {{ $feature['description'] ?? $feature['summary'] ?? '' }}
                            </p>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
