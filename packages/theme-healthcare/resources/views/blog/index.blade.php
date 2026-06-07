@php
    $blogAvailable ??= false;
    $articles ??= [];
    $heading ??= __('capell-theme-healthcare::generic.insights_heading');
    $summary ??= null;
@endphp

<main
    class="healthcare-insights-index bg-[var(--healthcare-surface)] px-6 py-20 text-[var(--healthcare-ink)]"
>
    <div class="mx-auto max-w-6xl">
        <header class="max-w-3xl">
            <p
                class="text-xs font-black tracking-widest text-[var(--healthcare-primary)] uppercase"
            >
                {{ $blogAvailable ? __('capell-theme-healthcare::generic.insights_label') : __('capell-theme-healthcare::generic.resources_label') }}
            </p>
            <h1 class="mt-4 text-5xl font-black tracking-normal md:text-7xl">
                {{ $heading }}
            </h1>
            @if ($summary)
                <p
                    class="mt-5 max-w-2xl text-lg leading-8 text-[var(--healthcare-muted)]"
                >
                    {{ $summary }}
                </p>
            @endif
        </header>

        <div class="mt-12 grid gap-5 md:grid-cols-3">
            @foreach ($articles as $article)
                @if ($blogAvailable)
                    <a
                        href="{{ $article['url'] ?? '#' }}"
                        class="rounded-xl border border-stone-200 bg-white p-6 transition hover:border-[var(--healthcare-primary)] hover:shadow-lg"
                    >
                        <p
                            class="text-xs font-black tracking-widest text-[var(--healthcare-accent)] uppercase"
                        >
                            {{ $article['type'] ?? __('capell-theme-healthcare::generic.insight') }}
                        </p>
                        <h2 class="mt-4 text-2xl font-black tracking-normal">
                            {{ $article['title'] }}
                        </h2>
                        <p
                            class="mt-3 text-sm leading-6 text-[var(--healthcare-muted)]"
                        >
                            {{ $article['summary'] ?? '' }}
                        </p>
                    </a>
                @else
                    <article
                        class="rounded-xl border border-stone-200 bg-white p-6"
                    >
                        <p
                            class="text-xs font-black tracking-widest text-[var(--healthcare-accent)] uppercase"
                        >
                            {{ __('capell-theme-healthcare::generic.resource') }}
                        </p>
                        <h2 class="mt-4 text-2xl font-black tracking-normal">
                            {{ $article['title'] }}
                        </h2>
                        <p
                            class="mt-3 text-sm leading-6 text-[var(--healthcare-muted)]"
                        >
                            {{ $article['summary'] ?? '' }}
                        </p>
                    </article>
                @endif
            @endforeach
        </div>
    </div>
</main>
