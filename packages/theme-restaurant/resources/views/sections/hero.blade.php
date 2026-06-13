@php
    $heading = $section->heading ?? ($heading ?? null);
    $summary = $section->summary ?? ($summary ?? __('capell-theme-restaurant::generic.hero_summary'));
    $eyebrow = $section->eyebrow ?? ($eyebrow ?? __('capell-theme-restaurant::generic.hero_label'));
    $actions = $section->actions ?? ($actions ?? []);
    $primaryAction = $actions[0] ?? [
        'label' => __('capell-theme-restaurant::generic.reserve_label'),
        'url' => '#reservations',
    ];
    $secondaryAction = $actions[1] ?? [
        'label' => __('capell-theme-restaurant::generic.menu_label'),
        'url' => '#menu',
    ];
    $imageUrl = $section->mediaUrl ?? ($imageUrl ?? ($image ?? null));
    $imageAlt = $section->mediaAlt ?? ($imageAlt ?? ($heading ?? __('capell-theme-restaurant::generic.hero_image_alt')));
@endphp

<section class="theme-section restaurant-hero px-6 py-16 lg:py-20">
    @isset($heading)
        <div
            class="mx-auto grid max-w-6xl gap-10 lg:grid-cols-[1.05fr_0.95fr] lg:items-center"
        >
            <div>
                <p class="restaurant-eyebrow">{{ $eyebrow }}</p>
                <h1
                    class="mt-5 max-w-3xl text-5xl leading-none font-black sm:text-6xl"
                >
                    {{ $heading }}
                </h1>
                @if ($summary)
                    <p
                        class="mt-6 max-w-2xl text-lg leading-8 text-[var(--restaurant-muted)]"
                    >
                        {{ $summary }}
                    </p>
                @endif

                <div class="mt-8 flex flex-wrap gap-3">
                    <a
                        href="{{ $primaryAction['url'] ?? '#reservations' }}"
                        class="restaurant-button-primary"
                    >
                        {{ $primaryAction['label'] ?? __('capell-theme-restaurant::generic.reserve_label') }}
                    </a>
                    <a
                        href="{{ $secondaryAction['url'] ?? '#menu' }}"
                        class="restaurant-button-secondary"
                    >
                        {{ $secondaryAction['label'] ?? __('capell-theme-restaurant::generic.menu_label') }}
                    </a>
                </div>

                <dl
                    class="mt-10 grid max-w-2xl grid-cols-3 border border-[var(--restaurant-border)] bg-white"
                >
                    <div class="p-4">
                        <dt class="restaurant-small-label">
                            {{ __('capell-theme-restaurant::generic.service_label') }}
                        </dt>
                        <dd class="mt-2 text-2xl font-black">
                            {{ __('capell-theme-restaurant::generic.service_value') }}
                        </dd>
                    </div>
                    <div class="border-l border-[var(--restaurant-border)] p-4">
                        <dt class="restaurant-small-label">
                            {{ __('capell-theme-restaurant::generic.room_label') }}
                        </dt>
                        <dd class="mt-2 text-2xl font-black">
                            {{ __('capell-theme-restaurant::generic.room_value') }}
                        </dd>
                    </div>
                    <div class="border-l border-[var(--restaurant-border)] p-4">
                        <dt class="restaurant-small-label">
                            {{ __('capell-theme-restaurant::generic.last_table_label') }}
                        </dt>
                        <dd class="mt-2 text-2xl font-black">
                            {{ __('capell-theme-restaurant::generic.last_table_value') }}
                        </dd>
                    </div>
                </dl>
            </div>

            <div class="restaurant-frame bg-white p-3">
                @if ($imageUrl)
                    <img
                        src="{{ $imageUrl }}"
                        alt="{{ $imageAlt }}"
                        width="1040"
                        height="860"
                        loading="eager"
                        decoding="async"
                        fetchpriority="high"
                        sizes="(min-width: 1024px) 46vw, 100vw"
                        class="aspect-[6/5] w-full object-cover"
                    />
                @else
                    <div
                        class="restaurant-hero-board aspect-[6/5] p-6"
                        aria-hidden="true"
                    >
                        <div class="flex h-full flex-col justify-between">
                            <div class="flex items-start justify-between gap-6">
                                <span class="restaurant-pill">
                                    {{ __('capell-theme-restaurant::generic.tonight_label') }}
                                </span>
                                <span class="text-5xl font-black text-white/90">
                                    12
                                </span>
                            </div>
                            <div>
                                <div class="h-3 w-3/4 bg-white/60"></div>
                                <div class="mt-3 h-3 w-1/2 bg-white/35"></div>
                                <div class="mt-8 grid grid-cols-3 gap-3">
                                    <span class="h-16 bg-white/20"></span>
                                    <span
                                        class="h-16 bg-[var(--restaurant-copper)]"
                                    ></span>
                                    <span class="h-16 bg-white/15"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endisset
</section>
