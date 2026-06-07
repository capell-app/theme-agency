@php
    $resources = $section->items ?? __('capell-theme-local-services::generic.resources_defaults');
    $blogAvailable ??= false;
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

        <div
            class="theme-carousel relative mt-8"
            data-carousel="local-services-resources"
        >
            <div
                class="flex gap-4 overflow-x-auto pr-6 pb-2 sm:grid sm:grid-cols-2 sm:overflow-x-visible sm:pr-0 sm:pb-0 lg:grid-cols-3"
                data-carousel-track
            >
                @foreach ($resources as $resource)
                    @if ($blogAvailable && ($resource['url'] ?? null))
                        <a
                            href="{{ $resource['url'] }}"
                            class="min-w-[240px] snap-start rounded-xl border border-stone-200 bg-white p-5 transition hover:border-[#17211c] hover:bg-stone-950 hover:text-white"
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
                            class="min-w-[240px] snap-start rounded-xl border border-stone-200 bg-white p-5 transition hover:border-[#17211c] hover:bg-stone-950 hover:text-white"
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

            <button
                type="button"
                class="theme-carousel-button carousel-prev absolute top-1/2 left-2 hidden -translate-y-1/2 rounded-full border border-stone-200 bg-white p-2 text-sm font-semibold shadow-md"
                aria-label="{{ __('capell-theme-local-services::generic.carousel_previous') }}"
                data-carousel-prev
            >
                ‹
            </button>
            <button
                type="button"
                class="theme-carousel-button carousel-next absolute top-1/2 right-2 hidden -translate-y-1/2 rounded-full border border-stone-200 bg-white p-2 text-sm font-semibold shadow-md"
                aria-label="{{ __('capell-theme-local-services::generic.carousel_next') }}"
                data-carousel-next
            >
                ›
            </button>
        </div>
    </div>
</section>
