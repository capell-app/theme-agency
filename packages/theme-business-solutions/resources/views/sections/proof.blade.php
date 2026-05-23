<section
    @class([
        'border-y border-black/10 bg-[var(--theme-primary)] text-white',
        'rounded-none' => ! $profile['modern'],
    ])
>
    <div class="business-theme-container">
        <div class="grid gap-8 lg:grid-cols-[0.4fr_0.6fr] lg:items-center">
            <div>
                <p
                    class="mb-3 text-sm font-bold uppercase tracking-[0.16em] text-white/70"
                >
                    {{ __('capell-theme-business-solutions::generic.trust_bar') }}
                </p>
                <h2
                    class="text-3xl font-bold leading-tight text-white sm:text-4xl"
                >
                    {{ $section->heading }}
                </h2>
                @if ($section->summary)
                    <p class="text-white/72 mt-4 text-base leading-7">
                        {{ $section->summary }}
                    </p>
                @endif
            </div>

            <div class="grid gap-3 sm:grid-cols-3">
                @foreach ($section->items as $item)
                    <figure
                        class="border-white/16 bg-white/8 rounded-[var(--theme-radius-value)] border p-5"
                    >
                        <blockquote
                            class="text-2xl font-bold leading-tight text-white"
                        >
                            {{ $item['metric'] ?? $item['quote'] ?? '' }}
                        </blockquote>
                        <figcaption class="text-white/72 mt-3 text-sm">
                            {{ $item['name'] ?? $item['logo'] ?? $item['title'] ?? '' }}
                        </figcaption>
                    </figure>
                @endforeach
            </div>
        </div>
    </div>
</section>
