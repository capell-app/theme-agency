@php
    $services = $section->services ?? $section->items ?? [];
@endphp

<section class="theme-services mx-auto max-w-7xl px-6 py-20">
    <div class="grid gap-6 lg:grid-cols-[0.72fr_1.28fr] lg:items-end">
        <div>
            <p
                class="text-sm font-black tracking-[0.24em] text-[var(--theme-primary)] uppercase"
            >
                {{ __('capell-theme-agency::generic.services_label') }}
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

    @if ($services !== [])
        <div class="mt-10 grid gap-5 lg:grid-cols-3">
            @foreach ($services as $service)
                <article
                    class="rounded-[1.5rem] border border-white/10 bg-white p-6 text-zinc-950 shadow-sm"
                >
                    <p
                        class="text-xs font-black tracking-[0.18em] text-[var(--theme-primary)] uppercase"
                    >
                        {{ $service['discipline'] ?? $service['type'] ?? __('capell-theme-agency::generic.studio_signal') }}
                    </p>
                    <h3 class="mt-5 text-2xl font-black tracking-tight">
                        {{ $service['title'] ?? '' }}
                    </h3>
                    @if (! empty($service['summary']) || ! empty($service['description']))
                        <p class="mt-4 text-sm leading-6 text-zinc-600">
                            {{ $service['summary'] ?? $service['description'] }}
                        </p>
                    @endif

                    @if (! empty($service['deliverables']))
                        <ul class="mt-6 space-y-3 text-sm font-bold">
                            @foreach ($service['deliverables'] as $deliverable)
                                <li class="flex gap-3">
                                    <span
                                        class="mt-2 h-2 w-2 rounded-full bg-[var(--theme-accent)]"
                                        aria-hidden="true"
                                    ></span>
                                    <span>{{ $deliverable }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
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
                {{ __('capell-theme-agency::generic.services_empty_title') }}
            </p>
            <p class="mt-3 max-w-xl text-sm leading-6 text-zinc-400">
                {{ __('capell-theme-agency::generic.services_empty_summary') }}
            </p>
        </div>
    @endif
</section>
