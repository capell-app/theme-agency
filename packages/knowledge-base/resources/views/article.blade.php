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
        <main>
            <nav
                aria-label="{{ __('capell-knowledge-base::generic.frontend.breadcrumbs') }}"
            >
                <a href="{{ route('capell-knowledge-base.index') }}">
                    {{ __('capell-knowledge-base::generic.frontend.index_title') }}
                </a>
                <span aria-hidden="true">/</span>
                <span>{{ $article['collectionTitle'] }}</span>
            </nav>

            <article>
                <h1>{{ $article['title'] }}</h1>
                @if ($article['summary'] !== null)
                    <p>{{ $article['summary'] }}</p>
                @endif

                <div>
                    {!! $article['body'] !!}
                </div>
            </article>

            <section>
                <h2>
                    {{ __('capell-knowledge-base::generic.frontend.feedback_title') }}
                </h2>

                @if (session('knowledge_base_feedback_status') !== null)
                    <p>{{ session('knowledge_base_feedback_status') }}</p>
                @endif

                <form
                    method="POST"
                    action="{{ route('capell-knowledge-base.article.feedback', ['collectionSlug' => $article['collectionSlug'], 'articleSlug' => $article['slug']]) }}"
                >
                    @csrf
                    <label>
                        {{ __('capell-knowledge-base::generic.frontend.feedback_comment') }}
                        <textarea
                            name="comment"
                            rows="3"
                        ></textarea>
                    </label>
                    <button
                        type="submit"
                        name="helpful"
                        value="1"
                    >
                        {{ __('capell-knowledge-base::generic.frontend.feedback_helpful') }}
                    </button>
                    <button
                        type="submit"
                        name="helpful"
                        value="0"
                    >
                        {{ __('capell-knowledge-base::generic.frontend.feedback_not_helpful') }}
                    </button>
                </form>
            </section>

            @if ($article['relatedArticles'] !== [])
                <aside>
                    <h2>
                        {{ __('capell-knowledge-base::generic.frontend.related_articles') }}
                    </h2>
                    <ul>
                        @foreach ($article['relatedArticles'] as $relatedArticle)
                            <li>
                                <a href="{{ $relatedArticle['publicPath'] }}">
                                    {{ $relatedArticle['title'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </aside>
            @endif
        </main>
    </body>
</html>
