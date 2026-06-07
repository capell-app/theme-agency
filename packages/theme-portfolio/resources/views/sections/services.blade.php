<section class="theme-section theme-section-services bg-[#f8fafc]">
    @isset($heading)
        <div class="mx-auto flex max-w-5xl flex-col gap-4 px-6 py-14">
            <p class="text-xs font-black tracking-[0.16em] text-slate-500">
                {{ __('capell-theme-portfolio::generic.services_heading_label') }}
            </p>
            <h2 class="text-4xl font-black tracking-tight text-[#0f172a]">
                {{ $heading }}
            </h2>
            <p class="max-w-2xl text-lg text-stone-600">
                {{ __('capell-theme-portfolio::generic.services_summary') }}
            </p>
        </div>
    @endisset

    <div class="mx-auto grid max-w-5xl gap-4 px-6 pb-14 md:grid-cols-3">
        @php
            $services = $section->items ?? [];
        @endphp

        @forelse ($services as $item)
            <article class="rounded-xl border border-slate-200 bg-white p-5">
                <p class="text-xs font-black tracking-[0.16em] text-[#0f172a]">
                    {{ $item['type'] ?? __('capell-theme-portfolio::generic.service_type_label') }}
                </p>
                <h3 class="mt-3 text-lg font-black text-[#0f172a]">
                    {{ $item['title'] ?? '' }}
                </h3>
                <p class="mt-3 text-sm text-stone-600">
                    {{ $item['summary'] ?? $item['description'] ?? '' }}
                </p>
            </article>
        @empty
            <article
                class="rounded-xl border border-dashed border-slate-300 bg-white p-6 md:col-span-3"
            >
                <h3 class="text-lg font-black text-[#0f172a]">
                    {{ __('capell-theme-portfolio::generic.premium_layout_ready') }}
                </h3>
                <p class="mt-2 text-sm text-slate-600">
                    {{ __('capell-theme-portfolio::generic.premium_layout_empty') }}
                </p>
            </article>
        @endforelse
    </div>
</section>
