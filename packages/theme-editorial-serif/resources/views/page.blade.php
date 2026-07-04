<a
    href="#main-content"
    class="site-theme-skip-link eser-skip-link"
>
    {{ __('capell-theme-editorial-serif::generic.skip_to_content') }}
</a>

<div
    class="eser-progress"
    aria-hidden="true"
></div>

<div
    style="{{ collect($brand->tokens())->map(fn (mixed $value, string $token): string => $token . ':' . $value)->implode(';') }}"
    class="site-theme-shell eser-shell min-h-screen antialiased"
>
    @if (isset($chromeHeader) || isset($chromeFooter))
        {!! $chromeHeader ?? '' !!}
        <main id="main-content">{!! $mainContent ?? $content !!}</main>
        {!! $chromeFooter ?? '' !!}
    @else
        <main id="main-content">{!! $content !!}</main>
    @endif
</div>
