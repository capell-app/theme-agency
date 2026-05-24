@php
    $blogAvailable ??= false;
    $articles ??= [];
    $heading ??= __('capell-theme-saas::generic.insights_heading');
    $summary ??= null;
    $total ??= 150;
    $from ??= count($articles) > 0 ? 1 : 0;
    $to ??= count($articles) > 0 ? min(10, count($articles)) : 0;
    $searchEnabled ??= false;
    $tags ??= ['Growth', 'Activation', 'Product', 'Retention', 'Launches'];
    $latestArticles ??= array_slice($articles, 0, 4);
@endphp

<main class="velocity-insights-index bg-white px-6 py-20 text-slate-950">
    <div class="mx-auto max-w-6xl">
        <header
            class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_22rem] lg:items-end"
        >
            <div class="max-w-3xl">
                <p
                    class="text-xs font-black uppercase tracking-widest text-cyan-700"
                >
                    {{ $blogAvailable ? __('capell-theme-saas::generic.insights_label') : __('capell-theme-saas::generic.resources_label') }}
                </p>
                <h1
                    class="mt-4 text-5xl font-black tracking-normal md:text-7xl"
                >
                    {{ $heading }}
                </h1>
                @if ($summary)
                    <p class="mt-5 max-w-2xl text-lg leading-8 text-slate-600">
                        {{ $summary }}
                    </p>
                @endif
            </div>

            <div class="rounded-lg border border-slate-200 bg-slate-50 p-5">
                <p
                    class="text-xs font-black uppercase tracking-widest text-cyan-700"
                >
                    {{ __('capell-theme-saas::generic.results_summary') }}
                </p>
                <p class="mt-2 text-2xl font-black tracking-normal">
                    {{ __('capell-theme-saas::generic.showing_results', ['from' => $from, 'to' => $to, 'total' => $total]) }}
                </p>

                @if ($searchEnabled)
                    <form
                        action="/search"
                        method="GET"
                        class="mt-5 grid gap-2 sm:grid-cols-[minmax(0,1fr)_auto]"
                        role="search"
                    >
                        <label class="sr-only" for="velocity-blog-search">
                            {{ __('capell-theme-saas::generic.search_articles') }}
                        </label>
                        <input
                            id="velocity-blog-search"
                            class="min-h-11 rounded-md border border-slate-200 bg-white px-3 text-sm font-bold text-slate-950"
                            name="q"
                            type="search"
                            placeholder="{{ __('capell-theme-saas::generic.search_articles') }}"
                        />
                        <button
                            class="min-h-11 rounded-md bg-slate-950 px-4 text-sm font-black text-white"
                            type="submit"
                        >
                            {{ __('capell-theme-saas::generic.search') }}
                        </button>
                    </form>
                @endif
            </div>
        </header>

        <div
            class="mt-12 grid gap-8 lg:grid-cols-[minmax(0,1fr)_18rem] lg:items-start"
        >
            <div class="grid gap-5">
                @foreach ($articles as $article)
                    @if ($blogAvailable)
                        <a
                            href="{{ $article['url'] ?? '#' }}"
                            class="rounded-lg border border-slate-200 bg-slate-50 p-6 transition hover:border-blue-300 hover:bg-white"
                        >
                            <p
                                class="text-xs font-black uppercase tracking-widest text-cyan-700"
                            >
                                {{ $article['type'] ?? __('capell-theme-saas::generic.insight') }}
                            </p>
                            <h2
                                class="mt-4 text-2xl font-black tracking-normal"
                            >
                                {{ $article['title'] }}
                            </h2>
                            <p class="mt-3 text-sm leading-6 text-slate-600">
                                {{ $article['summary'] ?? '' }}
                            </p>
                        </a>
                    @else
                        <article
                            class="rounded-lg border border-slate-200 bg-slate-50 p-6"
                        >
                            <p
                                class="text-xs font-black uppercase tracking-widest text-cyan-700"
                            >
                                {{ __('capell-theme-saas::generic.resource') }}
                            </p>
                            <h2
                                class="mt-4 text-2xl font-black tracking-normal"
                            >
                                {{ $article['title'] }}
                            </h2>
                            <p class="mt-3 text-sm leading-6 text-slate-600">
                                {{ $article['summary'] ?? '' }}
                            </p>
                        </article>
                    @endif
                @endforeach

                <nav
                    class="flex flex-wrap gap-2 pt-3"
                    aria-label="{{ __('capell-theme-saas::generic.pagination') }}"
                >
                    @foreach ([1, 2, 3, 4, 5] as $page)
                        <a
                            @class(['grid min-h-10 min-w-10 place-items-center rounded-md border px-3 text-sm font-black no-underline', 'border-cyan-700 bg-cyan-700 text-white' => $page === 1, 'border-slate-200 bg-white text-slate-950' => $page !== 1])
                            href="{{ $page === 1 ? '/blog' : '/blog?articles=' . $page }}"
                            @if ($page === 1) aria-current="page" @endif
                        >
                            {{ $page }}
                        </a>
                    @endforeach
                </nav>
            </div>

            <aside class="grid gap-5 lg:sticky lg:top-24">
                <section
                    class="rounded-lg border border-slate-200 bg-white p-5"
                >
                    <h2 class="text-lg font-black tracking-normal">
                        {{ __('capell-theme-saas::generic.tags') }}
                    </h2>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach ($tags as $tag)
                            <a
                                class="rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-bold text-slate-700 no-underline hover:border-cyan-700 hover:text-cyan-700"
                                href="/blog/tags"
                            >
                                {{ $tag }}
                            </a>
                        @endforeach
                    </div>
                </section>

                <section
                    class="rounded-lg border border-slate-200 bg-white p-5"
                >
                    <h2 class="text-lg font-black tracking-normal">
                        {{ __('capell-theme-saas::generic.latest_articles') }}
                    </h2>
                    <div class="mt-4 grid gap-4">
                        @foreach ($latestArticles as $article)
                            <a
                                class="block border-t border-slate-200 pt-4 no-underline first:border-t-0 first:pt-0"
                                href="{{ $blogAvailable ? ($article['url'] ?? '/blog') : '/blog' }}"
                            >
                                <span
                                    class="text-xs font-black uppercase tracking-widest text-cyan-700"
                                >
                                    {{ $article['type'] ?? __('capell-theme-saas::generic.insight') }}
                                </span>
                                <strong
                                    class="mt-1 block text-sm font-black leading-snug text-slate-950"
                                >
                                    {{ $article['title'] }}
                                </strong>
                            </a>
                        @endforeach
                    </div>
                </section>
            </aside>
        </div>
    </div>
</main>
