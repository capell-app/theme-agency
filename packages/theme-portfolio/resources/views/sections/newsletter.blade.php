<section class="theme-section theme-section-newsletter bg-[#f8fafc]">
    @isset($heading)
        <div class="mx-auto max-w-5xl px-6 py-14">
            <div class="grid gap-6 md:grid-cols-[1fr_auto] md:items-end">
                <div>
                    <h2
                        class="text-4xl font-black tracking-tight text-[#0f172a]"
                    >
                        {{ $heading }}
                    </h2>
                    <p class="mt-4 max-w-2xl text-stone-600">
                        {{ $newsletterAvailable ?? false ? __('capell-theme-portfolio::generic.newsletter_connected') : __('capell-theme-portfolio::generic.newsletter_static') }}
                    </p>
                </div>
                <form
                    class="rounded-xl border border-slate-200 bg-white p-3"
                    action="#"
                    aria-label="Newsletter signup"
                >
                    <label
                        class="sr-only"
                        for="portfolio-newsletter"
                    >
                        {{ __('capell-theme-portfolio::generic.email_label') ?? 'Email address' }}
                    </label>
                    <div class="flex min-w-[18rem] gap-2">
                        <input
                            id="portfolio-newsletter"
                            type="email"
                            placeholder="you@company.com"
                            class="w-full rounded-full border border-slate-200 px-4 py-2 text-sm"
                        />
                        <button
                            type="submit"
                            class="rounded-full bg-[#0f172a] px-4 py-2 text-sm font-black text-white"
                        >
                            Subscribe
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endisset
</section>
