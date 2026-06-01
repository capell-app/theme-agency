<a
    href="#main-content"
    class="education-skip-link"
>
    {{ __('capell-theme-education::generic.skip_to_content') }}
</a>

<div
    style="{{ collect($brand->tokens())->map(fn (mixed $value, string $token): string => $token . ':' . $value)->implode(';') }}"
    class="education-shell min-h-screen antialiased"
>
    {!! $content !!}
</div>
