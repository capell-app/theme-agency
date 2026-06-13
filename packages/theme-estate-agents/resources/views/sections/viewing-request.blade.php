@php
    $heading = $section->heading ?? ($heading ?? __('capell-theme-estate-agents::generic.viewing_heading'));
    $summary = $section->summary ?? ($summary ?? __('capell-theme-estate-agents::generic.viewing_summary'));
    $formAction = $section->form_action ?? ($formAction ?? '#');
    $formBuilderAvailable ??= false;
@endphp

<section class="theme-section px-6 py-16">
    <div class="mx-auto grid max-w-6xl gap-8 lg:grid-cols-[0.9fr_1.1fr]">
        <div>
            <p class="estate-eyebrow">
                {{ $formBuilderAvailable ? __('capell-theme-estate-agents::generic.form_connected') : __('capell-theme-estate-agents::generic.viewing_label') }}
            </p>
            <h2 class="mt-4 text-4xl leading-tight font-black">
                {{ $heading }}
            </h2>
            @if ($summary)
                <p class="mt-5 text-lg leading-8 text-[var(--estate-muted)]">
                    {{ $summary }}
                </p>
            @endif
        </div>
        <form
            action="{{ $formAction }}"
            method="GET"
            class="estate-panel bg-white p-5"
        >
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="estate-form-label">
                    {{ __('capell-theme-estate-agents::generic.property_label') }}
                    <input
                        type="text"
                        name="property"
                        class="estate-form-input"
                    />
                </label>
                <label class="estate-form-label">
                    {{ __('capell-theme-estate-agents::generic.date_label') }}
                    <input
                        type="date"
                        name="date"
                        class="estate-form-input"
                    />
                </label>
            </div>
            <button
                type="submit"
                class="estate-button-primary mt-4 w-full justify-center"
            >
                {{ __('capell-theme-estate-agents::generic.request_viewing_label') }}
            </button>
        </form>
    </div>
</section>
