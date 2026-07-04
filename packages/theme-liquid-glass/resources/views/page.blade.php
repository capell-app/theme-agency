<a
    href="#main-content"
    class="site-theme-skip-link"
>
    {{ __('capell-theme-liquid-glass::generic.skip_to_content') }}
</a>

<div
    style="{{ collect($brand->tokens())->map(fn (mixed $value, string $token): string => $token . ':' . $value)->implode(';') }}"
    class="site-theme-shell liquid-glass-shell min-h-screen antialiased"
>
    @if (isset($chromeHeader) || isset($chromeFooter))
        {!! $chromeHeader ?? '' !!}
        <main
            id="main-content"
            tabindex="-1"
        >
            {!! $mainContent ?? $content !!}
        </main>
        {!! $chromeFooter ?? '' !!}
    @else
        <main
            id="main-content"
            tabindex="-1"
        >
            {!! $content !!}
        </main>
    @endif
</div>
