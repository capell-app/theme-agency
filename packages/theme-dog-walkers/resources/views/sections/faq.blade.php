@php
    $items ??= $section->items ?? [];
    $heading ??= $section->heading ?? __('capell-theme-dog-walkers::generic.enquiry_estimator_label');
    $summary ??= $section->summary ?? null;
@endphp

<section class="theme-section theme-section-enquiry-estimator bg-[#f7fbf8]">
    <div class="mx-auto max-w-6xl px-6 py-16">
        <div class="grid gap-6 md:grid-cols-[0.8fr_1.2fr]">
            <div>
                <p
                    class="text-xs font-black tracking-[0.16em] text-[#0f766e] uppercase"
                >
                    {{ __('capell-theme-dog-walkers::generic.enquiry_estimator_label') }}
                </p>
                <h2 class="mt-3 text-4xl font-black text-[#13231f]">
                    {{ $heading }}
                </h2>
                @if ($summary)
                    <p class="mt-4 text-base leading-8 text-slate-600">
                        {{ $summary }}
                    </p>
                @endif
            </div>
            <div class="grid gap-3">
                @forelse ($items as $item)
                    <article class="border border-emerald-100 bg-white p-5">
                        <p class="text-xs font-black text-[#f97316] uppercase">
                            {{ $item['type'] ?? __('capell-theme-dog-walkers::generic.enquiry_signal') }}
                        </p>
                        <h3 class="mt-2 text-lg font-black text-[#13231f]">
                            {{ $item['title'] ?? __('capell-theme-dog-walkers::generic.enquiry_field_walk') }}
                        </h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            {{ $item['summary'] ?? __('capell-theme-dog-walkers::generic.enquiry_estimator_ready') }}
                        </p>
                    </article>
                @empty
                    <article
                        class="border border-dashed border-emerald-200 bg-white p-6"
                    >
                        <h3 class="text-lg font-black text-[#13231f]">
                            {{ __('capell-theme-dog-walkers::generic.enquiry_estimator_ready') }}
                        </h3>
                    </article>
                @endforelse
            </div>
        </div>
    </div>
</section>
