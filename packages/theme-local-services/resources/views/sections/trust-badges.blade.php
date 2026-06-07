@php
    $badges = $section->badges ?? $section->items ?? __('capell-theme-local-services::generic.trust_badges_defaults');
    $heading ??= $section->heading ?? __('capell-theme-local-services::generic.trust_badges_label');
    $summary ??= $section->summary ?? __('capell-theme-local-services::generic.trust_badges_summary');
@endphp

<section class="theme-section theme-section-trust-badges bg-[#f7fbf8]">
    <div class="mx-auto max-w-6xl px-6 py-14">
        <div class="grid gap-5 md:grid-cols-[0.75fr_1fr] md:items-end">
            <div>
                <p
                    class="text-xs font-black tracking-[0.16em] text-[#0f766e] uppercase"
                >
                    {{ __('capell-theme-local-services::generic.trust_badges_label') }}
                </p>
                <h2
                    class="mt-3 text-3xl font-black tracking-tight text-[#13231f]"
                >
                    {{ $heading }}
                </h2>
            </div>

            @if ($summary)
                <p class="text-base leading-8 text-slate-600">
                    {{ $summary }}
                </p>
            @endif
        </div>

        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @forelse ($badges as $badge)
                @php
                    $badgeTitle = $badge['title'] ?? $badge['name'] ?? $badge['label'] ?? __('capell-theme-local-services::generic.trust_badge_fallback');
                    $badgeSummary = $badge['summary'] ?? $badge['description'] ?? null;
                    $issuer = $badge['issuer'] ?? $badge['authority'] ?? null;
                    $reference = $badge['reference'] ?? $badge['credential'] ?? null;
                    $badgeUrl = $badge['url'] ?? null;
                    $logoUrl = $badge['image'] ?? $badge['imageUrl'] ?? $badge['logo'] ?? null;
                    $logoAlt = $badge['imageAlt'] ?? $badge['alt'] ?? $badgeTitle;
                @endphp

                <article
                    class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"
                >
                    @if ($logoUrl)
                        <img
                            src="{{ $logoUrl }}"
                            alt="{{ $logoAlt }}"
                            width="96"
                            height="96"
                            loading="lazy"
                            decoding="async"
                            class="h-12 w-12 rounded-lg border border-slate-200 object-contain p-2"
                        />
                    @else
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-lg bg-[#0f766e] text-lg font-black text-white"
                        >
                            ✓
                        </div>
                    @endif

                    <h3 class="mt-4 text-lg font-black text-[#13231f]">
                        @if ($badgeUrl)
                            <a
                                href="{{ $badgeUrl }}"
                                class="hover:text-[#0f766e]"
                            >
                                {{ $badgeTitle }}
                            </a>
                        @else
                            {{ $badgeTitle }}
                        @endif
                    </h3>

                    @if ($badgeSummary)
                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            {{ $badgeSummary }}
                        </p>
                    @endif

                    @if ($issuer || $reference)
                        <p
                            class="mt-4 text-xs font-black tracking-[0.14em] text-[#f97316] uppercase"
                        >
                            {{ collect([$issuer, $reference])->filter()->join(' · ') }}
                        </p>
                    @endif
                </article>
            @empty
                <article
                    class="rounded-xl border border-dashed border-slate-300 bg-white p-6 sm:col-span-2 lg:col-span-4"
                >
                    <h3 class="text-lg font-black text-[#13231f]">
                        {{ __('capell-theme-local-services::generic.trust_badges_empty_title') }}
                    </h3>
                    <p class="mt-2 text-sm text-slate-600">
                        {{ __('capell-theme-local-services::generic.trust_badges_empty_summary') }}
                    </p>
                </article>
            @endforelse
        </div>
    </div>
</section>
