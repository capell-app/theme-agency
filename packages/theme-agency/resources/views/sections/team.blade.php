@php
    $people = $section->people ?? $section->items ?? [];
@endphp

<section class="theme-team mx-auto max-w-7xl px-6 py-20">
    <div class="grid gap-6 lg:grid-cols-[0.65fr_1.35fr] lg:items-end">
        <div>
            <p
                class="text-sm font-black tracking-[0.24em] text-[var(--theme-primary)] uppercase"
            >
                {{ __('capell-theme-agency::generic.team_label') }}
            </p>
            <h2 class="mt-4 max-w-3xl text-5xl font-black tracking-tight">
                {{ $section->heading }}
            </h2>
        </div>

        @if ($section->summary)
            <p class="max-w-2xl text-zinc-400 lg:justify-self-end">
                {{ $section->summary }}
            </p>
        @endif
    </div>

    @if ($people !== [])
        <div class="mt-10 grid gap-5 md:grid-cols-2 lg:grid-cols-4">
            @foreach ($people as $person)
                <article
                    class="overflow-hidden rounded-[1.5rem] bg-white p-4 text-zinc-950 shadow-sm"
                >
                    @if (! empty($person['image']) || ! empty($person['imageUrl']))
                        <img
                            src="{{ $person['image'] ?? $person['imageUrl'] }}"
                            alt="{{ $person['imageAlt'] ?? $person['name'] ?? '' }}"
                            width="640"
                            height="760"
                            loading="lazy"
                            decoding="async"
                            class="aspect-[4/5] w-full rounded-[1.1rem] object-cover"
                        />
                    @else
                        <div
                            class="site-brand-gradient flex aspect-[4/5] items-end rounded-[1.1rem] p-4"
                            aria-hidden="true"
                        >
                            <span
                                class="rounded-full bg-white px-3 py-1 text-xs font-black text-zinc-950"
                            >
                                {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                            </span>
                        </div>
                    @endif

                    <div class="p-2 pt-5">
                        <p
                            class="text-xs font-black tracking-[0.18em] text-[var(--theme-primary)] uppercase"
                        >
                            {{ $person['role'] ?? __('capell-theme-agency::generic.team_role_fallback') }}
                        </p>
                        <h3 class="mt-3 text-xl font-black tracking-tight">
                            {{ $person['name'] ?? '' }}
                        </h3>
                        @if (! empty($person['summary']) || ! empty($person['bio']))
                            <p class="mt-3 text-sm leading-6 text-zinc-600">
                                {{ $person['summary'] ?? $person['bio'] }}
                            </p>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    @else
        <div
            class="mt-10 rounded-[1.5rem] border border-dashed border-white/20 p-8"
        >
            <p
                class="text-sm font-black tracking-[0.24em] text-[var(--theme-primary)] uppercase"
            >
                {{ __('capell-theme-agency::generic.team_empty_title') }}
            </p>
            <p class="mt-3 max-w-xl text-sm leading-6 text-zinc-400">
                {{ __('capell-theme-agency::generic.team_empty_summary') }}
            </p>
        </div>
    @endif
</section>
