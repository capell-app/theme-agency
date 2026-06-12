@php
    $variant = $section->variant ?? 'editorial';
    $sectionId = $variant === 'gallery' || $variant === 'media' ? 'gallery' : 'content';
    $variantLabel = match ($variant) {
        'faq' => __('capell-theme-corporate::generic.variant_faq'),
        'media' => __('capell-theme-corporate::generic.variant_media'),
        'metrics' => __('capell-theme-corporate::generic.variant_metrics'),
        'people' => __('capell-theme-corporate::generic.variant_people'),
        default => __('capell-theme-corporate::generic.variant_editorial'),
    };
@endphp

@if (in_array($variant, ['gallery', 'pathways', 'spotlight'], true))
    @include('capell::themes.default.sections.content-listing', ['section' => $section])
@else
    <section
        id="{{ $sectionId }}"
        class="theme-content-listing corporate-surface border-b border-slate-200/80 dark:border-white/10"
    >
        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-12 lg:py-16">
            <div
                class="mb-6 grid gap-3 sm:mb-8 lg:grid-cols-[0.75fr_1.25fr] lg:items-end"
            >
                <div>
                    <p
                        class="mb-3 text-xs font-semibold tracking-[0.16em] text-[var(--theme-primary)] uppercase dark:text-[var(--theme-accent)]"
                    >
                        {{ $variantLabel }}
                    </p>
                    <h2
                        class="max-w-xl text-2xl leading-tight font-semibold text-slate-950 sm:text-3xl lg:text-4xl dark:text-white"
                    >
                        {{ $section->heading }}
                    </h2>
                </div>
                @if ($section->summary)
                    <p
                        class="max-w-2xl text-sm leading-6 text-slate-600 sm:leading-7 lg:justify-self-end dark:text-slate-300"
                    >
                        {{ $section->summary }}
                    </p>
                @endif
            </div>

            @if ($variant === 'gallery')
                <div class="grid gap-2 sm:grid-cols-2 sm:gap-3 lg:grid-cols-4">
                    @foreach ($section->items as $item)
                        <a
                            href="{{ $item['url'] ?? '#' }}"
                            class="{{ $loop->first ? 'sm:col-span-2 sm:row-span-2' : '' }} group corporate-card-muted relative block overflow-hidden p-0 dark:bg-slate-800"
                        >
                            @if (! empty($item['image']))
                                <img
                                    src="{{ $item['image'] }}"
                                    alt=""
                                    class="{{ $loop->first ? 'aspect-[4/3] sm:aspect-square' : 'aspect-[5/3] sm:aspect-[4/3]' }} w-full object-cover transition duration-300 group-hover:scale-[1.025]"
                                />
                            @else
                                <div
                                    class="{{ $loop->first ? 'aspect-[4/3] sm:aspect-square' : 'aspect-[5/3] sm:aspect-[4/3]' }} w-full bg-slate-200 dark:bg-slate-800"
                                ></div>
                            @endif
                            <div
                                class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-slate-950/80 to-transparent p-3 text-white sm:p-4"
                            >
                                <h3 class="text-sm font-semibold">
                                    {{ $item['title'] }}
                                </h3>
                                @if (! empty($item['type']))
                                    <p class="mt-1 text-xs text-white/75">
                                        {{ $item['type'] }}
                                    </p>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            @elseif ($variant === 'media')
                <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($section->items as $item)
                        <a
                            href="{{ $item['url'] ?? '#' }}"
                            class="group corporate-card overflow-hidden p-0 dark:bg-white/[0.03]"
                        >
                            @if (! empty($item['image']))
                                <img
                                    src="{{ $item['image'] }}"
                                    alt=""
                                    class="aspect-[5/3] w-full object-cover transition duration-300 group-hover:scale-[1.02] sm:aspect-video"
                                />
                            @endif

                            <div
                                class="flex items-start justify-between gap-3 p-4 sm:p-5"
                            >
                                <div>
                                    <h3
                                        class="font-semibold text-slate-950 dark:text-white"
                                    >
                                        {{ $item['title'] }}
                                    </h3>
                                    @if (! empty($item['summary']))
                                        <p
                                            class="mt-1.5 line-clamp-2 text-sm leading-6 text-slate-600 dark:text-slate-300"
                                        >
                                            {{ $item['summary'] }}
                                        </p>
                                    @endif
                                </div>
                                <span class="text-xs text-slate-400">
                                    {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @elseif ($variant === 'faq')
                <div
                    class="corporate-card divide-y divide-slate-200 p-0 dark:divide-white/10 dark:bg-white/[0.03]"
                >
                    @foreach ($section->items as $item)
                        <a
                            href="{{ $item['url'] ?? '#' }}"
                            class="grid gap-2 p-4 transition hover:bg-slate-50 sm:grid-cols-[0.25fr_1fr] sm:p-5 dark:hover:bg-white/[0.04]"
                        >
                            <span
                                class="text-xs font-medium tracking-[0.16em] text-slate-400 uppercase"
                            >
                                {{ __('capell-theme-corporate::generic.question_number', ['number' => $loop->iteration]) }}
                            </span>
                            <span>
                                <span
                                    class="block font-semibold text-slate-950 dark:text-white"
                                >
                                    {{ $item['title'] }}
                                </span>
                                @if (! empty($item['summary']))
                                    <span
                                        class="mt-1.5 block text-sm leading-6 text-slate-600 dark:text-slate-300"
                                    >
                                        {{ $item['summary'] }}
                                    </span>
                                @endif
                            </span>
                        </a>
                    @endforeach
                </div>
            @elseif ($variant === 'people')
                <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($section->items as $item)
                        <a
                            href="{{ $item['url'] ?? '#' }}"
                            class="corporate-card flex gap-3 transition hover:border-slate-950 sm:gap-4 dark:bg-white/[0.03] dark:hover:border-white"
                        >
                            @if (! empty($item['image']))
                                <img
                                    src="{{ $item['image'] }}"
                                    alt=""
                                    class="h-16 w-16 shrink-0 rounded-[0.25rem] object-cover sm:h-20 sm:w-20"
                                />
                            @endif

                            <span class="min-w-0">
                                <span
                                    class="block font-semibold text-slate-950 dark:text-white"
                                >
                                    {{ $item['title'] }}
                                </span>
                                <span
                                    class="mt-1 block text-sm text-slate-500 dark:text-slate-400"
                                >
                                    {{ $item['meta'][0] ?? $item['type'] ?? $item['author'] ?? '' }}
                                </span>
                                @if (! empty($item['summary']))
                                    <span
                                        class="mt-1.5 line-clamp-2 block text-sm leading-6 text-slate-600 dark:text-slate-300"
                                    >
                                        {{ $item['summary'] }}
                                    </span>
                                @endif
                            </span>
                        </a>
                    @endforeach
                </div>
            @elseif ($variant === 'metrics')
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($section->items as $item)
                        <a
                            href="{{ $item['url'] ?? '#' }}"
                            class="corporate-card block sm:p-6 dark:bg-white/[0.03]"
                        >
                            <span
                                class="text-3xl font-semibold text-slate-950 sm:text-4xl dark:text-white"
                            >
                                {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                            </span>
                            <h3
                                class="mt-4 font-semibold text-slate-950 sm:mt-6 dark:text-white"
                            >
                                {{ $item['title'] }}
                            </h3>
                            @if (! empty($item['summary']))
                                <p
                                    class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300"
                                >
                                    {{ $item['summary'] }}
                                </p>
                            @endif
                        </a>
                    @endforeach
                </div>
            @else
                <div class="grid gap-4">
                    @foreach ($section->items as $item)
                        <a
                            href="{{ $item['url'] ?? '#' }}"
                            class="group corporate-card grid overflow-hidden p-0 transition hover:border-slate-950 md:grid-cols-[0.75fr_1.45fr_0.55fr] dark:bg-white/[0.03] dark:hover:border-white"
                        >
                            @if (! empty($item['image']))
                                <img
                                    src="{{ $item['image'] }}"
                                    alt=""
                                    class="h-full min-h-48 w-full object-cover transition duration-300 group-hover:scale-[1.02]"
                                />
                            @else
                                <span
                                    class="min-h-48 bg-slate-950 p-5 dark:bg-black"
                                    aria-hidden="true"
                                >
                                    <span
                                        class="block h-2 w-24 bg-[var(--theme-accent)]"
                                    ></span>
                                    <span
                                        class="mt-10 block h-2 w-4/5 bg-white/35"
                                    ></span>
                                    <span
                                        class="mt-3 block h-2 w-3/5 bg-white/25"
                                    ></span>
                                    <span
                                        class="mt-3 block h-2 w-2/5 bg-white/25"
                                    ></span>
                                </span>
                            @endif

                            <span class="block min-w-0 p-4 sm:p-5 lg:p-6">
                                <span
                                    class="mb-2 flex flex-wrap items-center gap-2 text-xs font-semibold tracking-[0.12em] text-slate-500 uppercase sm:mb-3 lg:mb-4 dark:text-slate-400"
                                >
                                    @if (! empty($item['type']))
                                        <span>{{ $item['type'] }}</span>
                                    @else
                                        <span>
                                            {{ __('capell-theme-corporate::generic.briefing_signal') }}
                                        </span>
                                    @endif

                                    @if (! empty($item['publishedAt']) && ! empty($item['publishedDate']))
                                        <time
                                            datetime="{{ $item['publishedAt'] }}"
                                        >
                                            {{ $item['publishedDate'] }}
                                        </time>
                                    @endif

                                    @if (! empty($item['author']))
                                        <span>{{ $item['author'] }}</span>
                                    @endif
                                </span>
                                <span
                                    class="block text-lg leading-tight font-semibold text-slate-950 sm:text-xl dark:text-white"
                                >
                                    {{ $item['title'] }}
                                </span>
                                @if (! empty($item['summary']))
                                    <span
                                        class="mt-2 block text-sm leading-6 text-slate-600 dark:text-slate-300"
                                    >
                                        {{ $item['summary'] }}
                                    </span>
                                @endif

                                @if (! empty($item['meta']))
                                    <span
                                        class="mt-3 flex flex-wrap gap-2 sm:mt-5"
                                    >
                                        @foreach ($item['meta'] as $label)
                                            <span
                                                class="rounded-full border border-slate-200 px-2.5 py-1 text-xs text-slate-500 dark:border-white/10 dark:text-slate-300"
                                            >
                                                {{ $label }}
                                            </span>
                                        @endforeach
                                    </span>
                                @endif
                            </span>
                            <span
                                class="corporate-card-muted border-t border-slate-200 p-4 text-sm md:border-t-0 md:border-l dark:border-white/10 dark:bg-white/[0.03]"
                            >
                                <span
                                    class="block text-xs font-semibold tracking-[0.16em] text-slate-500 uppercase dark:text-slate-400"
                                >
                                    {{ __('capell-theme-corporate::generic.register_signal') }}
                                </span>
                                <span
                                    class="mt-3 block font-mono text-3xl font-semibold text-[var(--theme-primary)] dark:text-[var(--theme-accent)]"
                                >
                                    {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                </span>
                                <span
                                    class="mt-3 block text-xs font-semibold text-slate-600 dark:text-slate-300"
                                >
                                    {{ __('capell-theme-corporate::generic.approved_signal') }}
                                </span>
                            </span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endif
