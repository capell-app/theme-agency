@php
    $blogAvailable ??= false;
    $articles = $section->items ?? [];
@endphp

<section class="retail-resources bg-[#fffaf3]">
    <div class="px-6">
        <div
            class="flex flex-col justify-between gap-5 md:flex-row md:items-end"
        >
            <div>
                <h2>{{ $section->heading }}</h2>
                @if ($section->summary ?? null)
                    <p class="mt-4 max-w-2xl text-lg">
                        {{ $section->summary }}
                    </p>
                @endif
            </div>
        </div>

        <div class="mt-10 grid gap-4 md:grid-cols-3">
            @foreach ($articles as $article)
                @if ($blogAvailable)
                    <a
                        href="{{ $article['url'] ?? '#' }}"
                        class="retail-resource-card rounded-xl border border-stone-200 bg-white p-6 transition hover:border-[#1f5f4a]"
                    >
                        <p
                            class="text-xs font-black tracking-widest text-[#1f5f4a] uppercase"
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
                    <article
                        class="retail-resource-card rounded-xl border border-stone-200 bg-white p-6"
                    >
                        <p
                            class="text-xs font-black tracking-widest text-[#1f5f4a] uppercase"
                        >
                            {{ __('capell-theme-commerce::generic.resource') }}
                        </p>
                        <h3 class="mt-4 text-xl font-black">
                            {{ $article['title'] }}
                        </h3>
                        <p class="mt-3 text-sm">
                            {{ $article['summary'] ?? '' }}
                        </p>
                    </article>
                @endif
            @endforeach
        </div>
    </div>
</section>
