@php
    $heading = $section->heading ?? ($heading ?? null);
    $summary = $section->summary ?? ($summary ?? __('capell-theme-estate-agents::generic.hero_summary'));
    $eyebrow = $section->eyebrow ?? ($eyebrow ?? __('capell-theme-estate-agents::generic.hero_label'));
    $actions = $section->actions ?? ($actions ?? []);
    $primaryAction = $actions[0] ?? [
        'label' => __('capell-theme-estate-agents::generic.search_label'),
        'url' => '#property-search',
    ];
    $secondaryAction = $actions[1] ?? [
        'label' => __('capell-theme-estate-agents::generic.valuation_label'),
        'url' => '#valuation',
    ];
    $imageUrl = $section->mediaUrl ?? ($imageUrl ?? ($image ?? null));
    $imageAlt = $section->mediaAlt ?? ($imageAlt ?? ($heading ?? __('capell-theme-estate-agents::generic.hero_image_alt')));
@endphp

<section class="theme-section estate-hero px-6 py-16 lg:py-20">
    @isset($heading)
        <div
            class="mx-auto grid max-w-6xl gap-10 lg:grid-cols-[0.92fr_1.08fr] lg:items-center"
        >
            <div>
                <p class="estate-eyebrow">{{ $eyebrow }}</p>
                <h1
                    class="mt-5 max-w-3xl text-5xl leading-none font-black sm:text-6xl"
                >
                    {{ $heading }}
                </h1>
                @if ($summary)
                    <p
                        class="mt-6 max-w-2xl text-lg leading-8 text-[var(--estate-muted)]"
                    >
                        {{ $summary }}
                    </p>
                @endif

                <div class="mt-8 flex flex-wrap gap-3">
                    <a
                        href="{{ $primaryAction['url'] ?? '#property-search' }}"
                        class="estate-button-primary"
                    >
                        {{ $primaryAction['label'] ?? __('capell-theme-estate-agents::generic.search_label') }}
                    </a>
                    <a
                        href="{{ $secondaryAction['url'] ?? '#valuation' }}"
                        class="estate-button-secondary"
                    >
                        {{ $secondaryAction['label'] ?? __('capell-theme-estate-agents::generic.valuation_label') }}
                    </a>
                </div>
            </div>

            <div class="estate-property-frame bg-white p-3">
                @if ($imageUrl)
                    <img
                        src="{{ $imageUrl }}"
                        alt="{{ $imageAlt }}"
                        width="1080"
                        height="780"
                        loading="eager"
                        decoding="async"
                        fetchpriority="high"
                        sizes="(min-width: 1024px) 50vw, 100vw"
                        class="aspect-[18/13] w-full object-cover"
                    />
                @else
                    <div
                        class="estate-hero-board aspect-[18/13] p-5"
                        aria-hidden="true"
                    >
                        <div class="grid h-full grid-cols-[1fr_0.4fr] gap-4">
                            <div class="bg-white/18"></div>
                            <div class="grid gap-4">
                                <span class="bg-white/35"></span>
                                <span class="bg-[var(--estate-lime)]"></span>
                                <span class="bg-white/20"></span>
                            </div>
                        </div>
                    </div>
                @endif
                <dl class="estate-listing-strip grid grid-cols-3">
                    <div>
                        <dt>
                            {{ __('capell-theme-estate-agents::generic.average_sale_label') }}
                        </dt>
                        <dd>
                            {{ __('capell-theme-estate-agents::generic.average_sale_value') }}
                        </dd>
                    </div>
                    <div>
                        <dt>
                            {{ __('capell-theme-estate-agents::generic.viewings_label') }}
                        </dt>
                        <dd>
                            {{ __('capell-theme-estate-agents::generic.viewings_value') }}
                        </dd>
                    </div>
                    <div>
                        <dt>
                            {{ __('capell-theme-estate-agents::generic.branches_label') }}
                        </dt>
                        <dd>
                            {{ __('capell-theme-estate-agents::generic.branches_value') }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    @endisset
</section>
