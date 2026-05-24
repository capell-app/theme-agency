<a href="#main-content" class="healthcare-skip-link">
    {{ __('capell-theme-healthcare::generic.skip_to_content') }}
</a>

<div
    style="{{ collect($brand->tokens())->map(fn (mixed $value, string $token): string => $token . ':' . $value)->implode(';') }}"
    class="healthcare-shell min-h-screen antialiased"
>
    {!! $content !!}
</div>
