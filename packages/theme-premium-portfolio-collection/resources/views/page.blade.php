<a
    href="#main-content"
    class="ppc-skip-link"
>
    {{ __('capell-theme-premium-portfolio-collection::generic.skip_to_content') }}
</a>

<div
    style="{{ collect($brand->tokens())->map(fn (mixed $value, string $token): string => $token . ':' . $value)->implode(';') }}"
    class="ppc-shell min-h-screen antialiased"
>
    @if (isset($chromeHeader) || isset($chromeFooter))
        {!! $chromeHeader ?? '' !!}
        <main id="main-content">{!! $mainContent ?? $content !!}</main>
        {!! $chromeFooter ?? '' !!}
    @else
        <main id="main-content">{!! $content !!}</main>
    @endif
</div>
