<a
    href="#main-content"
    class="outdoor-skip-link"
>
    {{ __('capell-theme-outdoor-mission::generic.skip_to_content') }}
</a>

<main
    id="main-content"
    style="{{ collect($brand->tokens())->map(fn (mixed $value, string $token): string => $token . ':' . $value)->implode(';') }}"
    class="outdoor-shell min-h-screen antialiased"
>
    {!! $content !!}
</main>
