@php
    $heroStats = collect(
        $section->stats ?? [
            [
                'label' => __('capell-theme-agency::generic.hero_status_label'),
                'value' => __('capell-theme-agency::generic.live_signal'),
                'accent' => true,
            ],
            [
                'label' => __('capell-theme-agency::generic.hero_channels_label'),
                'value' => __('capell-theme-agency::generic.hero_channels_value'),
            ],
            [
                'label' => __('capell-theme-agency::generic.hero_calendar_label'),
                'value' => __('capell-theme-agency::generic.hero_calendar_value'),
            ],
        ],
    )->take(3);
@endphp

<section class="theme-hero overflow-hidden bg-zinc-950 text-white">
    <div
        class="mx-auto grid max-w-7xl gap-10 px-6 py-20 lg:grid-cols-[0.86fr_1.14fr] lg:items-center lg:py-24"
    >
        <div>
            @if ($section->eyebrow)
                <p
                    class="mb-6 text-sm font-semibold tracking-[0.14em] text-[var(--theme-accent)] uppercase"
                >
                    {{ $section->eyebrow }}
                </p>
            @endif

            <h1
                class="max-w-4xl text-5xl leading-[1.02] font-extrabold tracking-tight md:text-6xl"
            >
                {{ $section->heading }}
            </h1>
            @if ($section->summary)
                <p class="mt-6 max-w-xl text-lg leading-8 text-white/85">
                    {{ $section->summary }}
                </p>
            @endif

            <div class="mt-8 flex flex-wrap gap-3">
                @foreach ($section->actions as $action)
                    <a
                        href="{{ $action['url'] }}"
                        class="{{ ($action['style'] ?? 'primary') === 'secondary' ? 'border border-white/25 text-white' : 'bg-[var(--theme-primary)] text-white' }} rounded-full px-6 py-3 text-sm font-bold"
                    >
                        {{ $action['label'] }}
                    </a>
                @endforeach
            </div>

            <div
                class="mt-10 grid max-w-xl grid-cols-3 gap-3 text-xs font-semibold tracking-[0.08em] uppercase"
            >
                @foreach ($heroStats as $stat)
                    <div
                        class="rounded-2xl border border-white/10 bg-white/5 p-4"
                    >
                        <p class="text-white/75">
                            {{ $stat['label'] ?? '' }}
                        </p>
                        <p
                            class="{{ $stat['accent'] ?? false ? 'text-[var(--theme-accent)]' : 'text-white' }} mt-2"
                        >
                            {{ $stat['value'] ?? '' }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>

        @include('capell-theme-agency::sections.partials.hero-canvas', ['section' => $section])
    </div>
</section>
