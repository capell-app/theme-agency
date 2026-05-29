<a href="#main-content" class="site-theme-skip-link">
    {{ __('capell-theme-agency::generic.skip_to_content') }}
</a>

<div
    style="{{ collect($brand->tokens())->map(fn ($value, $token) => $token . ':' . $value)->implode(';') }}"
    class="site-theme-shell min-h-screen bg-zinc-950 text-zinc-950 antialiased"
>
    {!! $content !!}
</div>
