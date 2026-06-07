@php
    $clinician = $section->clinician ?? $section->profile ?? [];
    $clinician = is_array($clinician) ? $clinician : [];
    $credentials = $section->credentials ?? $clinician['credentials'] ?? [];
    $specialties = $section->specialties ?? $clinician['specialties'] ?? [];
    $languages = $section->languages ?? $clinician['languages'] ?? [];
    $availability = $section->availability ?? $clinician['availability'] ?? null;
    $acceptingPatients = $section->acceptingPatients ?? $clinician['acceptingPatients'] ?? $clinician['accepting_new_patients'] ?? null;
    $heading = $section->heading ?? $clinician['name'] ?? $clinician['title'] ?? __('capell-theme-healthcare::generic.clinician_profile_heading');
    $summary = $section->summary ?? $clinician['summary'] ?? $clinician['bio'] ?? __('capell-theme-healthcare::generic.clinician_profile_summary');
    $image = $section->image ?? $section->imageUrl ?? $clinician['image'] ?? $clinician['imageUrl'] ?? null;
    $imageAlt = $section->imageAlt ?? $clinician['imageAlt'] ?? $heading;
@endphp

<section class="healthcare-clinician-profile bg-[var(--healthcare-surface)]">
    <div class="grid gap-8 px-6 lg:grid-cols-[0.82fr_1.18fr] lg:items-start">
        <div class="healthcare-frame bg-white p-3">
            @if ($image)
                <img
                    src="{{ $image }}"
                    alt="{{ $imageAlt }}"
                    width="960"
                    height="1120"
                    loading="lazy"
                    decoding="async"
                    class="aspect-[6/7] w-full rounded-xl object-cover"
                />
            @else
                <div
                    class="grid aspect-[6/7] place-items-end rounded-xl bg-[var(--healthcare-ink)] p-6 text-white"
                >
                    <div>
                        <p
                            class="text-xs font-black tracking-widest text-[var(--healthcare-accent)] uppercase"
                        >
                            {{ __('capell-theme-healthcare::generic.clinician_profile_label') }}
                        </p>
                        <p class="mt-4 text-3xl font-black text-white">
                            {{ $heading }}
                        </p>
                    </div>
                </div>
            @endif
        </div>

        <div>
            <p
                class="text-xs font-black tracking-widest text-[var(--healthcare-primary)] uppercase"
            >
                {{ __('capell-theme-healthcare::generic.clinician_profile_label') }}
            </p>
            <h2
                class="mt-3 text-4xl font-black tracking-tight text-[var(--healthcare-ink)]"
            >
                {{ $heading }}
            </h2>
            @if ($summary)
                <p class="mt-4 max-w-3xl text-lg text-stone-600">
                    {{ $summary }}
                </p>
            @endif

            <div class="mt-6 grid gap-3 sm:grid-cols-2">
                @if ($credentials !== [])
                    <div
                        class="rounded-xl border border-[var(--healthcare-line)] bg-white p-4"
                    >
                        <p
                            class="text-xs font-black text-[var(--healthcare-primary)] uppercase"
                        >
                            {{ __('capell-theme-healthcare::generic.clinician_credentials_label') }}
                        </p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($credentials as $credential)
                                <span
                                    class="rounded-full bg-[var(--healthcare-primary-soft)] px-3 py-1 text-sm font-bold text-[var(--healthcare-primary)]"
                                >
                                    {{ is_array($credential) ? $credential['label'] ?? $credential['title'] ?? '' : $credential }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($specialties !== [])
                    <div
                        class="rounded-xl border border-[var(--healthcare-line)] bg-white p-4"
                    >
                        <p
                            class="text-xs font-black text-[var(--healthcare-primary)] uppercase"
                        >
                            {{ __('capell-theme-healthcare::generic.clinician_specialties_label') }}
                        </p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($specialties as $specialty)
                                <span
                                    class="rounded-full border border-[var(--healthcare-line)] px-3 py-1 text-sm font-bold text-[var(--healthcare-ink)]"
                                >
                                    {{ is_array($specialty) ? $specialty['label'] ?? $specialty['title'] ?? '' : $specialty }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($languages !== [])
                    <div
                        class="rounded-xl border border-[var(--healthcare-line)] bg-white p-4"
                    >
                        <p
                            class="text-xs font-black text-[var(--healthcare-primary)] uppercase"
                        >
                            {{ __('capell-theme-healthcare::generic.clinician_languages_label') }}
                        </p>
                        <p
                            class="mt-3 text-sm font-bold text-[var(--healthcare-ink)]"
                        >
                            {{ implode(', ', array_map(static fn (mixed $language): string => is_array($language) ? (string) ($language['label'] ?? $language['title'] ?? '') : (string) $language, $languages)) }}
                        </p>
                    </div>
                @endif

                @if ($availability || is_bool($acceptingPatients))
                    <div
                        class="rounded-xl border border-[var(--healthcare-line)] bg-white p-4"
                    >
                        <p
                            class="text-xs font-black text-[var(--healthcare-primary)] uppercase"
                        >
                            {{ __('capell-theme-healthcare::generic.clinician_availability_label') }}
                        </p>
                        <p
                            class="mt-3 text-sm font-bold text-[var(--healthcare-ink)]"
                        >
                            {{ $availability ?? ($acceptingPatients ? __('capell-theme-healthcare::generic.clinician_accepting_patients') : __('capell-theme-healthcare::generic.clinician_not_accepting_patients')) }}
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
