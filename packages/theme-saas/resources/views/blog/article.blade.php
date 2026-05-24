@php
    $blogAvailable ??= false;
    $title ??= ($article['title'] ?? '');
    $summary ??= ($article['summary'] ?? null);
    $body ??= ($article['body'] ?? null);
    $archiveUrl ??= '/blog';
    $previousArticle ??= null;
    $nextArticle ??= null;
    $suggestions ??= [];
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

        <nav
            class="mt-12 grid gap-3 border-t border-slate-200 pt-8 sm:grid-cols-3"
            aria-label="{{ __('capell-theme-saas::generic.article_navigation') }}"
        >
            @if ($previousArticle)
                <a
                    class="rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm font-black text-slate-950 no-underline hover:border-cyan-700"
                    href="{{ $previousArticle['url'] ?? '#' }}"
                >
                    <span
                        class="block text-xs uppercase tracking-widest text-cyan-700"
                    >
                        {{ __('capell-theme-saas::generic.previous_article') }}
                    </span>
                    {{ $previousArticle['title'] ?? '' }}
                </a>
            @endif

            <a
                class="rounded-lg border border-slate-200 bg-slate-950 p-4 text-center text-sm font-black text-white no-underline hover:bg-cyan-700 sm:col-start-2"
                href="{{ $archiveUrl }}"
            >
                {{ __('capell-theme-saas::generic.archive') }}
            </a>

            @if ($nextArticle)
                <a
                    class="rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm font-black text-slate-950 no-underline hover:border-cyan-700 sm:text-right"
                    href="{{ $nextArticle['url'] ?? '#' }}"
                >
                    <span
                        class="block text-xs uppercase tracking-widest text-cyan-700"
                    >
                        {{ __('capell-theme-saas::generic.next_article') }}
                    </span>
                    {{ $nextArticle['title'] ?? '' }}
                </a>
            @endif
        </nav>

        @if ($suggestions !== [])
            <section class="mt-12 border-t border-slate-200 pt-8">
                <p
                    class="text-xs font-black uppercase tracking-widest text-cyan-700"
                >
                    {{ __('capell-theme-saas::generic.suggested_reading') }}
                </p>
                <div class="mt-5 grid gap-4 sm:grid-cols-3">
                    @foreach ($suggestions as $suggestion)
                        <a
                            class="rounded-lg border border-slate-200 bg-slate-50 p-4 no-underline hover:border-cyan-700 hover:bg-white"
                            href="{{ $suggestion['url'] ?? '#' }}"
                        >
                            <strong
                                class="block text-base font-black leading-snug text-slate-950"
                            >
                                {{ $suggestion['title'] ?? '' }}
                            </strong>
                            @if (($suggestion['summary'] ?? '') !== '')
                                <span
                                    class="mt-2 block text-sm leading-6 text-slate-600"
                                >
                                    {{ $suggestion['summary'] }}
                                </span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </article>
</main>
