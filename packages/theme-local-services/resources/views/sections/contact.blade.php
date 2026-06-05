@php
    $sectionHeading = $heading ?? ($section->heading ?? null);
    $contact = $section->contact ?? [];

    $phone = data_get($section, 'phone', data_get($contact, 'phone'));
    $phone = is_scalar($phone) ? trim((string) $phone) : '';
    $phoneHref = preg_replace('/(?!^\+)[^\d]/', '', $phone) ?: '';
    $phoneHref = preg_match('/\d/', $phoneHref) === 1 ? $phoneHref : '';

    $email = data_get($section, 'email', data_get($contact, 'email'));
    $email = is_scalar($email) ? trim((string) $email) : '';
    $emailHref = filter_var($email, FILTER_VALIDATE_EMAIL) ? 'mailto:' . $email : '';

    $address = data_get($section, 'address', data_get($contact, 'address'));
    $address = is_scalar($address) ? trim((string) $address) : '';

    $mapUrl = data_get($section, 'mapUrl', data_get($section, 'map_url', data_get($contact, 'mapUrl', data_get($contact, 'map_url'))));
    $mapUrl = is_scalar($mapUrl) ? trim((string) $mapUrl) : '';
    $mapScheme = parse_url($mapUrl, PHP_URL_SCHEME);
    $mapHref = in_array($mapScheme, ['http', 'https'], true) ? $mapUrl : '';

    $contactCards = [
        [
            'label' => __('capell-theme-local-services::generic.contact_card_calls'),
            'value' => $phone,
            'href' => $phoneHref !== '' ? 'tel:' . $phoneHref : '',
            'summary' => $phoneHref !== ''
                ? __('capell-theme-local-services::generic.contact_card_calls_action')
                : __('capell-theme-local-services::generic.contact_card_calls_summary'),
        ],
        [
            'label' => __('capell-theme-local-services::generic.contact_card_quotes'),
            'value' => $email,
            'href' => $emailHref,
            'summary' => $emailHref !== ''
                ? __('capell-theme-local-services::generic.contact_card_quotes_action')
                : __('capell-theme-local-services::generic.contact_card_quotes_summary'),
        ],
        [
            'label' => __('capell-theme-local-services::generic.contact_card_visits'),
            'value' => $address,
            'href' => $address !== '' ? $mapHref : '',
            'summary' => $address !== ''
                ? __('capell-theme-local-services::generic.contact_card_visits_action')
                : __('capell-theme-local-services::generic.contact_card_visits_summary'),
        ],
        [
            'label' => __('capell-theme-local-services::generic.contact_card_map'),
            'value' => $mapHref !== '' ? __('capell-theme-local-services::generic.contact_card_map_value') : '',
            'href' => $mapHref,
            'summary' => $mapHref !== ''
                ? __('capell-theme-local-services::generic.contact_card_map_action')
                : __('capell-theme-local-services::generic.contact_card_map_summary'),
        ],
    ];
@endphp

<section
    id="contact"
    class="theme-section theme-section-contact bg-white"
>
    <div class="mx-auto max-w-5xl px-6 py-14">
        <div class="grid gap-8 lg:grid-cols-[0.75fr_1fr] lg:items-start">
            <div>
                <p
                    class="text-xs font-black tracking-[0.18em] text-[#0f766e] uppercase"
                >
                    {{ __('capell-theme-local-services::generic.contact_label') }}
                </p>
                @if ($sectionHeading)
                    <h2
                        class="mt-4 text-3xl font-black tracking-tight text-[#17211c]"
                    >
                        {{ $sectionHeading }}
                    </h2>
                @endif

                <p class="mt-4 max-w-2xl text-slate-600">
                    {{ __('capell-theme-local-services::generic.contact_copy') }}
                </p>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                @foreach ($contactCards as $card)
                    <article class="border border-slate-200 bg-[#f8fafc] p-5">
                        <p class="text-sm font-black text-[#17211c]">
                            {{ $card['label'] }}
                        </p>
                        @if ($card['value'] !== '')
                            @if ($card['href'] !== '')
                                <a
                                    href="{{ $card['href'] }}"
                                    class="mt-2 inline-flex text-base font-black text-[#0f766e] hover:text-[#115e59]"
                                >
                                    {{ $card['value'] }}
                                </a>
                            @else
                                <p
                                    class="mt-2 text-base font-black text-[#17211c]"
                                >
                                    {{ $card['value'] }}
                                </p>
                            @endif
                        @endif

                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            {{ $card['summary'] }}
                        </p>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
</section>
