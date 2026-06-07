@php
    $heading ??= $section->heading ?? null;
    $summary ??= $section->summary ?? (($paymentsAvailable ?? false) && ($formBuilderAvailable ?? false) ? __('capell-theme-nonprofit::generic.volunteer_payments_connected') : (($formBuilderAvailable ?? false) ? __('capell-theme-nonprofit::generic.volunteer_connected') : __('capell-theme-nonprofit::generic.volunteer_static')));
@endphp

<section
    class="theme-section theme-section-volunteer-donate nonprofit-bg-surface-warm"
>
    <div class="mx-auto max-w-5xl px-6 py-14">
        <div
            class="nonprofit-border-accent grid gap-6 border bg-white p-8 md:grid-cols-[0.72fr_1fr] md:items-center"
        >
            <div>
                <p
                    class="nonprofit-text-accent-strong text-xs font-black tracking-[0.18em] uppercase"
                >
                    {{ __('capell-theme-nonprofit::generic.supporter_cta_label') }}
                </p>
                @isset($heading)
                    <h2
                        class="nonprofit-text-ink mt-4 text-4xl font-black tracking-tight"
                    >
                        {{ $heading }}
                    </h2>
                @endisset
            </div>

            <div>
                <p class="nonprofit-text-muted text-base leading-7">
                    {{ $summary }}
                </p>
                <div class="mt-6 grid gap-3 text-sm font-bold sm:grid-cols-3">
                    <span
                        class="nonprofit-bg-primary-soft nonprofit-text-primary px-4 py-3"
                    >
                        {{ __('capell-theme-nonprofit::generic.supporter_step_one') }}
                    </span>
                    <span
                        class="nonprofit-bg-accent-soft nonprofit-text-accent-strong px-4 py-3"
                    >
                        {{ __('capell-theme-nonprofit::generic.supporter_step_two') }}
                    </span>
                    <span class="nonprofit-text-primary bg-white px-4 py-3">
                        {{ __('capell-theme-nonprofit::generic.supporter_step_three') }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</section>
