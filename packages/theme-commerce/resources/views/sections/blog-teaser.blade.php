@php
    $blogAvailable ??= false;
    $articles = $section->items ?? [];
@endphp

<section class="retail-resources bg-[var(--retail-surface)]">
    <div class="px-6">
        <div
            class="flex flex-col justify-between gap-5 md:flex-row md:items-end"
        >
            <div>
                <h2
                    class="text-4xl font-black tracking-tight text-[var(--retail-ink)]"
                >
                    {{ $section->heading }}
                </h2>
                @if ($section->summary ?? null)
                    <p class="mt-4 max-w-2xl text-lg">
                        {{ $section->summary }}
                    </p>
                @endif
            </div>
        </div>

        <div
            class="theme-carousel relative mt-10"
            data-carousel="commerce-resources"
        >
            <div
                class="flex snap-x snap-mandatory [scrollbar-width:none] gap-4 overflow-x-auto pr-6 pb-2 md:grid md:grid-cols-3 [&::-webkit-scrollbar]:hidden"
                data-carousel-track
            >
                @foreach ($articles as $article)
                    @if ($blogAvailable && isset($article['url']))
                        <a
                            href="{{ $article['url'] }}"
                            class="retail-resource-card-hover rounded-xl border border-stone-200 bg-white p-6"
                            style="min-width: 260px"
                        >
                            <p
                                class="text-xs font-black tracking-widest text-[var(--retail-primary)] uppercase"
                            >
                                {{ $article['type'] ?? __('capell-theme-commerce::generic.article_label') }}
                            </p>
                            <h3 class="mt-4 text-xl font-black">
                                {{ $article['title'] }}
                            </h3>
                            <p class="mt-3 text-sm">
                                {{ $article['summary'] ?? '' }}
                            </p>
                        </a>
                    @else
                        <div
                            class="retail-resource-card rounded-xl border border-stone-200 bg-white p-6"
                            style="min-width: 260px"
                        >
                            <p
                                class="text-xs font-black tracking-widest text-[var(--retail-primary)] uppercase"
                            >
                                {{ __('capell-theme-commerce::generic.resource') }}
                            </p>
                            <h3 class="mt-4 text-xl font-black">
                                {{ $article['title'] }}
                            </h3>
                            <p class="mt-3 text-sm">
                                {{ $article['summary'] ?? '' }}
                            </p>
                        </div>
                    @endif
                @endforeach
            </div>

            <button
                type="button"
                class="theme-carousel-button carousel-prev absolute top-1/2 left-2 hidden -translate-y-1/2 rounded-full border border-stone-200 bg-white p-2 text-sm font-semibold shadow-md"
                aria-label="{{ __('capell-theme-commerce::generic.carousel_previous') }}"
                data-carousel-prev
            >
                ‹
            </button>
            <button
                type="button"
                class="theme-carousel-button carousel-next absolute top-1/2 right-2 hidden -translate-y-1/2 rounded-full border border-stone-200 bg-white p-2 text-sm font-semibold shadow-md"
                aria-label="{{ __('capell-theme-commerce::generic.carousel_next') }}"
                data-carousel-next
            >
                ›
            </button>
        </div>
    </div>
</section>
