@php
    $studies = $section->items ?? __('capell-theme-dog-walkers::generic.case_studies_defaults');
    $mobileLayout = $section->mobileLayout ?? 'carousel';
    $useMobileCarousel = $mobileLayout !== 'stack';
@endphp

<section class="theme-section theme-section-case-studies">
    <div class="mx-auto max-w-5xl px-6 py-14">
        @isset($heading)
            <h2 class="text-3xl font-black tracking-tight text-[#17211c]">
                {{ $heading }}
            </h2>
        @endisset

        <p class="mt-4 max-w-2xl text-stone-600">
            {{ __('capell-theme-dog-walkers::generic.case_studies_copy') }}
        </p>
        <div class="@container mt-8">
            <x-capell::section.repeatable-carousel
                carousel-id="dog-walkers-cases"
                @class(['@2xl:hidden' => $useMobileCarousel])
                :enabled="$useMobileCarousel"
            >
                @foreach ($studies as $study)
                    <article
                        @class([
                            'rounded-xl border border-stone-200 bg-white p-5 transition hover:border-[#17211c] hover:bg-stone-950 hover:text-white',
                            'swiper-slide h-auto' => $useMobileCarousel,
                        ])
                    >
                        <p
                            class="text-xs font-black tracking-[0.2em] text-[#17211c]"
                        >
                            {{ $study['metric'] ?? __('capell-theme-dog-walkers::generic.case_study_metric_fallback') }}
                        </p>
                        <h3 class="mt-2 text-lg font-black">
                            {{ $study['title'] }}
                        </h3>
                        <p class="mt-2 text-sm text-stone-600">
                            {{ $study['summary'] ?? '' }}
                        </p>
                    </article>
                @endforeach
            </x-capell::section.repeatable-carousel>

            @if ($useMobileCarousel)
                <div
                    class="hidden gap-4 @2xl:grid @2xl:grid-cols-2 @4xl:grid-cols-3"
                >
                    @foreach ($studies as $study)
                        <article
                            class="rounded-xl border border-stone-200 bg-white p-5 transition hover:border-[#17211c] hover:bg-stone-950 hover:text-white"
                        >
                            <p
                                class="text-xs font-black tracking-[0.2em] text-[#17211c]"
                            >
                                {{ $study['metric'] ?? __('capell-theme-dog-walkers::generic.case_study_metric_fallback') }}
                            </p>
                            <h3 class="mt-2 text-lg font-black">
                                {{ $study['title'] }}
                            </h3>
                            <p class="mt-2 text-sm text-stone-600">
                                {{ $study['summary'] ?? '' }}
                            </p>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>
