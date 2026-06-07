@php
    $items = $section->items ?? [];
    $heading ??= $section->heading ?? __('capell-theme-portfolio::generic.resume_cv_label');
    $summary ??= $section->summary ?? __('capell-theme-portfolio::generic.resume_cv_summary');
    $downloadAction = $section->downloadAction ?? $section->action ?? null;
@endphp

<section class="theme-section theme-section-resume-cv portfolio-bg-card-soft">
    <div
        class="mx-auto grid max-w-6xl gap-8 px-6 py-16 lg:grid-cols-[0.75fr_1.25fr]"
    >
        <div>
            <p
                class="portfolio-text-primary text-xs font-black tracking-[0.16em] uppercase"
            >
                {{ __('capell-theme-portfolio::generic.resume_cv_label') }}
            </p>
            <h2 class="portfolio-text-ink mt-3 text-4xl font-black">
                {{ $heading }}
            </h2>
            @if ($summary)
                <p class="mt-4 text-base leading-8 text-slate-600">
                    {{ $summary }}
                </p>
            @endif

            @if (is_array($downloadAction) && ($downloadAction['url'] ?? null) && ($downloadAction['label'] ?? null))
                <a
                    href="{{ $downloadAction['url'] }}"
                    class="portfolio-bg-ink mt-6 inline-flex rounded-full px-5 py-3 text-sm font-black text-white"
                >
                    {{ $downloadAction['label'] }}
                </a>
            @endif
        </div>

        <div class="grid gap-4">
            @forelse ($items as $item)
                <article
                    class="rounded-xl border border-slate-200 bg-white p-5"
                >
                    <div class="grid gap-2 md:grid-cols-[0.35fr_1fr]">
                        <p
                            class="portfolio-text-primary-strong text-xs font-black tracking-[0.14em] uppercase"
                        >
                            {{ $item['period'] ?? $item['date'] ?? __('capell-theme-portfolio::generic.resume_period_label') }}
                        </p>
                        <div>
                            <h3 class="portfolio-text-ink text-lg font-black">
                                {{ $item['title'] ?? __('capell-theme-portfolio::generic.resume_item_label') }}
                            </h3>
                            @if ($item['organization'] ?? $item['company'] ?? null)
                                <p
                                    class="mt-1 text-sm font-bold text-slate-500"
                                >
                                    {{ $item['organization'] ?? $item['company'] }}
                                </p>
                            @endif

                            <p class="mt-3 text-sm leading-6 text-slate-600">
                                {{ $item['summary'] ?? $item['description'] ?? __('capell-theme-portfolio::generic.resume_cv_ready') }}
                            </p>
                        </div>
                    </div>
                </article>
            @empty
                <article
                    class="rounded-xl border border-dashed border-slate-300 bg-white p-6"
                >
                    <h3 class="portfolio-text-ink text-lg font-black">
                        {{ __('capell-theme-portfolio::generic.premium_layout_ready') }}
                    </h3>
                    <p class="mt-2 text-sm text-slate-600">
                        {{ __('capell-theme-portfolio::generic.premium_layout_empty') }}
                    </p>
                </article>
            @endforelse
        </div>
    </div>
</section>
