<section class="theme-section theme-section-featured-content">
    @isset($heading)
        <div class="mx-auto max-w-5xl px-6 py-14">
            <h2
                class="text-4xl font-black tracking-tight text-[var(--site-theme-muted)]"
            >
                {{ $heading }}
            </h2>
        </div>
    @endisset

    <div class="mx-auto grid max-w-5xl gap-4 px-6 pb-14 md:grid-cols-3">
        <article class="rounded-xl border border-slate-200 bg-white p-5">
            <p
                class="text-xs font-black tracking-widest text-[var(--site-theme-muted)] uppercase"
            >
                {{ __('capell-theme-knowledge::generic.featured_content_first_label') }}
            </p>
            <h3 class="mt-2 text-lg font-black">
                {{ __('capell-theme-knowledge::generic.featured_content_first_title') }}
            </h3>
            <p class="mt-2 text-sm text-stone-600">
                {{ __('capell-theme-knowledge::generic.featured_content_first_summary') }}
            </p>
        </article>
        <article class="rounded-xl border border-slate-200 bg-white p-5">
            <p
                class="text-xs font-black tracking-widest text-[var(--site-theme-muted)] uppercase"
            >
                {{ __('capell-theme-knowledge::generic.featured_content_second_label') }}
            </p>
            <h3 class="mt-2 text-lg font-black">
                {{ __('capell-theme-knowledge::generic.featured_content_second_title') }}
            </h3>
            <p class="mt-2 text-sm text-stone-600">
                {{ __('capell-theme-knowledge::generic.featured_content_second_summary') }}
            </p>
        </article>
        <article class="rounded-xl border border-slate-200 bg-white p-5">
            <p
                class="text-xs font-black tracking-widest text-[var(--site-theme-muted)] uppercase"
            >
                {{ __('capell-theme-knowledge::generic.featured_content_third_label') }}
            </p>
            <h3 class="mt-2 text-lg font-black">
                {{ __('capell-theme-knowledge::generic.featured_content_third_title') }}
            </h3>
            <p class="mt-2 text-sm text-stone-600">
                {{ __('capell-theme-knowledge::generic.featured_content_third_summary') }}
            </p>
        </article>
    </div>
</section>
