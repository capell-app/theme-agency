<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta
            name="viewport"
            content="width=device-width, initial-scale=1"
        />
        <title>{{ $article['title'] }}</title>
    </head>
    <body>
        <main
            id="main-content"
            class="knowledge-shell min-h-screen bg-[#f8fafc] antialiased"
        >
            <section class="theme-section theme-section-doc-article">
                <div
                    class="mx-auto grid max-w-7xl gap-8 px-6 lg:grid-cols-[17rem_minmax(0,1fr)]"
                >
                    <aside
                        class="knowledge-doc-sidebar hidden lg:block"
                        aria-label="{{ __('capell-theme-knowledge::generic.doc_sidebar_label') }}"
                    >
                        <div class="sticky top-8 border border-slate-200 bg-white p-4">
                            <p
                                class="text-xs font-black tracking-[0.18em] text-[#1d4ed8] uppercase"
                            >
                                {{ __('capell-theme-knowledge::generic.doc_sidebar_label') }}
                            </p>
                            <nav class="mt-4 space-y-1">
                                <a
                                    href="{{ route('capell-knowledge-base.index') }}"
                                    class="block border-l-2 border-transparent px-3 py-2 text-sm font-bold text-slate-600 hover:border-[#93c5fd] hover:bg-slate-50 hover:text-[#172033]"
                                >
                                    {{ __('capell-knowledge-base::generic.frontend.index_title') }}
                                </a>
                                @foreach ($article['relatedArticles'] as $relatedArticle)
                                    <a
                                        href="{{ $relatedArticle['publicPath'] }}"
                                        class="block border-l-2 border-transparent px-3 py-2 text-sm font-bold text-slate-600 hover:border-[#93c5fd] hover:bg-slate-50 hover:text-[#172033]"
                                    >
                                        {{ $relatedArticle['title'] }}
                                    </a>
                                @endforeach
                            </nav>
                        </div>
                    </aside>

                    <article
                        class="knowledge-doc-article border border-slate-200 bg-white p-6 shadow-sm md:p-8 lg:p-10"
                    >
                        <nav
                            class="mb-8"
                            aria-label="{{ __('capell-knowledge-base::generic.frontend.breadcrumbs') }}"
                        >
                            <ol
                                class="flex flex-wrap items-center gap-2 text-sm font-bold text-slate-500"
                            >
                                <li>
                                    <a
                                        href="{{ route('capell-knowledge-base.index') }}"
                                        class="hover:text-[#1d4ed8]"
                                    >
                                        {{ __('capell-knowledge-base::generic.frontend.index_title') }}
                                    </a>
                                </li>
                                <li aria-hidden="true">/</li>
                                <li class="text-slate-700">
                                    {{ $article['collectionTitle'] }}
                                </li>
                            </ol>
                        </nav>

                        <header class="max-w-3xl">
                            <p
                                class="text-xs font-black tracking-[0.18em] text-[#1d4ed8] uppercase"
                            >
                                {{ $article['collectionTitle'] }}
                            </p>
                            <h1
                                class="mt-4 text-4xl leading-tight font-black text-[#172033] md:text-5xl"
                            >
                                {{ $article['title'] }}
                            </h1>
                            @if ($article['summary'] !== null)
                                <p class="mt-5 text-lg leading-8 text-slate-600">
                                    {{ $article['summary'] }}
                                </p>
                            @endif
                        </header>

                        <div class="knowledge-doc-prose mt-10 max-w-3xl text-slate-700">
                            {!! $article['body'] !!}
                        </div>

                        <section class="mt-10 border-t border-slate-200 pt-8">
                            <h2 class="text-2xl font-black text-[#172033]">
                                {{ __('capell-knowledge-base::generic.frontend.feedback_title') }}
                            </h2>

                            @if (session('knowledge_base_feedback_status') !== null)
                                <p class="mt-3 font-bold text-[#1d4ed8]">
                                    {{ session('knowledge_base_feedback_status') }}
                                </p>
                            @endif

                            @if ($article['feedbackCount'] > 0 && $article['helpfulFeedbackPercentage'] !== null)
                                <p class="mt-3 text-sm font-bold text-slate-600">
                                    {{ __('capell-knowledge-base::generic.frontend.feedback_summary', [
                                        'percentage' => $article['helpfulFeedbackPercentage'],
                                        'count' => $article['feedbackCount'],
                                    ]) }}
                                </p>
                            @endif

                            <form
                                method="POST"
                                action="{{ route('capell-knowledge-base.article.feedback', ['collectionSlug' => $article['collectionSlug'], 'articleSlug' => $article['slug']]) }}"
                                class="mt-5 grid gap-4"
                            >
                                @csrf
                                <label class="grid gap-2 text-sm font-bold text-slate-700">
                                    {{ __('capell-knowledge-base::generic.frontend.feedback_comment') }}
                                    <textarea
                                        name="comment"
                                        rows="3"
                                        class="border border-slate-300 bg-white p-3"
                                    ></textarea>
                                </label>
                                <div class="flex flex-wrap gap-3">
                                    <button
                                        type="submit"
                                        name="helpful"
                                        value="1"
                                        class="bg-[#1d4ed8] px-4 py-2 text-sm font-black text-white"
                                    >
                                        {{ __('capell-knowledge-base::generic.frontend.feedback_helpful') }}
                                    </button>
                                    <button
                                        type="submit"
                                        name="helpful"
                                        value="0"
                                        class="border border-slate-300 px-4 py-2 text-sm font-black text-slate-700"
                                    >
                                        {{ __('capell-knowledge-base::generic.frontend.feedback_not_helpful') }}
                                    </button>
                                </div>
                            </form>
                        </section>
                    </article>
                </div>
            </section>
        </main>
    </body>
</html>
