<a
    href="#main-content"
    class="estate-skip-link"
>
    {{ __('capell-theme-estate-agents::generic.skip_to_content') }}
</a>

<main
    id="main-content"
    style="{{ collect($brand->tokens())->map(fn (mixed $value, string $token): string => $token . ':' . $value)->implode(';') }}"
    class="estate-shell min-h-screen antialiased"
>
    {!! $content !!}
</main>
