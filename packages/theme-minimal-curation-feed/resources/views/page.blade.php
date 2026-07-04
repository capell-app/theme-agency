<a
    href="#main-content"
    class="mcf-skip-link"
>
    {{ __('capell-theme-minimal-curation-feed::generic.skip_to_content') }}
</a>

<div
    style="{{ collect($brand->tokens())->map(fn (mixed $value, string $token): string => $token . ':' . $value)->implode(';') }}"
    class="mcf-shell min-h-screen antialiased"
>
    @if (isset($chromeHeader) || isset($chromeFooter))
        {!! $chromeHeader ?? '' !!}
        <main id="main-content">{!! $mainContent ?? $content !!}</main>
        {!! $chromeFooter ?? '' !!}
    @else
        <main id="main-content">{!! $content !!}</main>
    @endif
</div>
