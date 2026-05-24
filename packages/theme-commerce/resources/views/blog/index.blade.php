@php
    $blogAvailable ??= false;
    $articles ??= [];
    $heading ??= __('capell-theme-commerce::generic.insights_heading');
    $summary ??= null;
@endphp

<main class="retail-insights-index bg-[#fffaf3] px-6 py-20 text-[#17211c]">
    <div class="mx-auto max-w-6xl">
        <header class="max-w-3xl">
            <p
                class="text-xs font-black uppercase tracking-widest text-[#1f5f4a]"
            >
                {{ $blogAvailable ? __('capell-theme-commerce::generic.insights_label') : __('capell-theme-commerce::generic.resources_label') }}
            </p>
            <h1 class="mt-4 text-5xl font-black tracking-normal md:text-7xl">
                {{ $heading }}
            </h1>
            @if ($summary)
                <p class="mt-5 max-w-2xl text-lg leading-8 text-[#5f6b61]">
                    {{ $summary }}
                </p>
            @endif
        </header>

        <div class="mt-12 grid gap-5 md:grid-cols-3">
            @foreach ($articles as $article)
                @if ($blogAvailable)
                    <a
                        href="{{ $article['url'] ?? '#' }}"
                        class="rounded-xl border border-stone-200 bg-white p-6 transition hover:border-[#1f5f4a] hover:shadow-lg"
                    >
                        <p
                            class="text-xs font-black uppercase tracking-widest text-[#e86f5c]"
                        >
                            {{ $article['type'] ?? __('capell-theme-commerce::generic.insight') }}
                        </p>
                        <h2 class="mt-4 text-2xl font-black tracking-normal">
                            {{ $article['title'] }}
                        </h2>
                        <p class="mt-3 text-sm leading-6 text-[#5f6b61]">
                            {{ $article['summary'] ?? '' }}
                        </p>
                    </a>
                @else
                    <article
                        class="rounded-xl border border-stone-200 bg-white p-6"
                    >
                        <p
                            class="text-xs font-black uppercase tracking-widest text-[#e86f5c]"
                        >
                            {{ __('capell-theme-commerce::generic.resource') }}
                        </p>
                        <h2 class="mt-4 text-2xl font-black tracking-normal">
                            {{ $article['title'] }}
                        </h2>
                        <p class="mt-3 text-sm leading-6 text-[#5f6b61]">
                            {{ $article['summary'] ?? '' }}
                        </p>
                    </article>
                @endif
            @endforeach
        </div>
    </div>
</main>
