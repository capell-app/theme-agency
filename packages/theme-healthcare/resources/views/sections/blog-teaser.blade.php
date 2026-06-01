@php
    $blogAvailable ??= false;
    $articles = $section->items ?? [];
    $articleCount = is_countable($articles) ? count($articles) : 0;
    $usesCarousel = $articleCount > 8;
    $gridClass = match ($articleCount) {
        1 => 'grid gap-4',
        2 => 'grid gap-4 md:grid-cols-2',
        3 => 'grid gap-4 md:grid-cols-3',
        default => 'grid gap-4 sm:grid-cols-2 lg:grid-cols-4',
    };
@endphp

@if (in_array($section->variant ?? null, ['gallery', 'pathways', 'spotlight'], true))
    @include('capell-foundation-theme::theme.sections.content-listing', ['section' => $section])
@else
    <section class="healthcare-resources bg-[#f6fbfd]">
        <div class="px-6">
            <div
                class="flex flex-col justify-between gap-5 md:flex-row md:items-end"
            >
                <div>
                    <h2
                        class="text-4xl font-black tracking-tight text-[#14323a]"
                    >
                        {{ $section->heading }}
                    </h2>
                    @if ($section->summary ?? null)
                        <p class="mt-4 max-w-2xl text-lg text-stone-600">
                            {{ $section->summary }}
                        </p>
                    @endif
                </div>
            </div>

            <div
                class="theme-carousel relative mt-10"
                data-carousel="healthcare-blog-teaser"
            >
                <div
                    class="{{ $usesCarousel ? 'flex snap-x snap-mandatory [scrollbar-width:none] gap-4 overflow-x-auto pr-6 pb-2 [&::-webkit-scrollbar]:hidden' : $gridClass }}"
                    data-carousel-track
                >
                    @foreach ($articles as $article)
                        @php
                            $imageUrl = $article['imageUrl'] ?? ($article['mediaUrl'] ?? null);
                            $imageAlt = $article['imageAlt'] ?? ($article['mediaAlt'] ?? ($article['title'] ?? ''));
                            $resourceType = $article['type'] ?? __('capell-theme-healthcare::generic.article_label');
                        @endphp

                        @if ($blogAvailable)
                            <a
                                href="{{ $article['url'] ?? '#' }}"
                                class="{{ $usesCarousel ? 'min-w-[270px] snap-start sm:min-w-[300px]' : '' }} healthcare-resource-card overflow-hidden rounded-xl border border-stone-200 bg-white transition hover:-translate-y-1 hover:border-[#0f766e] hover:shadow-lg"
                            >
                                @include('capell-theme-healthcare::sections.partials.resource-card-media', [
                                    'imageUrl' => $imageUrl,
                                    'imageAlt' => $imageAlt,
                                    'resourceType' => $resourceType,
                                ])
                                <span class="block p-6">
                                    <span
                                        class="text-xs font-black tracking-widest text-[#0f766e] uppercase"
                                    >
                                        {{ $resourceType }}
                                    </span>
                                    <span class="mt-4 block text-xl font-black">
                                        {{ $article['title'] }}
                                    </span>
                                    <span class="mt-3 block text-sm">
                                        {{ $article['summary'] ?? '' }}
                                    </span>
                                    <span
                                        class="mt-5 inline-flex rounded-full bg-[#e0f2f1] px-3 py-1 text-xs font-black text-[#0f766e]"
                                    >
                                        {{ __('capell-theme-healthcare::generic.clinical_review') }}
                                    </span>
                                </span>
                            </a>
                        @else
                            <article
                                class="{{ $usesCarousel ? 'min-w-[270px] snap-start sm:min-w-[300px]' : '' }} healthcare-resource-card overflow-hidden rounded-xl border border-stone-200 bg-white"
                            >
                                @include('capell-theme-healthcare::sections.partials.resource-card-media', [
                                    'imageUrl' => $imageUrl,
                                    'imageAlt' => $imageAlt,
                                    'resourceType' => __('capell-theme-healthcare::generic.resource'),
                                ])
                                <div class="p-6">
                                    <p
                                        class="text-xs font-black tracking-widest text-[#0f766e] uppercase"
                                    >
                                        {{ __('capell-theme-healthcare::generic.resource') }}
                                    </p>
                                    <h3 class="mt-4 text-xl font-black">
                                        {{ $article['title'] }}
                                    </h3>
                                    <p class="mt-3 text-sm">
                                        {{ $article['summary'] ?? '' }}
                                    </p>
                                    <p
                                        class="mt-5 inline-flex rounded-full bg-[#e0f2f1] px-3 py-1 text-xs font-black text-[#0f766e]"
                                    >
                                        {{ __('capell-theme-healthcare::generic.clinical_review') }}
                                    </p>
                                </div>
                            </article>
                        @endif
                    @endforeach
                </div>

                <button
                    type="button"
                    class="theme-carousel-button carousel-prev absolute top-1/2 left-2 hidden -translate-y-1/2 rounded-full border border-stone-200 bg-white p-2 text-sm font-semibold shadow-md"
                    aria-label="{{ __('capell-theme-healthcare::generic.carousel_previous') }}"
                    data-carousel-prev
                >
                    ‹
                </button>
                <button
                    type="button"
                    class="theme-carousel-button carousel-next absolute top-1/2 right-2 hidden -translate-y-1/2 rounded-full border border-stone-200 bg-white p-2 text-sm font-semibold shadow-md"
                    aria-label="{{ __('capell-theme-healthcare::generic.carousel_next') }}"
                    data-carousel-next
                >
                    ›
                </button>
            </div>
        </div>
    </section>
@endif
