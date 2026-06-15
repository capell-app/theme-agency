@php
    $publicThemeUrl = 'Capell\\ThemeStudio\\Restaurant\\Support\\PublicThemeUrl';
    $heading = $section->heading ?? ($heading ?? __('capell-theme-restaurant::generic.reservation_heading'));
    $summary = $section->summary ?? ($summary ?? __('capell-theme-restaurant::generic.reservation_summary'));
    $formAction = $section->form_action ?? ($formAction ?? ($form_action ?? null));
    $formAction = $publicThemeUrl::formAction($formAction);
    $formBuilderAvailable ??= false;
    $bookingsAvailable ??= false;
@endphp

<section
    id="reservations"
    class="theme-section restaurant-band px-6 py-16"
>
    <div
        class="mx-auto grid max-w-6xl gap-8 lg:grid-cols-[0.92fr_1.08fr] lg:items-start"
    >
        <div>
            <p class="restaurant-eyebrow">
                {{ __('capell-theme-restaurant::generic.reserve_label') }}
            </p>
            <h2 class="mt-4 text-4xl leading-tight font-black">
                {{ $heading }}
            </h2>
            @if ($summary)
                <p class="mt-5 text-lg leading-8 text-white/75">
                    {{ $summary }}
                </p>
            @endif

            <div class="mt-8 grid gap-3 sm:grid-cols-2">
                <div class="restaurant-dark-panel">
                    <p class="restaurant-small-label text-white/60">
                        {{ __('capell-theme-restaurant::generic.booking_status_label') }}
                    </p>
                    <p class="mt-2 text-xl font-black">
                        {{ $bookingsAvailable ? __('capell-theme-restaurant::generic.booking_connected') : __('capell-theme-restaurant::generic.booking_static') }}
                    </p>
                </div>
                <div class="restaurant-dark-panel">
                    <p class="restaurant-small-label text-white/60">
                        {{ __('capell-theme-restaurant::generic.form_status_label') }}
                    </p>
                    <p class="mt-2 text-xl font-black">
                        {{ $formBuilderAvailable ? __('capell-theme-restaurant::generic.form_connected') : __('capell-theme-restaurant::generic.form_static') }}
                    </p>
                </div>
            </div>
        </div>

        @if ($formAction !== null)
            <form
                action="{{ $formAction }}"
                method="GET"
                class="restaurant-reservation-form bg-white p-5"
            >
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="restaurant-form-label">
                        {{ __('capell-theme-restaurant::generic.date_label') }}
                        <input
                            type="date"
                            name="date"
                            class="restaurant-form-input"
                        />
                    </label>
                    <label class="restaurant-form-label">
                        {{ __('capell-theme-restaurant::generic.guests_label') }}
                        <select
                            name="guests"
                            class="restaurant-form-input"
                        >
                            <option>2</option>
                            <option>4</option>
                            <option>6</option>
                        </select>
                    </label>
                    <label class="restaurant-form-label">
                        {{ __('capell-theme-restaurant::generic.time_label') }}
                        <select
                            name="time"
                            class="restaurant-form-input"
                        >
                            <option>18:30</option>
                            <option>20:00</option>
                            <option>21:30</option>
                        </select>
                    </label>
                    <label class="restaurant-form-label">
                        {{ __('capell-theme-restaurant::generic.occasion_label') }}
                        <input
                            type="text"
                            name="occasion"
                            class="restaurant-form-input"
                        />
                    </label>
                </div>
                <button
                    type="submit"
                    class="restaurant-button-primary mt-5 w-full justify-center"
                >
                    {{ __('capell-theme-restaurant::generic.check_tables_label') }}
                </button>
            </form>
        @else
            <div class="restaurant-reservation-form bg-white p-5">
                <p class="restaurant-small-label">
                    {{ __('capell-theme-restaurant::generic.reservation_static_label') }}
                </p>
                <h3 class="mt-3 text-2xl font-black">
                    {{ __('capell-theme-restaurant::generic.reservation_static_heading') }}
                </h3>
                <p
                    class="mt-4 text-sm leading-6 text-[var(--restaurant-muted)]"
                >
                    {{ __('capell-theme-restaurant::generic.reservation_static_summary') }}
                </p>
            </div>
        @endif
    </div>
</section>
