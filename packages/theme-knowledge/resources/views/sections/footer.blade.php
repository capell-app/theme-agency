<section class="theme-section theme-section-footer">
    @isset($heading)
        <div class="mx-auto max-w-5xl px-6 py-6">
            <h2
                class="text-xs font-black tracking-[0.16em] text-[var(--site-theme-muted)] uppercase"
            >
                {{ $heading }}
            </h2>
        </div>
    @endisset
</section>
