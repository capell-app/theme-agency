@php
    $eyebrow = (string) ($widget->getMeta('eyebrow') ?? '');
    $heading = (string) ($widget->getMeta('heading') ?? '');
    $summary = $widget->getMeta('summary');
    $presets = is_array($widget->getMeta('presets')) ? $widget->getMeta('presets') : [];
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

        <div class="glass-surface-grid mt-10 grid md:grid-cols-3">
            @foreach ($presets as $preset)
                <article
                    class="glass-surface glass-surface-card border border-(--theme-foreground)/10 bg-(--theme-surface)/80"
                >
                    <p
                        class="text-sm font-bold tracking-wide text-(--theme-primary) uppercase"
                    >
                        {{ $preset['name'] ?? '' }}
                    </p>
                    <h3
                        class="mt-3 leading-tight font-black text-(--theme-foreground)"
                    >
                        {{ $preset['title'] ?? '' }}
                    </h3>
                    <p class="mt-4 text-sm leading-7 text-(--theme-foreground)/70">
                        {{ $preset['description'] ?? '' }}
                    </p>

                    @if (! empty($preset['surfaces']))
                        <ul class="mt-5 grid gap-2">
                            @foreach ($preset['surfaces'] as $surface)
                                <li
                                    class="flex items-center gap-3 text-sm text-(--theme-foreground)"
                                >
                                    <span
                                        class="block h-[0.35rem] w-6 rounded-full bg-(--theme-foreground)/15"
                                    ></span>
                                    <span>{{ $surface }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </article>
            @endforeach
        </div>
    </div>
</section>
