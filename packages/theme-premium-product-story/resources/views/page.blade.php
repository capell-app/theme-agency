<a
    href="#main-content"
    class="product-skip-link"
>
    {{ __('capell-theme-premium-product-story::generic.skip_to_content') }}
</a>

<main
    id="main-content"
    style="{{ collect($brand->tokens())->map(fn (mixed $value, string $token): string => $token . ':' . $value)->implode(';') }}"
    class="product-shell min-h-screen antialiased"
>
    {!! $content !!}
</main>
