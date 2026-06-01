<a
    href="#main-content"
    class="site-theme-skip-link"
>
    {{ __('capell-theme-corporate::generic.skip_to_content') }}
</a>

<div
    style="{{ collect($brand->tokens())->map(fn (mixed $value, string $token): string => $token . ':' . $value)->implode(';') }}"
    class="site-theme-shell min-h-screen bg-[#f7f8f6] text-slate-950 antialiased dark:bg-slate-950 dark:text-slate-50"
>
    {!! $content !!}
</div>
