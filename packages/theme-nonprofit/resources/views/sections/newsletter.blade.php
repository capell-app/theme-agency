@php
    $heading ??= $section->heading ?? __('capell-theme-nonprofit::generic.newsletter_label');
    $summary ??= $section->summary ?? null;
    $formAction = $section->formAction ?? $section->action ?? ($formAction ?? $newsletterFormAction ?? null);
    $formMethod = strtoupper((string) ($section->formMethod ?? $method ?? $newsletterFormMethod ?? 'POST'));
    $formMethod = in_array($formMethod, ['GET', 'POST'], true) ? $formMethod : 'POST';
    $formAction = is_string($formAction) ? trim($formAction) : '';
@endphp

<section class="theme-section theme-section-newsletter bg-white">
    <div class="mx-auto max-w-5xl px-6 py-14">
        <div
            class="nonprofit-bg-surface-warm grid gap-6 border border-slate-200 p-6 md:grid-cols-[1fr_auto] md:items-end md:p-8"
        >
            <div>
                <p
                    class="nonprofit-text-accent-strong text-xs font-black tracking-[0.18em] uppercase"
                >
                    {{ __('capell-theme-nonprofit::generic.newsletter_label') }}
                </p>
                <h2
                    class="nonprofit-text-ink mt-4 max-w-2xl text-4xl font-black tracking-tight"
                >
                    {{ $heading }}
                </h2>
                <p class="mt-4 max-w-2xl text-base leading-7 text-slate-600">
                    {{ $summary ?? (($newsletterAvailable ?? false) ? __('capell-theme-nonprofit::generic.newsletter_connected') : __('capell-theme-nonprofit::generic.newsletter_static')) }}
                </p>
            </div>

            @if ($formAction !== '')
                <form
                    class="border border-slate-200 bg-white p-3"
                    action="{{ $formAction }}"
                    method="{{ $formMethod }}"
                    aria-label="{{ __('capell-theme-nonprofit::generic.newsletter_form_label') }}"
                >
                    <label
                        class="sr-only"
                        for="nonprofit-newsletter"
                    >
                        {{ __('capell-theme-nonprofit::generic.newsletter_email_label') }}
                    </label>
                    <div class="flex min-w-[18rem] gap-2">
                        <input
                            type="hidden"
                            name="source"
                            value="theme_nonprofit_newsletter"
                        />
                        <input
                            id="nonprofit-newsletter"
                            name="email"
                            type="email"
                            autocomplete="email"
                            required
                            placeholder="{{ __('capell-theme-nonprofit::generic.newsletter_email_placeholder') }}"
                            class="w-full border border-slate-200 px-4 py-3 text-sm"
                        />
                        <button
                            type="submit"
                            class="nonprofit-bg-primary inline-flex items-center justify-center px-5 py-3 text-sm font-black text-white"
                        >
                            {{ __('capell-theme-nonprofit::generic.newsletter_submit_label') }}
                        </button>
                    </div>
                </form>
            @else
                <div
                    class="border border-dashed border-slate-300 bg-white p-5 text-sm font-bold text-slate-600"
                    aria-label="{{ __('capell-theme-nonprofit::generic.newsletter_form_label') }}"
                >
                    {{ __('capell-theme-nonprofit::generic.newsletter_form_unavailable') }}
                </div>
            @endif
        </div>
    </div>
</section>
