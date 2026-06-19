<a
    href="#main-content"
    class="luxury-skip-link"
>
    {{ __('capell-theme-quiet-luxury-retail::generic.skip_to_content') }}
</a>

<main
    id="main-content"
    style="{{ collect($brand->tokens())->map(fn (mixed $value, string $token): string => $token . ':' . $value)->implode(';') }}"
    class="luxury-shell min-h-screen antialiased"
>
    {!! $content !!}
</main>
