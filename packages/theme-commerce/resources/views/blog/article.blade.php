@php
    $blogAvailable ??= false;
    $title ??= ($article['title'] ?? '');
    $summary ??= ($article['summary'] ?? null);
    $body ??= ($article['body'] ?? null);
@endphp

<main
    class="retail-article bg-[var(--retail-surface)] px-6 py-20 text-[var(--retail-ink)]"
>
    <article class="mx-auto max-w-3xl">
        <header>
            <p
                class="text-xs font-black tracking-widest text-[var(--retail-primary)] uppercase"
            >
                {{ $blogAvailable ? __('capell-theme-commerce::generic.article_label') : __('capell-theme-commerce::generic.resource') }}
            </p>
            <h1 class="mt-4 text-5xl font-black tracking-normal md:text-7xl">
                {{ $title }}
            </h1>
            @if ($summary)
                <p class="mt-6 text-xl leading-8 text-[var(--retail-muted)]">
                    {{ $summary }}
                </p>
            @endif
        </header>

        @if ($body)
            <div
                class="mt-12 rounded-xl border border-stone-200 bg-white p-7 text-lg leading-8 text-[var(--retail-ink)]"
            >
                {{ $body }}
            </div>
        @endif
    </article>
</main>
