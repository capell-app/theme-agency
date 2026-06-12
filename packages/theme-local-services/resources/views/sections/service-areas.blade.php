@php
    $sectionHeading = $heading ?? ($section->heading ?? null);
    $areas = $section->items ?? $items ?? __('capell-theme-local-services::generic.service_area_defaults');
    $areas = is_iterable($areas) ? $areas : [];
    $mobileLayout = $section->mobileLayout ?? 'carousel';
    $useMobileCarousel = $mobileLayout !== 'stack';
@endphp

<section class="theme-section theme-section-service-areas">
    <div class="mx-auto max-w-5xl px-6 py-14">
        @if ($sectionHeading)
            <h2 class="text-4xl font-black tracking-tight text-[#17211c]">
                {{ $sectionHeading }}
            </h2>
        @endif

        <p class="mx-auto mt-4 max-w-2xl text-stone-600">
            {{ __('capell-theme-local-services::generic.service_areas_copy') }}
        </p>

        <div class="@container mt-8">
            <x-capell::section.repeatable-carousel
                carousel-id="local-services-areas"
                @class(['@2xl:hidden' => $useMobileCarousel])
                :enabled="$useMobileCarousel"
            >
                @forelse ($areas as $area)
                    @php
                        $areaLabel = is_scalar($area) ? (string) $area : '';

                        if (is_array($area)) {
                            $areaLabelValue = $area['label'] ?? $area['title'] ?? $area['name'] ?? null;
                            $areaPostcodeValue = $area['postcode'] ?? $area['postcodePrefix'] ?? null;
                            $areaLabel = is_scalar($areaLabelValue) ? (string) $areaLabelValue : $areaLabel;
                            $areaUrl = is_scalar($area['url'] ?? null) ? (string) $area['url'] : '';
                            $areaPostcode = is_scalar($areaPostcodeValue) ? (string) $areaPostcodeValue : '';
                        } else {
                            $areaUrl = '';
                            $areaPostcode = '';
                        }

                        $areaContent = trim($areaLabel . ($areaPostcode !== '' ? ' ' . $areaPostcode : ''));
                    @endphp

                    @if ($areaContent !== '')
                        @if ($areaUrl !== '')
                            <a
                                href="{{ $areaUrl }}"
                                @class([
                                    'rounded-2xl border border-stone-200 bg-[#f8fafc] px-5 py-4 text-sm font-bold text-[#17211c] transition hover:border-[#17211c] hover:bg-[#17211c] hover:text-white',
                                    'swiper-slide h-auto' => $useMobileCarousel,
                                ])
                            >
                                {{ $areaContent }}
                            </a>
                        @else
                            <span
                                @class([
                                    'rounded-2xl border border-stone-200 bg-[#f8fafc] px-5 py-4 text-sm font-bold text-[#17211c]',
                                    'swiper-slide h-auto' => $useMobileCarousel,
                                ])
                            >
                                {{ $areaContent }}
                            </span>
                        @endif
                    @endif
                @empty
                    <p
                        @class([
                            'rounded-2xl border border-dashed border-stone-300 bg-[#f8fafc] px-5 py-4 text-sm font-bold text-stone-600',
                            'swiper-slide h-auto' => $useMobileCarousel,
                        ])
                    >
                        {{ __('capell-theme-local-services::generic.service_areas_empty') }}
                    </p>
                @endforelse
            </x-capell::section.repeatable-carousel>

            @if ($useMobileCarousel)
                <div
                    class="hidden gap-4 @2xl:grid @2xl:grid-cols-2 @4xl:grid-cols-3"
                >
                    @forelse ($areas as $area)
                        @php
                            $areaLabel = is_scalar($area) ? (string) $area : '';

                            if (is_array($area)) {
                                $areaLabelValue = $area['label'] ?? $area['title'] ?? $area['name'] ?? null;
                                $areaPostcodeValue = $area['postcode'] ?? $area['postcodePrefix'] ?? null;
                                $areaLabel = is_scalar($areaLabelValue) ? (string) $areaLabelValue : $areaLabel;
                                $areaUrl = is_scalar($area['url'] ?? null) ? (string) $area['url'] : '';
                                $areaPostcode = is_scalar($areaPostcodeValue) ? (string) $areaPostcodeValue : '';
                            } else {
                                $areaUrl = '';
                                $areaPostcode = '';
                            }

                            $areaContent = trim($areaLabel . ($areaPostcode !== '' ? ' ' . $areaPostcode : ''));
                        @endphp

                        @if ($areaContent !== '')
                            @if ($areaUrl !== '')
                                <a
                                    href="{{ $areaUrl }}"
                                    class="rounded-2xl border border-stone-200 bg-[#f8fafc] px-5 py-4 text-sm font-bold text-[#17211c] transition hover:border-[#17211c] hover:bg-[#17211c] hover:text-white"
                                >
                                    {{ $areaContent }}
                                </a>
                            @else
                                <span
                                    class="rounded-2xl border border-stone-200 bg-[#f8fafc] px-5 py-4 text-sm font-bold text-[#17211c]"
                                >
                                    {{ $areaContent }}
                                </span>
                            @endif
                        @endif
                    @empty
                        <p
                            class="rounded-2xl border border-dashed border-stone-300 bg-[#f8fafc] px-5 py-4 text-sm font-bold text-stone-600"
                        >
                            {{ __('capell-theme-local-services::generic.service_areas_empty') }}
                        </p>
                    @endforelse
                </div>
            @endif
        </div>
    </div>
</section>
