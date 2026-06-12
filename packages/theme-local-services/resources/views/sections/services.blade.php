@php
    $services = $section->items ?? __('capell-theme-local-services::generic.services_defaults');
    $mobileLayout = $section->mobileLayout ?? 'carousel';
    $useMobileCarousel = $mobileLayout !== 'stack';
@endphp

<section class="theme-section theme-section-services">
    <div class="mx-auto max-w-5xl px-6 py-14">
        @isset($heading)
            <p class="text-xs font-black tracking-[0.2em] text-slate-500">
                {{ __('capell-theme-local-services::generic.services_label') }}
            </p>
            <h2 class="mt-3 text-4xl font-black tracking-tight text-[#17211c]">
                {{ $heading }}
            </h2>
        @endisset

        <p class="mt-4 max-w-2xl text-stone-600">
            {{ __('capell-theme-local-services::generic.services_copy') }}
        </p>
        <div class="@container mt-8">
            <x-capell::section.repeatable-carousel
                carousel-id="local-services-services"
                @class(['@2xl:hidden' => $useMobileCarousel])
                :enabled="$useMobileCarousel"
            >
                @foreach ($services as $service)
                    <article
                        @class([
                            'rounded-xl border border-stone-200 bg-white p-4 transition hover:-translate-y-1 hover:border-[#17211c] hover:bg-stone-950 hover:text-white',
                            'swiper-slide h-auto' => $useMobileCarousel,
                        ])
                    >
                        <p
                            class="text-xs font-black tracking-widest text-[#17211c] sm:text-stone-500"
                        >
                            {{ $service['category'] ?? __('capell-theme-local-services::generic.service_label') }}
                        </p>
                        <h3 class="mt-2 text-lg font-black">
                            {{ $service['title'] }}
                        </h3>
                        <p class="mt-2 text-sm text-stone-600">
                            {{ $service['summary'] ?? '' }}
                        </p>
                    </article>
                @endforeach
            </x-capell::section.repeatable-carousel>

            @if ($useMobileCarousel)
                <div
                    class="hidden gap-4 @2xl:grid @2xl:grid-cols-2 @4xl:grid-cols-4"
                >
                    @foreach ($services as $service)
                        <article
                            class="rounded-xl border border-stone-200 bg-white p-4 transition hover:-translate-y-1 hover:border-[#17211c] hover:bg-stone-950 hover:text-white"
                        >
                            <p
                                class="text-xs font-black tracking-widest text-[#17211c] sm:text-stone-500"
                            >
                                {{ $service['category'] ?? __('capell-theme-local-services::generic.service_label') }}
                            </p>
                            <h3 class="mt-2 text-lg font-black">
                                {{ $service['title'] }}
                            </h3>
                            <p class="mt-2 text-sm text-stone-600">
                                {{ $service['summary'] ?? '' }}
                            </p>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>
