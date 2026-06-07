<section class="theme-section theme-section-newsletter bg-white">
    <div class="mx-auto max-w-6xl px-6 py-16">
        <div
            class="grid gap-8 rounded-[2rem] bg-[var(--retail-ink)] p-8 text-white lg:grid-cols-[0.8fr_1.2fr] lg:items-center"
        >
            <div>
                <p
                    class="text-xs font-black tracking-[0.16em] text-[var(--retail-warm)] uppercase"
                >
                    {{ __('capell-theme-commerce::generic.newsletter_label') }}
                </p>
                <h2 class="mt-3 text-4xl font-black tracking-normal">
                    {{ $section->heading }}
                </h2>
                @if ($section->summary)
                    <p class="mt-4 text-base leading-7 text-white/78">
                        {{ $section->summary }}
                    </p>
                @endif
            </div>

            <form
                action="{{ $section->formAction ?? '#' }}"
                method="post"
                class="grid gap-3 rounded-2xl bg-white p-4 text-[var(--retail-ink)] sm:grid-cols-[1fr_auto]"
            >
                <label
                    class="sr-only"
                    for="commerce-newsletter-email"
                >
                    {{ __('capell-theme-commerce::generic.newsletter_email_label') }}
                </label>
                <input
                    id="commerce-newsletter-email"
                    type="email"
                    name="email"
                    placeholder="{{ $section->placeholder ?? __('capell-theme-commerce::generic.newsletter_placeholder') }}"
                    class="min-h-12 rounded-full border border-[var(--retail-line)] px-4 text-sm font-bold"
                />
                <button
                    type="submit"
                    class="rounded-full bg-[var(--retail-accent)] px-5 py-3 text-sm font-black text-white"
                >
                    {{ $section->buttonLabel ?? __('capell-theme-commerce::generic.newsletter_submit') }}
                </button>
            </form>
        </div>
    </div>
</section>
