@php
    $eyebrow = (string) ($widget->getMeta('eyebrow') ?? '');
    $heading = (string) ($widget->getMeta('heading') ?? '');
    $summary = $widget->getMeta('summary');
    $items = is_array($widget->getMeta('items')) ? $widget->getMeta('items') : [];
@endphp

<section class="px-5 py-16 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl">
        <div class="max-w-3xl">
            @if ($eyebrow !== '')
                <p
                    class="text-xs font-black tracking-[0.08em] text-(--theme-primary) uppercase"
                >
                    {{ $eyebrow }}
                </p>
            @endif

            <h2
                class="mt-4 text-4xl leading-tight font-black text-balance text-(--theme-foreground) sm:text-5xl"
            >
                {{ $heading }}
            </h2>

            @if ($summary)
                <p class="mt-5 text-lg leading-8 text-(--theme-foreground)/75">
                    {{ $summary }}
                </p>
            @endif
        </div>

        <div class="glass-surface-grid mt-10 grid lg:grid-cols-3">
            @foreach ($items as $item)
                <article
                    class="glass-surface glass-surface-card glass-surface-card--flush overflow-hidden border border-(--theme-foreground)/10 bg-(--theme-surface)/80"
                >
                    @if (! empty($item['image']))
                        <img
                            src="{{ $item['image'] }}"
                            alt="{{ $item['imageAlt'] ?? '' }}"
                            width="720"
                            height="520"
                            loading="lazy"
                            decoding="async"
                            class="aspect-[3/2] w-full object-cover"
                        />
                    @endif

                    <div class="glass-surface-body">
                        <p
                            class="text-sm font-bold tracking-wide text-(--theme-primary) uppercase"
                        >
                            {{ $item['discipline'] ?? '' }}
                        </p>
                        <h3
                            class="mt-3 leading-tight font-black text-(--theme-foreground)"
                        >
                            {{ $item['title'] ?? '' }}
                        </h3>
                        <p class="mt-4 text-sm leading-7 text-(--theme-foreground)/70">
                            {{ $item['summary'] ?? '' }}
                        </p>
                        @if (! empty($item['metric']))
                            <p class="mt-5 text-3xl font-black text-(--theme-primary)">
                                {{ $item['metric'] }}
                                <span
                                    class="block text-xs font-bold tracking-wide text-(--theme-foreground)/55 uppercase"
                                >
                                    {{ $item['metricLabel'] ?? '' }}
                                </span>
                            </p>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
