@php
    $blogAvailable ??= false;
    $title ??= ($article['title'] ?? '');
    $summary ??= ($article['summary'] ?? null);
    $body ??= ($article['body'] ?? null);
@endphp

<main class="retail-article bg-[#fffaf3] px-6 py-20 text-[#17211c]">
    <article class="mx-auto max-w-3xl">
        <header>
            <p
                class="text-xs font-black uppercase tracking-widest text-[#1f5f4a]"
            >
                {{ $blogAvailable ? __('capell-theme-commerce::generic.article_label') : __('capell-theme-commerce::generic.resource') }}
            </p>
            <h1 class="mt-4 text-5xl font-black tracking-normal md:text-7xl">
                {{ $title }}
            </h1>
            @if ($summary)
                <p class="mt-6 text-xl leading-8 text-[#5f6b61]">
                    {{ $summary }}
                </p>
            @endif
        </header>

        @if ($body)
            <div
                class="mt-12 rounded-xl border border-stone-200 bg-white p-7 text-lg leading-8 text-[#17211c]"
            >
                {{ $body }}
            </div>
        @endif
    </article>
</main>
