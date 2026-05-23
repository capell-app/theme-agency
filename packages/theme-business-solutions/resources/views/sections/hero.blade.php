@php
    $layout = $profile['layout'];
    $primaryAction = $section->actions[0] ?? null;
    $secondaryAction = $section->actions[1] ?? null;
    $quickLabels = [
        __('capell-theme-business-solutions::generic.certified'),
        __('capell-theme-business-solutions::generic.service_available'),
        __('capell-theme-business-solutions::generic.verified'),
    ];
@endphp

<section class="business-theme-hero overflow-hidden">
    <div class="business-theme-container">
        <div
            @class([
                'grid gap-8',
                'lg:grid-cols-[0.85fr_1.15fr] lg:items-stretch' => in_array($layout, ['search-hero', 'booking-hero'], true),
                'lg:grid-cols-[1fr_0.85fr] lg:items-center' => in_array($layout, ['form-hero', 'appointment-hero', 'split-modern', 'spec-hero', 'job-search'], true),
                'text-center' => $layout === 'ticker-hero',
                'lg:grid-cols-[0.72fr_1.28fr] lg:items-center' => in_array($layout, ['program-finder', 'story-impact', 'service-directory', 'catalog-grid'], true),
            ])
        >
            <div class="flex flex-col justify-center">
                <p
                    class="mb-4 text-sm font-bold uppercase tracking-[0.16em] text-[var(--theme-primary)]"
                >
                    {{ $section->eyebrow ?: $profile['industry'] }}
                </p>
                <h1
                    class="max-w-4xl text-4xl font-bold leading-[1.02] sm:text-5xl lg:text-6xl"
                >
                    {{ $section->heading }}
                </h1>
                @if ($section->summary)
                    <p class="mt-5 max-w-2xl text-lg leading-8 text-slate-600">
                        {{ $section->summary }}
                    </p>
                @endif

                <div class="mt-7 flex flex-wrap gap-3">
                    @if ($primaryAction)
                        <a
                            href="{{ $primaryAction['url'] }}"
                            class="business-theme-button"
                        >
                            {{ $primaryAction['label'] }}
                        </a>
                    @endif

                    @if ($secondaryAction)
                        <a
                            href="{{ $secondaryAction['url'] }}"
                            class="business-theme-button business-theme-button-secondary"
                        >
                            {{ $secondaryAction['label'] }}
                        </a>
                    @endif
                </div>
            </div>

            @if (in_array($layout, ['form-hero', 'appointment-hero'], true))
                <aside class="business-theme-card p-5 sm:p-6">
                    <p
                        class="text-sm font-bold uppercase tracking-[0.14em] text-[var(--theme-primary)]"
                    >
                        {{ __('capell-theme-business-solutions::generic.quick_action') }}
                    </p>
                    <div class="mt-5 grid gap-3">
                        <div
                            class="rounded-[var(--theme-radius-value)] border border-black/10 bg-white px-4 py-3 text-sm text-slate-500"
                        >
                            {{ __('capell-theme-business-solutions::generic.search_placeholder') }}
                        </div>
                        <div
                            class="rounded-[var(--theme-radius-value)] border border-black/10 bg-white px-4 py-3 text-sm text-slate-500"
                        >
                            {{ $profile['industry'] }}
                        </div>
                        @if ($primaryAction)
                            <a
                                href="{{ $primaryAction['url'] }}"
                                class="business-theme-button mt-2"
                            >
                                {{ $primaryAction['label'] }}
                            </a>
                        @endif
                    </div>
                    <div class="mt-5 grid gap-2 sm:grid-cols-3">
                        @foreach ($quickLabels as $quickLabel)
                            <div
                                class="bg-[var(--theme-primary)]/8 rounded-[var(--theme-radius-value)] px-3 py-2 text-xs font-bold text-[var(--theme-primary)]"
                            >
                                {{ $quickLabel }}
                            </div>
                        @endforeach
                    </div>
                </aside>
            @elseif (in_array($layout, ['search-hero', 'job-search', 'service-directory', 'program-finder', 'catalog-grid'], true))
                <aside class="business-theme-visual-panel">
                    <form
                        action="{{ $primaryAction['url'] ?? '#' }}"
                        class="business-theme-mini-card grid gap-3 sm:grid-cols-[1fr_auto]"
                    >
                        <label class="sr-only" for="business-theme-search">
                            {{ __('capell-theme-business-solutions::generic.search') }}
                        </label>
                        <input
                            id="business-theme-search"
                            name="q"
                            type="search"
                            placeholder="{{ __('capell-theme-business-solutions::generic.search_placeholder') }}"
                            class="min-h-12 rounded-[var(--theme-radius-value)] border border-black/10 px-4 text-base"
                        />
                        <button type="submit" class="business-theme-button">
                            {{ __('capell-theme-business-solutions::generic.search') }}
                        </button>
                    </form>

                    <div class="mt-4 grid gap-3 sm:grid-cols-3">
                        @foreach ($quickLabels as $quickLabel)
                            <div class="business-theme-mini-card">
                                <p
                                    class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500"
                                >
                                    {{ $profile['layout'] === 'catalog-grid' ? __('capell-theme-business-solutions::generic.featured_categories') : __('capell-theme-business-solutions::generic.featured_results') }}
                                </p>
                                <p
                                    class="mt-2 text-sm font-bold text-[var(--theme-primary)]"
                                >
                                    {{ $quickLabel }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </aside>
            @elseif ($layout === 'ticker-hero')
                <div class="mx-auto mt-8 grid max-w-4xl gap-3 sm:grid-cols-3">
                    @foreach ([
                                  __('capell-theme-business-solutions::generic.metric_aum'),
                                  __('capell-theme-business-solutions::generic.metric_clients'),
                                  __('capell-theme-business-solutions::generic.metric_retention'),
                              ] as $metricLabel)
                        <div class="business-theme-mini-card">
                            <p
                                class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500"
                            >
                                {{ $metricLabel }}
                            </p>
                            <p
                                class="mt-2 text-3xl font-bold text-[var(--theme-primary)]"
                            >
                                {{ ['£1.8B', '420+', '97%'][$loop->index] }}
                            </p>
                        </div>
                    @endforeach
                </div>
            @elseif ($layout === 'booking-hero')
                <aside class="business-theme-visual-panel">
                    <div class="business-theme-mini-card">
                        <p
                            class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500"
                        >
                            {{ __('capell-theme-business-solutions::generic.quick_action') }}
                        </p>
                        <div class="mt-4 grid gap-3 sm:grid-cols-3">
                            @foreach ($quickLabels as $quickLabel)
                                <span
                                    class="rounded-[var(--theme-radius-value)] border border-black/10 px-3 py-3 text-sm font-semibold"
                                >
                                    {{ $quickLabel }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                    <div class="mt-4 grid gap-3 sm:grid-cols-[1.2fr_0.8fr]">
                        <div class="business-theme-mini-card min-h-44"></div>
                        <div class="business-theme-mini-card">
                            <p
                                class="text-3xl font-bold text-[var(--theme-primary)]"
                            >
                                4.9
                            </p>
                            <p class="mt-2 text-sm text-slate-600">
                                {{ __('capell-theme-business-solutions::generic.verified') }}
                            </p>
                        </div>
                    </div>
                </aside>
            @else
                <div class="business-theme-visual-panel min-h-72">
                    @if ($section->mediaUrl)
                        <img
                            src="{{ $section->mediaUrl }}"
                            alt="{{ $section->mediaAlt ?? '' }}"
                            class="h-full min-h-64 w-full rounded-[var(--theme-radius-value)] object-cover"
                        />
                    @else
                        <div
                            class="grid min-h-64 gap-3 rounded-[var(--theme-radius-value)]"
                        >
                            <div class="business-theme-mini-card self-end">
                                <span
                                    class="text-sm font-bold uppercase tracking-[0.18em] text-[var(--theme-primary)]"
                                >
                                    {{ $profile['industry'] }}
                                </span>
                                <p
                                    class="mt-3 text-3xl font-bold text-slate-950"
                                >
                                    {{ $profile['name'] }}
                                </p>
                            </div>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div class="business-theme-mini-card">
                                    <p
                                        class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500"
                                    >
                                        {{ __('capell-theme-business-solutions::generic.popular_services') }}
                                    </p>
                                    <p
                                        class="mt-2 text-sm font-semibold text-slate-700"
                                    >
                                        {{ __('capell-theme-business-solutions::generic.service_available') }}
                                    </p>
                                </div>
                                <div class="business-theme-mini-card">
                                    <p
                                        class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500"
                                    >
                                        {{ __('capell-theme-business-solutions::generic.response_time') }}
                                    </p>
                                    <p
                                        class="mt-2 text-sm font-semibold text-slate-700"
                                    >
                                        24h
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</section>
