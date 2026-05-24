<a href="#main-content" class="saas-skip-link">
    {{ __('capell-theme-saas::generic.skip_to_content') }}
</a>

<div
    style="{{ collect($brand->tokens())->map(fn (mixed $value, string $token): string => $token . ':' . $value)->implode(';') }}"
    class="saas-shell min-h-screen antialiased"
>
    {!! $content !!}
</div>
