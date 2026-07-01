@php
    $presets = $section->presets !== [] ? $section->presets : [];
@endphp

<section class="liquid-glass-section px-5 py-16 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl">
        <div class="max-w-3xl">
            <p class="liquid-glass-eyebrow">
                {{ $section->eyebrow ?: __('capell-theme-liquid-glass::generic.features_eyebrow') }}
            </p>
            <h2
                class="mt-4 text-4xl leading-tight font-black text-balance sm:text-5xl"
            >
                {{ $section->heading }}
            </h2>
            @if ($section->summary)
                <p
                    class="mt-5 text-lg leading-8 text-[color-mix(in_srgb,var(--liquid-glass-foreground)_72%,transparent)]"
                >
                    {{ $section->summary }}
                </p>
            @endif
        </div>

        <div class="mt-10 grid gap-5 md:grid-cols-3">
            @foreach ($presets as $preset)
                <article class="liquid-glass-panel p-6">
                    <p
                        class="text-sm font-bold tracking-wide text-[var(--theme-primary)] uppercase"
                    >
                        {{ $preset['name'] ?? '' }}
                    </p>
                    <h3 class="mt-3 text-2xl leading-tight font-black">
                        {{ $preset['title'] ?? '' }}
                    </h3>
                    <p
                        class="mt-4 text-sm leading-7 text-[color-mix(in_srgb,var(--liquid-glass-foreground)_70%,transparent)]"
                    >
                        {{ $preset['description'] ?? '' }}
                    </p>
                    @if (! empty($preset['surfaces']))
                        <ul class="mt-5 grid gap-2">
                            @foreach ($preset['surfaces'] as $surface)
                                <li class="flex items-center gap-3 text-sm">
                                    <span class="liquid-glass-rule w-6"></span>
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
