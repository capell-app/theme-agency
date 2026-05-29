<a
    href="#main-content"
    class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-50 focus:rounded-md focus:bg-white focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-slate-950 focus:shadow-lg"
>
    {{ __('capell-foundation-theme::generic.skip_to_content') }}
</a>

<div
    style="{{ collect($brand->tokens())->map(fn ($value, $token) => $token . ':' . $value)->implode(';') }}"
    class="site-theme-shell min-h-screen bg-[var(--theme-surface)] font-[var(--theme-body-font)] text-[var(--theme-foreground)] antialiased"
>
    {!! $content !!}
</div>
