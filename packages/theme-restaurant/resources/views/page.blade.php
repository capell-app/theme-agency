<a
    href="#main-content"
    class="restaurant-skip-link"
>
    {{ __('capell-theme-restaurant::generic.skip_to_content') }}
</a>

<div
    style="{{ collect($brand->tokens())->map(fn (mixed $value, string $token): string => $token . ':' . $value)->implode(';') }}"
    class="restaurant-shell min-h-screen antialiased"
>
    <main id="main-content">
        {!! $content !!}
    </main>
</div>
