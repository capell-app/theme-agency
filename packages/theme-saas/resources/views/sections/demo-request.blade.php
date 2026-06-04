@php
    $items ??= $section->items ?? [];
    $heading ??= $section->heading ?? __('capell-theme-saas::generic.demo_request_label');
    $summary ??= $section->summary ?? null;
@endphp

<section
    class="theme-section theme-section-demo-request bg-slate-950 text-white"
>
    <div class="mx-auto max-w-6xl px-6 py-16">
        <div class="grid gap-8 md:grid-cols-[0.8fr_1.2fr] md:items-center">
            <div>
                <p
                    class="text-xs font-black tracking-[0.16em] text-cyan-200 uppercase"
                >
                    {{ __('capell-theme-saas::generic.demo_request_label') }}
                </p>
                <h2 class="mt-3 text-4xl font-black">{{ $heading }}</h2>
                @if ($summary)
                    <p class="mt-4 text-base leading-8 text-slate-300">
                        {{ $summary }}
                    </p>
                @endif

                <p class="mt-4 text-sm font-bold text-slate-300">
                    {{ $formBuilderAvailable ?? false ? __('capell-theme-saas::generic.demo_request_connected') : __('capell-theme-saas::generic.demo_request_static') }}
                </p>
            </div>

            <div class="grid gap-3">
                @forelse ($items as $item)
                    <article
                        class="grid gap-2 rounded-xl border border-white/10 bg-white/[0.04] p-5"
                    >
                        <p class="text-xs font-black text-cyan-200 uppercase">
                            {{ $item['type'] ?? __('capell-theme-saas::generic.conversion_command_label') }}
                        </p>
                        <h3 class="text-xl font-black">
                            {{ $item['title'] ?? __('capell-theme-saas::generic.demo_request_label') }}
                        </h3>
                        <p class="text-sm leading-6 text-slate-300">
                            {{ $item['summary'] ?? __('capell-theme-saas::generic.demo_request_ready') }}
                        </p>
                    </article>
                @empty
                    <article
                        class="rounded-xl border border-white/10 bg-white/[0.04] p-5"
                    >
                        <h3 class="text-lg font-black">
                            {{ __('capell-theme-saas::generic.demo_request_ready') }}
                        </h3>
                    </article>
                @endforelse
            </div>
        </div>
    </div>
</section>
