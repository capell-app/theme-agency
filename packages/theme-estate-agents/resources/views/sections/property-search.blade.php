@php
    $publicThemeUrl = 'Capell\\ThemeStudio\\EstateAgents\\Support\\PublicThemeUrl';
    $heading = $section->heading ?? ($heading ?? __('capell-theme-estate-agents::generic.search_heading'));
    $summary = $section->summary ?? ($summary ?? __('capell-theme-estate-agents::generic.search_summary'));
    $formAction = $publicThemeUrl::formAction($form_action ?? ($formAction ?? ($section->form_action ?? null)));
    $searchAvailable ??= false;
@endphp

<section
    id="property-search"
    class="theme-section estate-search-band px-6 py-12"
>
    <div class="mx-auto max-w-6xl">
        <div class="grid gap-6 lg:grid-cols-[0.72fr_1.28fr] lg:items-end">
            <div>
                <p class="estate-eyebrow text-white/70">
                    {{ $searchAvailable ? __('capell-theme-estate-agents::generic.search_connected') : __('capell-theme-estate-agents::generic.search_label') }}
                </p>
                <h2 class="mt-4 text-4xl leading-tight font-black text-white">
                    {{ $heading }}
                </h2>
                @if ($summary)
                    <p class="mt-4 text-sm leading-6 text-white/75">
                        {{ $summary }}
                    </p>
                @endif
            </div>
            @if ($formAction !== null)
                <form
                    action="{{ $formAction }}"
                    method="GET"
                    class="estate-search-form bg-white p-4"
                >
                    <div
                        class="grid gap-3 md:grid-cols-[1fr_0.75fr_0.75fr_auto]"
                    >
                        <label class="estate-form-label">
                            {{ __('capell-theme-estate-agents::generic.location_label') }}
                            <input
                                type="search"
                                name="location"
                                class="estate-form-input"
                            />
                        </label>
                        <label class="estate-form-label">
                            {{ __('capell-theme-estate-agents::generic.budget_label') }}
                            <select
                                name="budget"
                                class="estate-form-input"
                            >
                                <option>450k</option>
                                <option>750k</option>
                                <option>1.2m</option>
                            </select>
                        </label>
                        <label class="estate-form-label">
                            {{ __('capell-theme-estate-agents::generic.beds_label') }}
                            <select
                                name="beds"
                                class="estate-form-input"
                            >
                                <option>2+</option>
                                <option>3+</option>
                                <option>4+</option>
                            </select>
                        </label>
                        <button
                            type="submit"
                            class="estate-button-primary justify-center self-end"
                        >
                            {{ __('capell-theme-estate-agents::generic.search_button_label') }}
                        </button>
                    </div>
                </form>
            @else
                <div class="estate-search-form bg-white p-4">
                    <p class="text-sm leading-6 text-[var(--estate-muted)]">
                        {{ __('capell-theme-estate-agents::generic.search_static_summary') }}
                    </p>
                </div>
            @endif
        </div>
    </div>
</section>
