@php
    $blogAvailable ??= false;
    $title ??= ($article['title'] ?? '');
    $summary ??= ($article['summary'] ?? null);
    $body ??= ($article['body'] ?? null);
@endphp

<main
    class="healthcare-article bg-[var(--healthcare-surface)] px-6 py-20 text-[var(--healthcare-ink)]"
>
    <article class="mx-auto max-w-3xl">
        <header>
            <p
                class="text-xs font-black tracking-widest text-[var(--healthcare-primary)] uppercase"
            >
                {{ $blogAvailable ? __('capell-theme-healthcare::generic.article_label') : __('capell-theme-healthcare::generic.resource') }}
            </p>
            <h1 class="mt-4 text-5xl font-black tracking-normal md:text-7xl">
                {{ $title }}
            </h1>
            @if ($summary)
                <p
                    class="mt-6 text-xl leading-8 text-[var(--healthcare-muted)]"
                >
                    {{ $summary }}
                </p>
            @endif
        </header>

        @if ($body)
            <div
                class="mt-12 rounded-xl border border-stone-200 bg-white p-7 text-lg leading-8 text-[var(--healthcare-ink)]"
            >
                {{ $body }}
            </div>
        @endif
    </article>
</main>
