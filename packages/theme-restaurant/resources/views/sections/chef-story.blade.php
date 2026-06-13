@php
    $heading = $section->heading ?? ($heading ?? __('capell-theme-restaurant::generic.chef_heading'));
    $summary = $section->summary ?? ($summary ?? __('capell-theme-restaurant::generic.chef_summary'));
    $imageUrl = $section->mediaUrl ?? ($imageUrl ?? null);
    $imageAlt = $section->mediaAlt ?? ($imageAlt ?? $heading);
@endphp

<section class="theme-section restaurant-section-muted px-6 py-16">
    <div
        class="mx-auto grid max-w-6xl gap-8 lg:grid-cols-[0.92fr_1.08fr] lg:items-center"
    >
        <div class="restaurant-frame bg-white p-3">
            @if ($imageUrl)
                <img
                    src="{{ $imageUrl }}"
                    alt="{{ $imageAlt }}"
                    width="960"
                    height="720"
                    loading="lazy"
                    decoding="async"
                    class="aspect-[4/3] w-full object-cover"
                />
            @else
                <div
                    class="restaurant-chef-placeholder aspect-[4/3]"
                    aria-hidden="true"
                ></div>
            @endif
        </div>
        <div>
            <p class="restaurant-eyebrow">
                {{ __('capell-theme-restaurant::generic.chef_label') }}
            </p>
            <h2 class="mt-4 text-4xl leading-tight font-black">
                {{ $heading }}
            </h2>
            @if ($summary)
                <p
                    class="mt-5 text-lg leading-8 text-[var(--restaurant-muted)]"
                >
                    {{ $summary }}
                </p>
            @endif
        </div>
    </div>
</section>
