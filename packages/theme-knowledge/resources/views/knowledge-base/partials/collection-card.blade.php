@php
    $offsetClass = $depth > 0 ? 'ml-6 border-l-2 border-[var(--site-theme-primary-border)] pl-5' : '';
@endphp

<section class="{{ $offsetClass }} border border-slate-200 bg-white p-5 shadow-sm">
    <h2 class="text-2xl font-black text-[var(--site-theme-heading)]">
        {{ $collection['title'] }}
    </h2>
    @if ($collection['description'] !== null)
        <p class="mt-2 max-w-2xl text-slate-600">
            {{ $collection['description'] }}
        </p>
    @endif

    @if ($collection['articles'] !== [])
        <ul class="mt-5 grid gap-3">
            @foreach ($collection['articles'] as $article)
                <li>
                    <a
                        href="{{ $article['publicPath'] }}"
                        class="text-lg font-black text-[var(--site-theme-primary)] hover:text-[var(--site-theme-heading)]"
                    >
                        {{ $article['title'] }}
                    </a>
                    @if ($article['summary'] !== null)
                        <p class="mt-1 text-sm leading-6 text-slate-600">
                            {{ $article['summary'] }}
                        </p>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif

    @foreach ($collection['children'] as $childCollection)
        <div class="mt-5">
            @include('capell-theme-knowledge::knowledge-base.partials.collection-card', [
                'collection' => $childCollection,
                'depth' => $depth + 1,
            ])
        </div>
    @endforeach
</section>
