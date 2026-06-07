@php
    use Illuminate\Support\Facades\Route;

    $sectionHeading = $heading ?? ($section->heading ?? null);
    $formAction = $section->formAction ?? $section->action ?? ($formAction ?? $newsletterFormAction ?? null);
    $formRoute = $newsletterFormRoute ?? null;
    Route::getRoutes()->refreshNameLookups();
    if (
        (! is_string($formAction) || trim($formAction) === '') &&
        is_string($formRoute) &&
        Route::has($formRoute)
    ) {
        $formAction = route($formRoute);
    }
    $formMethod = strtoupper((string) ($section->formMethod ?? $method ?? $newsletterFormMethod ?? 'POST'));
    $formMethod = in_array($formMethod, ['GET', 'POST'], true) ? $formMethod : 'POST';
    $formAction = is_string($formAction) ? trim($formAction) : '';
@endphp

<section class="theme-section theme-section-newsletter portfolio-bg-card-soft">
    @if ($sectionHeading)
        <div class="mx-auto max-w-5xl px-6 py-14">
            <div class="grid gap-6 md:grid-cols-[1fr_auto] md:items-end">
                <div>
                    <h2
                        class="portfolio-text-ink text-4xl font-black tracking-tight"
                    >
                        {{ $sectionHeading }}
                    </h2>
                    <p class="mt-4 max-w-2xl text-stone-600">
                        {{ $newsletterAvailable ?? false ? __('capell-theme-portfolio::generic.newsletter_connected') : __('capell-theme-portfolio::generic.newsletter_static') }}
                    </p>
                </div>

                @if ($formAction !== '')
                    <form
                        class="rounded-xl border border-slate-200 bg-white p-3"
                        action="{{ $formAction }}"
                        method="{{ $formMethod }}"
                        aria-label="{{ __('capell-theme-portfolio::generic.newsletter_form_label') }}"
                    >
                        <label
                            class="sr-only"
                            for="portfolio-newsletter"
                        >
                            {{ __('capell-theme-portfolio::generic.email_label') }}
                        </label>
                        <div class="flex min-w-[18rem] gap-2">
                            <input
                                type="hidden"
                                name="source"
                                value="theme_portfolio_newsletter"
                            />
                            <input
                                id="portfolio-newsletter"
                                name="email"
                                type="email"
                                autocomplete="email"
                                required
                                placeholder="{{ __('capell-theme-portfolio::generic.email_placeholder') }}"
                                class="w-full rounded-full border border-slate-200 px-4 py-2 text-sm"
                            />
                            <button
                                type="submit"
                                class="portfolio-bg-ink rounded-full px-4 py-2 text-sm font-black text-white"
                            >
                                {{ __('capell-theme-portfolio::generic.subscribe_label') }}
                            </button>
                        </div>
                    </form>
                @else
                    <div
                        class="rounded-xl border border-dashed border-slate-300 bg-white p-5 text-sm font-bold text-slate-600"
                        aria-label="{{ __('capell-theme-portfolio::generic.newsletter_form_label') }}"
                    >
                        {{ __('capell-theme-portfolio::generic.newsletter_form_unavailable') }}
                    </div>
                @endif
            </div>
        </div>
    @endif
</section>
