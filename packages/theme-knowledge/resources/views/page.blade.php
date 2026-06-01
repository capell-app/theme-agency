<a
    href="#main-content"
    class="knowledge-skip-link"
>
    {{ __('capell-theme-knowledge::generic.skip_to_content') }}
</a>

<div
    style="{{ collect($brand->tokens())->map(fn (mixed $value, string $token): string => $token . ':' . $value)->implode(';') }}"
    class="knowledge-shell min-h-screen antialiased"
>
    {!! $content !!}
</div>
