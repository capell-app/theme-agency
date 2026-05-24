<a href="#main-content" class="retail-skip-link">
    {{ __('capell-theme-commerce::generic.skip_to_content') }}
</a>

<div
    style="{{ collect($brand->tokens())->map(fn (mixed $value, string $token): string => $token . ':' . $value)->implode(';') }}"
    class="retail-shell min-h-screen antialiased"
>
    {!! $content !!}
</div>
