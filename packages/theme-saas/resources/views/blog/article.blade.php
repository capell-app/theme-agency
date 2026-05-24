@php
    $blogAvailable ??= false;
    $title ??= ($article['title'] ?? '');
    $summary ??= ($article['summary'] ?? null);
    $body ??= ($article['body'] ?? null);
@endphp

<main class="velocity-article bg-white px-6 py-20 text-slate-950">
    <article class="mx-auto max-w-3xl">
        <header>
            <p
                class="text-xs font-black uppercase tracking-widest text-cyan-700"
            >
                {{ $blogAvailable ? __('capell-theme-saas::generic.article_label') : __('capell-theme-saas::generic.resource') }}
            </p>
            <h1 class="mt-4 text-5xl font-black tracking-normal md:text-7xl">
                {{ $title }}
            </h1>
            @if ($summary)
                <p class="mt-6 text-xl leading-8 text-slate-600">
                    {{ $summary }}
                </p>
            @endif
        </header>

        @if ($body)
            <div class="mt-12 space-y-6 text-lg leading-8 text-slate-700">
                {{ $body }}
            </div>
        @endif
    </article>
</main>
