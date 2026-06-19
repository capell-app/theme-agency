<a
    href="#main-content"
    class="editorial-skip-link"
>
    {{ __('capell-theme-one-page-showcase::generic.skip_to_content') }}
</a>

<main
    id="main-content"
    style="{{ collect($brand->tokens())->map(fn (mixed $value, string $token): string => $token . ':' . $value)->implode(';') }}"
    class="editorial-shell min-h-screen antialiased"
>
    {!! $content !!}
</main>
