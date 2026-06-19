<a
    href="#main-content"
    class="dog-walkers-skip-link"
>
    {{ __('capell-theme-dog-walkers::generic.skip_to_content') }}
</a>

<div
    style="{{ collect($brand->tokens())->map(fn (mixed $value, string $token): string => $token . ':' . $value)->implode(';') }}"
    class="dog-walkers-shell min-h-screen antialiased"
>
    {!! $content !!}
</div>
