@php
    $blogAvailable ??= false;
    $articles ??= [];
    $heading ??= __('capell-theme-saas::generic.insights_heading');
    $summary ??= null;
@endphp

<main class="velocity-insights-index bg-white px-6 py-20 text-slate-950">
    <div class="mx-auto max-w-6xl">
        <header class="max-w-3xl">
            <p
                class="text-xs font-black uppercase tracking-widest text-cyan-700"
            >
                {{ $blogAvailable ? __('capell-theme-saas::generic.insights_label') : __('capell-theme-saas::generic.resources_label') }}
            </p>
            <h1 class="mt-4 text-5xl font-black tracking-normal md:text-7xl">
                {{ $heading }}
            </h1>
            @if ($summary)
                <p class="mt-5 max-w-2xl text-lg leading-8 text-slate-600">
                    {{ $summary }}
                </p>
            @endif
        </header>

        <div class="mt-12 grid gap-5 md:grid-cols-3">
            @foreach ($articles as $article)
                @if ($blogAvailable)
                    <a
                        href="{{ $article['url'] ?? '#' }}"
                        class="rounded-2xl border border-slate-200 bg-slate-50 p-6 transition hover:border-blue-300 hover:bg-white"
                    >
                        <p
                            class="text-xs font-black uppercase tracking-widest text-cyan-700"
                        >
                            {{ $article['type'] ?? __('capell-theme-saas::generic.insight') }}
                        </p>
                        <h2 class="mt-4 text-2xl font-black tracking-normal">
                            {{ $article['title'] }}
                        </h2>
                        <p class="mt-3 text-sm leading-6 text-slate-600">
                            {{ $article['summary'] ?? '' }}
                        </p>
                    </a>
                @else
                    <article
                        class="rounded-2xl border border-slate-200 bg-slate-50 p-6"
                    >
                        <p
                            class="text-xs font-black uppercase tracking-widest text-cyan-700"
                        >
                            {{ __('capell-theme-saas::generic.resource') }}
                        </p>
                        <h2 class="mt-4 text-2xl font-black tracking-normal">
                            {{ $article['title'] }}
                        </h2>
                        <p class="mt-3 text-sm leading-6 text-slate-600">
                            {{ $article['summary'] ?? '' }}
                        </p>
                    </article>
                @endif
            @endforeach
        </div>
    </div>
</main>
