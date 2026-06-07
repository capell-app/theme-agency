@php
    $logos = $section->items ?? [];
    $heading ??= $section->heading ?? __('capell-theme-portfolio::generic.client_logos_label');
    $summary ??= $section->summary ?? __('capell-theme-portfolio::generic.client_logos_summary');
@endphp

<section class="theme-section theme-section-client-logos bg-white">
    <div class="mx-auto max-w-6xl px-6 py-14">
        <div class="grid gap-5 md:grid-cols-[0.7fr_1fr] md:items-end">
            <div>
                <p
                    class="text-xs font-black tracking-[0.16em] text-[#7c2d12] uppercase"
                >
                    {{ __('capell-theme-portfolio::generic.client_logos_label') }}
                </p>
                <h2 class="mt-3 text-4xl font-black text-[#0f172a]">
                    {{ $heading }}
                </h2>
            </div>

            @if ($summary)
                <p class="text-base leading-8 text-slate-600">
                    {{ $summary }}
                </p>
            @endif
        </div>

        <div class="mt-8 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
            @forelse ($logos as $logo)
                @php
                    $logoName = $logo['title'] ?? $logo['name'] ?? $logo['label'] ?? __('capell-theme-portfolio::generic.client_logo_fallback');
                    $logoUrl = $logo['image'] ?? $logo['imageUrl'] ?? $logo['logo'] ?? null;
                @endphp

                <article
                    class="flex min-h-28 items-center justify-center rounded-xl border border-slate-200 bg-[#f8fafc] p-5 text-center"
                >
                    @if ($logoUrl)
                        <img
                            src="{{ $logoUrl }}"
                            alt="{{ $logo['alt'] ?? $logoName }}"
                            width="180"
                            height="80"
                            loading="lazy"
                            decoding="async"
                            class="max-h-14 w-auto object-contain"
                        />
                    @else
                        <span
                            class="text-sm font-black tracking-[0.12em] text-slate-500 uppercase"
                        >
                            {{ $logoName }}
                        </span>
                    @endif
                </article>
            @empty
                <article
                    class="rounded-xl border border-dashed border-slate-300 bg-[#f8fafc] p-6 sm:col-span-2 lg:col-span-5"
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
    </div>
</section>
