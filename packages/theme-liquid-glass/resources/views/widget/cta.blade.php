@php
    $heading = (string) ($widget->getMeta('heading') ?? '');
    $summary = $widget->getMeta('summary');
    $actions = is_array($widget->getMeta('actions')) ? $widget->getMeta('actions') : [];

    if ($actions === []) {
        $actions = [
            ['label' => __('capell-theme-liquid-glass::generic.cta_primary_action'), 'url' => '#main-content', 'style' => 'primary'],
            ['label' => __('capell-theme-liquid-glass::generic.cta_secondary_action'), 'url' => '#proof', 'style' => 'secondary'],
        ];
    }
@endphp

<section class="px-5 py-16 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl">
        <div
            class="glass-surface glass-surface-card bg-linear-to-br from-(--theme-primary)/18 to-(--theme-accent)/14 text-center"
        >
            <p
                class="inline-flex items-center justify-center text-xs font-black tracking-[0.08em] text-(--theme-primary) uppercase"
            >
                {{ __('capell-theme-liquid-glass::generic.cta_eyebrow') }}
            </p>

            <h2
                class="mx-auto mt-4 max-w-3xl leading-tight font-black text-balance text-(--theme-foreground)"
            >
                {{ $heading }}
            </h2>

            @if ($summary)
                <p
                    class="mx-auto mt-5 max-w-2xl text-lg leading-8 text-(--theme-foreground)/75"
                >
                    {{ $summary }}
                </p>
            @endif

            <div class="mt-8 flex flex-wrap justify-center gap-3">
                @foreach ($actions as $action)
                    <a
                        href="{{ $action['url'] ?? '#' }}"
                        @class ([
                            'inline-flex min-h-11 items-center justify-center rounded-full px-5 py-3 text-sm font-black' => true,
                            'bg-(--theme-primary) text-white shadow-lg shadow-(--theme-primary)/25' => ($action['style'] ?? 'primary') === 'primary',
                            'border border-(--theme-foreground)/15 bg-(--theme-surface)/70 text-(--theme-foreground)' => ($action['style'] ?? 'primary') !== 'primary',
                        ])
                    >
                        {{ $action['label'] ?? '' }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</section>
