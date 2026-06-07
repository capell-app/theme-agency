@php
    $logos = $section->logos ?? $section->items ?? [];
@endphp

<section class="theme-client-logos mx-auto max-w-7xl px-6 py-16">
    <div
        class="rounded-[1.5rem] border border-white/10 bg-white p-6 text-zinc-950"
    >
        <div class="grid gap-5 lg:grid-cols-[0.55fr_1.45fr] lg:items-center">
            <div>
                <p
                    class="text-xs font-black tracking-[0.18em] text-[var(--theme-primary)] uppercase"
                >
                    {{ __('capell-theme-agency::generic.client_logos_label') }}
                </p>
                <h2 class="mt-3 text-3xl font-black tracking-tight">
                    {{ $section->heading }}
                </h2>
                @if ($section->summary)
                    <p class="mt-3 text-sm leading-6 text-zinc-600">
                        {{ $section->summary }}
                    </p>
                @endif
            </div>

            @if ($logos !== [])
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($logos as $logo)
                        <div
                            class="flex min-h-24 items-center justify-center rounded-2xl border border-zinc-200 bg-zinc-50 p-4 text-center"
                        >
                            @if (! empty($logo['image']) || ! empty($logo['imageUrl']))
                                <img
                                    src="{{ $logo['image'] ?? $logo['imageUrl'] }}"
                                    alt="{{ $logo['alt'] ?? $logo['name'] ?? '' }}"
                                    width="220"
                                    height="96"
                                    loading="lazy"
                                    decoding="async"
                                    class="max-h-12 w-auto object-contain"
                                />
                            @else
                                <span
                                    class="text-sm font-black tracking-[0.18em] text-zinc-500 uppercase"
                                >
                                    {{ $logo['name'] ?? '' }}
                                </span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div
                    class="rounded-2xl border border-dashed border-zinc-300 p-6"
                >
                    <p
                        class="text-sm font-black tracking-[0.18em] text-[var(--theme-primary)] uppercase"
                    >
                        {{ __('capell-theme-agency::generic.client_logos_empty_title') }}
                    </p>
                    <p class="mt-3 text-sm leading-6 text-zinc-600">
                        {{ __('capell-theme-agency::generic.client_logos_empty_summary') }}
                    </p>
                </div>
            @endif
        </div>
    </div>
</section>
