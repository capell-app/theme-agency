<a
    href="#main-content"
    class="dlm-skip-link"
>
    {{ __('capell-theme-design-led-magazine::generic.skip_to_content') }}
</a>

<div
    style="{{ collect($brand->tokens())->map(fn (mixed $value, string $token): string => $token . ':' . $value)->implode(';') }}"
    class="dlm-shell min-h-screen antialiased"
>
    @if (isset($chromeHeader) || isset($chromeFooter))
        {!! $chromeHeader ?? '' !!}
        <main id="main-content">{!! $mainContent ?? $content !!}</main>
        {!! $chromeFooter ?? '' !!}
    @else
        <main id="main-content">{!! $content !!}</main>
    @endif
</div>
