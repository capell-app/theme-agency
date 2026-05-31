<a href="#main-content" class="local-services-skip-link">
    {{ __('capell-theme-local-services::generic.skip_to_content') }}
</a>

<div
    style="{{ collect($brand->tokens())->map(fn (mixed $value, string $token): string => $token . ':' . $value)->implode(';') }}"
    class="local-services-shell min-h-screen antialiased"
>
    {!! $content !!}
</div>
