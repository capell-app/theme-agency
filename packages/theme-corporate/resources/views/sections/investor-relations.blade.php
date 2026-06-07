@php
    $items = $section->items ?? $section->reports ?? [];
    $events = $section->events ?? $section->calendar ?? [];
    $documents = $section->documents ?? $section->downloads ?? [];
    $heading = $section->heading ?? __('capell-theme-corporate::generic.investor_heading');
    $summary = $section->summary ?? __('capell-theme-corporate::generic.investor_summary');
@endphp

<section
    class="theme-investor-relations corporate-surface border-b border-slate-200/80 dark:border-white/10"
>
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-12 lg:py-16">
        <div class="grid gap-6 lg:grid-cols-[0.72fr_1.28fr] lg:gap-8">
            <div>
                <p
                    class="mb-3 text-xs font-semibold tracking-[0.16em] text-[var(--theme-primary)] uppercase dark:text-[var(--theme-accent)]"
                >
                    {{ __('capell-theme-corporate::generic.investor_eyebrow') }}
                </p>
                <h2
                    class="max-w-lg text-2xl leading-tight font-semibold text-slate-950 sm:text-3xl lg:text-4xl dark:text-white"
                >
                    {{ $heading }}
                </h2>
                @if ($summary)
                    <p
                        class="mt-3 max-w-md text-sm leading-6 text-slate-600 sm:leading-7 dark:text-slate-300"
                    >
                        {{ $summary }}
                    </p>
                @endif

                <div
                    class="mt-6 grid max-w-md grid-cols-3 border border-slate-200 text-xs font-semibold text-slate-600 dark:border-white/10 dark:text-slate-300"
                >
                    <span
                        class="border-r border-slate-200 p-3 dark:border-white/10"
                    >
                        {{ __('capell-theme-corporate::generic.investor_reports_label') }}
                    </span>
                    <span
                        class="border-r border-slate-200 p-3 dark:border-white/10"
                    >
                        {{ __('capell-theme-corporate::generic.investor_events_label') }}
                    </span>
                    <span class="p-3">
                        {{ __('capell-theme-corporate::generic.investor_disclosures_label') }}
                    </span>
                </div>
            </div>

            <div class="grid gap-3">
                @forelse ($items as $item)
                    <a
                        href="{{ $item['url'] ?? '#' }}"
                        class="group corporate-card grid gap-4 p-4 transition hover:border-slate-950 sm:grid-cols-[0.2fr_1fr_0.32fr] sm:items-center sm:p-5 dark:bg-white/[0.03] dark:hover:border-white"
                    >
                        <span
                            class="font-mono text-sm font-semibold text-slate-500 dark:text-slate-400"
                        >
                            {{ $item['period'] ?? str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                        </span>
                        <span>
                            <span
                                class="block text-base font-semibold text-slate-950 dark:text-white"
                            >
                                {{ $item['title'] ?? $item['label'] ?? '' }}
                            </span>
                            @if (! empty($item['summary']))
                                <span
                                    class="mt-1.5 block text-sm leading-6 text-slate-600 dark:text-slate-300"
                                >
                                    {{ $item['summary'] }}
                                </span>
                            @endif
                        </span>
                        <span
                            class="text-xs font-semibold tracking-[0.14em] text-[var(--theme-primary)] uppercase sm:text-right dark:text-[var(--theme-accent)]"
                        >
                            {{ $item['type'] ?? __('capell-theme-corporate::generic.investor_report_type') }}
                        </span>
                    </a>
                @empty
                    <div class="corporate-card-muted p-5 sm:p-6">
                        <h3
                            class="text-lg font-semibold text-slate-950 dark:text-white"
                        >
                            {{ __('capell-theme-corporate::generic.investor_empty_title') }}
                        </h3>
                        <p
                            class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300"
                        >
                            {{ __('capell-theme-corporate::generic.investor_empty_summary') }}
                        </p>
                    </div>
                @endforelse

                @if ($events !== [] || $documents !== [])
                    <div class="grid gap-3 md:grid-cols-2">
                        @if ($events !== [])
                            <div class="corporate-card-muted p-4 sm:p-5">
                                <h3
                                    class="text-sm font-semibold tracking-[0.14em] text-slate-500 uppercase dark:text-slate-400"
                                >
                                    {{ __('capell-theme-corporate::generic.investor_events_label') }}
                                </h3>
                                <div class="mt-4 grid gap-3">
                                    @foreach (array_slice($events, 0, 3) as $event)
                                        <p
                                            class="text-sm leading-6 text-slate-700 dark:text-slate-200"
                                        >
                                            <span
                                                class="block font-semibold text-slate-950 dark:text-white"
                                            >
                                                {{ $event['title'] ?? $event['label'] ?? '' }}
                                            </span>
                                            <span>
                                                {{ $event['date'] ?? $event['publishedDate'] ?? '' }}
                                            </span>
                                        </p>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if ($documents !== [])
                            <div class="corporate-card-muted p-4 sm:p-5">
                                <h3
                                    class="text-sm font-semibold tracking-[0.14em] text-slate-500 uppercase dark:text-slate-400"
                                >
                                    {{ __('capell-theme-corporate::generic.investor_disclosures_label') }}
                                </h3>
                                <div class="mt-4 grid gap-2">
                                    @foreach (array_slice($documents, 0, 4) as $document)
                                        <a
                                            href="{{ $document['url'] ?? '#' }}"
                                            class="text-sm font-semibold text-[var(--theme-primary)] underline-offset-4 hover:underline dark:text-[var(--theme-accent)]"
                                        >
                                            {{ $document['title'] ?? $document['label'] ?? '' }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
