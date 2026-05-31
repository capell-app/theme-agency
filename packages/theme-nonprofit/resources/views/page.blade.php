<a href="#main-content" class="nonprofit-skip-link">
    {{ __('capell-theme-nonprofit::generic.skip_to_content') }}
</a>

<div
    style="{{ collect($brand->tokens())->map(fn (mixed $value, string $token): string => $token . ':' . $value)->implode(';') }}"
    class="nonprofit-shell min-h-screen antialiased"
>
    {!! $content !!}
</div>
