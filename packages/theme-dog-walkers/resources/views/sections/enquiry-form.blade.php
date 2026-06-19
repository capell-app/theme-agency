@php
    use Illuminate\Support\Facades\Route;

    $bookingsAvailable ??= false;
    $formBuilderAvailable ??= false;
    $sectionHeading = $heading ?? ($section->heading ?? null);
    $formHandle = $section->formHandle ?? $section->form_handle ?? $section->handle ?? null;
    $formAction = $section->formAction ?? $section->action ?? ($formAction ?? '/contact');
    $formMethod = strtoupper((string) ($section->formMethod ?? $method ?? 'POST'));
    $formMethod = in_array($formMethod, ['GET', 'POST'], true) ? $formMethod : 'POST';
    $formAction = is_string($formAction) && trim($formAction) !== '' ? trim($formAction) : '/contact';
    $bookingAction = $section->bookingUrl
        ?? $section->booking_url
        ?? ($bookingsAvailable && Route::has('capell-bookings.request') ? route('capell-bookings.request') : null);
    $bookingAction = is_string($bookingAction) && trim($bookingAction) !== '' ? trim($bookingAction) : null;
@endphp

<section
    id="enquiry"
    class="theme-section theme-section-enquiry-form bg-[#f8fafc]"
>
    <div class="mx-auto max-w-5xl px-6 py-14">
        <div class="grid gap-8 lg:grid-cols-[0.82fr_1fr] lg:items-start">
            <div>
                <p
                    class="text-xs font-black tracking-[0.18em] text-[#ea580c] uppercase"
                >
                    {{ __('capell-theme-dog-walkers::generic.enquiry_form_label') }}
                </p>
                @if ($sectionHeading)
                    <h2
                        class="mt-4 text-3xl font-black tracking-tight text-[#17211c]"
                    >
                        {{ $sectionHeading }}
                    </h2>
                @endif

                <p class="mt-4 max-w-2xl text-slate-600">
                    {{ $formBuilderAvailable ?? false ? __('capell-theme-dog-walkers::generic.enquiry_form_copy') : __('capell-theme-dog-walkers::generic.enquiry_form_copy_fallback') }}
                </p>
            </div>

            <div class="border border-[#99f6e4] bg-white p-5 shadow-sm">
                @if (($formBuilderAvailable ?? false) && is_string($formHandle) && trim($formHandle) !== '')
                    @livewire('capell-form-builder::form', [
                        'handle' => trim($formHandle),
                        'instanceId' => 'theme-dog-walkers-enquiry',
                    ])
                @else
                    <form
                        action="{{ $formAction }}"
                        method="{{ $formMethod }}"
                        aria-label="{{ __('capell-theme-dog-walkers::generic.enquiry_form_aria_label') }}"
                    >
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label
                                    class="text-xs font-black tracking-[0.16em] text-slate-500 uppercase"
                                    for="dog-walkers-enquiry-name"
                                >
                                    {{ __('capell-theme-dog-walkers::generic.enquiry_field_name') }}
                                </label>
                                <input
                                    id="dog-walkers-enquiry-name"
                                    name="name"
                                    type="text"
                                    autocomplete="name"
                                    required
                                    class="mt-2 w-full border border-slate-200 bg-[#f8fafc] px-4 py-3 text-sm text-[#17211c] transition outline-none focus:border-[#0f766e] focus:bg-white"
                                />
                            </div>

                            <div>
                                <label
                                    class="text-xs font-black tracking-[0.16em] text-slate-500 uppercase"
                                    for="dog-walkers-enquiry-phone"
                                >
                                    {{ __('capell-theme-dog-walkers::generic.enquiry_field_phone') }}
                                </label>
                                <input
                                    id="dog-walkers-enquiry-phone"
                                    name="phone"
                                    type="tel"
                                    autocomplete="tel"
                                    required
                                    class="mt-2 w-full border border-slate-200 bg-[#f8fafc] px-4 py-3 text-sm text-[#17211c] transition outline-none focus:border-[#0f766e] focus:bg-white"
                                />
                            </div>

                            <div>
                                <label
                                    class="text-xs font-black tracking-[0.16em] text-slate-500 uppercase"
                                    for="dog-walkers-enquiry-postcode"
                                >
                                    {{ __('capell-theme-dog-walkers::generic.enquiry_field_postcode') }}
                                </label>
                                <input
                                    id="dog-walkers-enquiry-postcode"
                                    name="postcode"
                                    type="text"
                                    autocomplete="postal-code"
                                    class="mt-2 w-full border border-slate-200 bg-[#f8fafc] px-4 py-3 text-sm text-[#17211c] transition outline-none focus:border-[#0f766e] focus:bg-white"
                                />
                            </div>

                            <div>
                                <label
                                    class="text-xs font-black tracking-[0.16em] text-slate-500 uppercase"
                                    for="dog-walkers-enquiry-walk"
                                >
                                    {{ __('capell-theme-dog-walkers::generic.enquiry_field_walk') }}
                                </label>
                                <input
                                    id="dog-walkers-enquiry-walk"
                                    name="walk"
                                    type="text"
                                    required
                                    class="mt-2 w-full border border-slate-200 bg-[#f8fafc] px-4 py-3 text-sm text-[#17211c] transition outline-none focus:border-[#0f766e] focus:bg-white"
                                />
                            </div>
                        </div>

                        <div class="mt-4">
                            <label
                                class="text-xs font-black tracking-[0.16em] text-slate-500 uppercase"
                                for="dog-walkers-enquiry-message"
                            >
                                {{ __('capell-theme-dog-walkers::generic.enquiry_field_message') }}
                            </label>
                            <textarea
                                id="dog-walkers-enquiry-message"
                                name="message"
                                rows="4"
                                class="mt-2 w-full border border-slate-200 bg-[#f8fafc] px-4 py-3 text-sm text-[#17211c] transition outline-none focus:border-[#0f766e] focus:bg-white"
                            ></textarea>
                        </div>

                        <div
                            class="mt-4 flex items-center justify-between border border-[#fed7aa] bg-[#fff7ed] p-4"
                        >
                            <p class="text-sm font-black text-[#9a3412]">
                                {{ __('capell-theme-dog-walkers::generic.enquiry_eta') }}
                            </p>
                            <span class="text-sm font-black text-[#0f766e]">
                                {{ __('capell-theme-dog-walkers::generic.enquiry_ready') }}
                            </span>
                        </div>

                        <button
                            type="submit"
                            class="mt-4 w-full bg-[#0f766e] px-5 py-3 text-sm font-black text-white transition hover:bg-[#115e59] focus:ring-2 focus:ring-[#0f766e] focus:ring-offset-2 focus:outline-none"
                        >
                            {{ __('capell-theme-dog-walkers::generic.enquiry_submit_label') }}
                        </button>
                    </form>
                @endif

                @if (($bookingsAvailable ?? false) && $bookingAction)
                    <div class="mt-4 border border-[#d1fae5] bg-[#ecfdf5] p-4">
                        <p class="text-sm font-black text-[#065f46]">
                            {{ __('capell-theme-dog-walkers::generic.enquiry_booking_ready') }}
                        </p>
                        <a
                            href="{{ $bookingAction }}"
                            class="mt-3 inline-flex w-full items-center justify-center bg-[#13231f] px-4 py-3 text-sm font-black text-white transition hover:bg-[#0f766e]"
                        >
                            {{ __('capell-theme-dog-walkers::generic.enquiry_booking_action') }}
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
