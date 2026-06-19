<a
    href="#main-content"
    class="fashion-skip-link"
>
    {{ __('capell-theme-minimal-fashion::generic.skip_to_content') }}
</a>

<main
    id="main-content"
    style="{{ collect($brand->tokens())->map(fn (mixed $value, string $token): string => $token . ':' . $value)->implode(';') }}"
    class="fashion-shell min-h-screen antialiased"
>
    {!! $content !!}
</main>
