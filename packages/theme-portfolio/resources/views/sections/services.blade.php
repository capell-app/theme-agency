<section class="theme-section theme-section-services bg-[#f8fafc]">
    @isset($heading)
        <div class="mx-auto flex max-w-5xl flex-col gap-4 px-6 py-14">
            <p class="text-xs font-black tracking-[0.16em] text-slate-500">
                What we build
            </p>
            <h2 class="text-4xl font-black tracking-tight text-[#0f172a]">
                {{ $heading }}
            </h2>
            <p class="max-w-2xl text-lg text-stone-600">
                A modular services layer built for portfolio storytelling that
                converts attention into action.
            </p>
        </div>
    @endisset

    <div class="mx-auto grid max-w-5xl gap-4 px-6 pb-14 md:grid-cols-3">
        @php
            $services = $section->items ?? [];
        @endphp

        @if ($services !== [])
            @foreach ($services as $item)
                <article
                    class="rounded-xl border border-slate-200 bg-white p-5"
                >
                    <p
                        class="text-xs font-black tracking-[0.16em] text-[#0f172a]"
                    >
                        {{ $item['type'] ?? 'SERVICE' }}
                    </p>
                    <h3 class="mt-3 text-lg font-black text-[#0f172a]">
                        {{ $item['title'] ?? '' }}
                    </h3>
                    <p class="mt-3 text-sm text-stone-600">
                        {{ $item['summary'] ?? $item['description'] ?? '' }}
                    </p>
                </article>
            @endforeach
        @else
            <article class="rounded-xl border border-slate-200 bg-white p-5">
                <p class="text-xs font-black tracking-[0.16em] text-[#0f172a]">
                    DISCOVERY
                </p>
                <h3 class="mt-3 text-lg font-black text-[#0f172a]">
                    Brand systems
                </h3>
                <p class="mt-3 text-sm text-stone-600">
                    Identity-led positioning and messaging frameworks.
                </p>
            </article>
            <article class="rounded-xl border border-slate-200 bg-white p-5">
                <p class="text-xs font-black tracking-[0.16em] text-[#0f172a]">
                    DESIGN
                </p>
                <h3 class="mt-3 text-lg font-black text-[#0f172a]">
                    Conversion storytelling
                </h3>
                <p class="mt-3 text-sm text-stone-600">
                    Long-form narratives with visual hierarchy for trust.
                </p>
            </article>
            <article class="rounded-xl border border-slate-200 bg-white p-5">
                <p class="text-xs font-black tracking-[0.16em] text-[#0f172a]">
                    LAUNCH
                </p>
                <h3 class="mt-3 text-lg font-black text-[#0f172a]">
                    Performance tune-up
                </h3>
                <p class="mt-3 text-sm text-stone-600">
                    Rapid iteration on headlines, UI, and conversion points.
                </p>
            </article>
        @endif
    </div>
</section>
