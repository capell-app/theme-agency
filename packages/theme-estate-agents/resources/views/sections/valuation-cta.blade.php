@php
    $publicThemeUrl = 'Capell\\ThemeStudio\\EstateAgents\\Support\\PublicThemeUrl';
    $heading = $section->heading ?? ($heading ?? __('capell-theme-estate-agents::generic.valuation_heading'));
    $summary = $section->summary ?? ($summary ?? __('capell-theme-estate-agents::generic.valuation_summary'));
    $formAction = $publicThemeUrl::formAction($form_action ?? ($formAction ?? ($section->form_action ?? null)));
    $formBuilderAvailable ??= false;
@endphp

<section
    id="valuation"
    class="theme-section px-6 py-16"
>
    <div
        class="estate-valuation mx-auto grid max-w-6xl gap-8 p-8 lg:grid-cols-[1fr_0.8fr]"
    >
        <div>
            <p class="estate-eyebrow text-white/70">
                {{ $formBuilderAvailable ? __('capell-theme-estate-agents::generic.form_connected') : __('capell-theme-estate-agents::generic.valuation_label') }}
            </p>
            <h2
                class="mt-4 max-w-3xl text-4xl leading-tight font-black text-white"
            >
                {{ $heading }}
            </h2>
            @if ($summary)
                <p class="mt-5 max-w-2xl text-lg leading-8 text-white/75">
                    {{ $summary }}
                </p>
            @endif
        </div>
        @if ($formAction !== null)
            <form
                action="{{ $formAction }}"
                method="GET"
                class="estate-panel bg-white p-5"
            >
                <label class="estate-form-label">
                    {{ __('capell-theme-estate-agents::generic.postcode_label') }}
                    <input
                        type="text"
                        name="postcode"
                        class="estate-form-input"
                    />
                </label>
                <button
                    type="submit"
                    class="estate-button-primary mt-4 w-full justify-center"
                >
                    {{ __('capell-theme-estate-agents::generic.start_valuation_label') }}
                </button>
            </form>
        @else
            <div class="estate-panel bg-white p-5">
                <p class="text-sm leading-6 text-[var(--estate-muted)]">
                    {{ __('capell-theme-estate-agents::generic.valuation_static_summary') }}
                </p>
            </div>
        @endif
    </div>
</section>
