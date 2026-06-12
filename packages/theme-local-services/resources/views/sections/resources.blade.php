@php
    $resources = $section->items ?? __('capell-theme-local-services::generic.resources_defaults');
    $blogAvailable ??= false;
    $mobileLayout = $section->mobileLayout ?? 'carousel';
    $useMobileCarousel = $mobileLayout !== 'stack';
@endphp

<section class="theme-section theme-section-resources">
    <div class="mx-auto max-w-5xl px-6 py-14">
        @isset($heading)
            <h2 class="text-4xl font-black tracking-tight text-[#17211c]">
                {{ $heading }}
            </h2>
        @endisset

        <p class="mt-4 max-w-2xl text-stone-600">
            {{ $blogAvailable ?? false ? __('capell-theme-local-services::generic.resources_connected') : __('capell-theme-local-services::generic.resources_static') }}
        </p>

        <div class="@container mt-8">
            <x-capell::section.repeatable-carousel
                carousel-id="local-services-resources"
                @class(['@2xl:hidden' => $useMobileCarousel])
                :enabled="$useMobileCarousel"
            >
                @foreach ($resources as $resource)
                    @if ($blogAvailable && ($resource['url'] ?? null))
                        <a
                            href="{{ $resource['url'] }}"
                            @class([
                                'rounded-xl border border-stone-200 bg-white p-5 transition hover:border-[#17211c] hover:bg-stone-950 hover:text-white',
                                'swiper-slide h-auto' => $useMobileCarousel,
                            ])
                        >
                            <p
                                class="text-xs font-black tracking-[0.2em] text-[#17211c] uppercase"
                            >
                                {{ __('capell-theme-local-services::generic.resource_label') }}
                            </p>
                            <h3 class="mt-2 text-lg font-black">
                                {{ $resource['title'] }}
                            </h3>
                            <p class="mt-2 text-sm">
                                {{ $resource['summary'] }}
                            </p>
                        </a>
                    @else
                        <article
                            @class([
                                'rounded-xl border border-stone-200 bg-white p-5 transition hover:border-[#17211c] hover:bg-stone-950 hover:text-white',
                                'swiper-slide h-auto' => $useMobileCarousel,
                            ])
                        >
                            <p
                                class="text-xs font-black tracking-[0.2em] text-[#17211c] uppercase"
                            >
                                {{ __('capell-theme-local-services::generic.resource_label') }}
                            </p>
                            <h3 class="mt-2 text-lg font-black">
                                {{ $resource['title'] }}
                            </h3>
                            <p class="mt-2 text-sm">
                                {{ $resource['summary'] }}
                            </p>
                        </article>
                    @endif
                @endforeach
            </x-capell::section.repeatable-carousel>

            @if ($useMobileCarousel)
                <div
                    class="hidden gap-4 @2xl:grid @2xl:grid-cols-2 @4xl:grid-cols-3"
                >
                    @foreach ($resources as $resource)
                        @if ($blogAvailable && ($resource['url'] ?? null))
                            <a
                                href="{{ $resource['url'] }}"
                                class="rounded-xl border border-stone-200 bg-white p-5 transition hover:border-[#17211c] hover:bg-stone-950 hover:text-white"
                            >
                                <p
                                    class="text-xs font-black tracking-[0.2em] text-[#17211c] uppercase"
                                >
                                    {{ __('capell-theme-local-services::generic.resource_label') }}
                                </p>
                                <h3 class="mt-2 text-lg font-black">
                                    {{ $resource['title'] }}
                                </h3>
                                <p class="mt-2 text-sm">
                                    {{ $resource['summary'] }}
                                </p>
                            </a>
                        @else
                            <article
                                class="rounded-xl border border-stone-200 bg-white p-5 transition hover:border-[#17211c] hover:bg-stone-950 hover:text-white"
                            >
                                <p
                                    class="text-xs font-black tracking-[0.2em] text-[#17211c] uppercase"
                                >
                                    {{ __('capell-theme-local-services::generic.resource_label') }}
                                </p>
                                <h3 class="mt-2 text-lg font-black">
                                    {{ $resource['title'] }}
                                </h3>
                                <p class="mt-2 text-sm">
                                    {{ $resource['summary'] }}
                                </p>
                            </article>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>
