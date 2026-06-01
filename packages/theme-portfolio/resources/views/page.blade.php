<a
    href="#main-content"
    class="portfolio-skip-link"
>
    {{ __('capell-theme-portfolio::generic.skip_to_content') }}
</a>

<div
    style="{{ collect($brand->tokens())->map(fn (mixed $value, string $token): string => $token . ':' . $value)->implode(';') }}"
    class="portfolio-shell min-h-screen antialiased"
>
    {!! $content !!}
</div>
