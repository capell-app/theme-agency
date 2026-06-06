<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta
            name="viewport"
            content="width=device-width, initial-scale=1"
        />
        <title>
            {{ __('capell-knowledge-base::generic.frontend.index_title') }}
        </title>
    </head>
    <body>
        <main
            id="main-content"
            class="knowledge-shell min-h-screen bg-[#f8fafc] antialiased"
        >
            <section class="theme-section theme-section-content-listing">
                <div class="mx-auto max-w-6xl px-6">
                    <header class="max-w-3xl">
                        <p
                            class="text-xs font-black tracking-[0.18em] text-[#1d4ed8] uppercase"
                        >
                            {{ __('capell-theme-knowledge::generic.doc_sidebar_label') }}
                        </p>
                        <h1
                            class="mt-4 text-4xl leading-tight font-black text-[#172033] md:text-5xl"
                        >
                            {{ __('capell-knowledge-base::generic.frontend.index_title') }}
                        </h1>
                    </header>

                    <div class="mt-10 grid gap-5">
                        @forelse ($navigation as $collection)
                            @include('capell-theme-knowledge::knowledge-base.partials.collection-card', [
                                'collection' => $collection,
                                'depth' => 0,
                            ])
                        @empty
                            <p class="text-slate-600">
                                {{ __('capell-knowledge-base::generic.frontend.no_articles') }}
                            </p>
                        @endforelse
                    </div>
                </div>
            </section>
        </main>
    </body>
</html>
