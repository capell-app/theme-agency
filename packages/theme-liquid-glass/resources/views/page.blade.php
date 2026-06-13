<a
    href="#main-content"
    class="site-theme-skip-link"
>
    {{ __('capell-theme-liquid-glass::generic.skip_to_content') }}
</a>

<div
    style="{{ collect($brand->tokens())->map(fn (mixed $value, string $token): string => $token . ':' . $value)->implode(';') }}"
    class="site-theme-shell liquid-glass-shell min-h-screen antialiased"
>
    {!! $content !!}
</div>
