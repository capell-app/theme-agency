@php
    $formBuilderAvailable ??= false;
    $items = $section->items ?? [];
    $formMarkup = $section->formHtml ?? $section->formMarkup ?? null;
    $primaryAction = $section->primaryAction ?? $section->action ?? null;
    $phone = $section->phone ?? $section->telephone ?? null;
@endphp

<section class="healthcare-care bg-white">
    <div class="grid gap-8 px-6 lg:grid-cols-[1fr_0.9fr] lg:items-center">
        <div>
            <h2 class="text-4xl font-black tracking-tight text-[#14323a]">
                {{ $section->heading }}
            </h2>
            @if ($section->summary ?? null)
                <p class="mt-4 max-w-2xl text-lg text-stone-600">
                    {{ $section->summary }}
                </p>
            @endif

            <div class="mt-8 flex flex-wrap gap-3">
                @foreach ($items as $item)
                    <span
                        class="rounded-full border border-stone-200 bg-[#f6fbfd] px-4 py-2 text-sm font-bold text-[#14323a]"
                    >
                        {{ $item['title'] ?? $item['label'] ?? '' }}
                    </span>
                @endforeach
            </div>
        </div>

        <div class="healthcare-frame bg-[#14323a] p-6 text-white">
            <p
                class="text-xs font-black tracking-widest text-[#f59e0b] uppercase"
            >
                {{ $formBuilderAvailable ? __('capell-theme-healthcare::generic.booking_live') : __('capell-theme-healthcare::generic.booking_static') }}
            </p>
            <h3 class="mt-4 text-2xl font-black text-white">
                {{ $formBuilderAvailable ? __('capell-theme-healthcare::generic.booking_panel_live') : __('capell-theme-healthcare::generic.booking_panel_static') }}
            </h3>
            <p class="mt-3 text-sm text-stone-200">
                {{ $formBuilderAvailable ? __('capell-theme-healthcare::generic.booking_summary_live') : __('capell-theme-healthcare::generic.booking_summary_static') }}
            </p>
            @if ($formBuilderAvailable && $formMarkup)
                <div class="mt-5 rounded-lg bg-white p-4 text-[#14323a]">
                    {!! $formMarkup !!}
                </div>
            @else
                <div class="mt-5 grid gap-3">
                    <div class="rounded-lg bg-white/10 p-3">
                        <span class="text-sm font-bold text-white">
                            {{ __('capell-theme-healthcare::generic.patient_name') }}
                        </span>
                    </div>
                    <div class="rounded-lg bg-white/10 p-3">
                        <span class="text-sm font-bold text-white">
                            {{ __('capell-theme-healthcare::generic.preferred_service') }}
                        </span>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <span
                            class="rounded-lg bg-white px-3 py-2 text-sm font-black text-[#0f766e]"
                        >
                            {{ __('capell-theme-healthcare::generic.morning_slot') }}
                        </span>
                        <span
                            class="rounded-lg bg-[#f59e0b] px-3 py-2 text-sm font-black text-[#14323a]"
                        >
                            {{ __('capell-theme-healthcare::generic.afternoon_slot') }}
                        </span>
                    </div>
                    <div class="grid gap-2 sm:grid-cols-2">
                        @if (($primaryAction['url'] ?? null) && ($primaryAction['label'] ?? null))
                            <a
                                href="{{ $primaryAction['url'] }}"
                                class="rounded-lg bg-white px-4 py-3 text-center text-sm font-black text-[#0f766e]"
                            >
                                {{ $primaryAction['label'] }}
                            </a>
                        @else
                            <a
                                href="{{ $section->contactUrl ?? '/contact' }}"
                                class="rounded-lg bg-white px-4 py-3 text-center text-sm font-black text-[#0f766e]"
                            >
                                {{ __('capell-theme-healthcare::generic.booking_primary_cta') }}
                            </a>
                        @endif

                        @if ($phone)
                            <a
                                href="tel:{{ preg_replace('/[^0-9+]/', '', (string) $phone) }}"
                                class="rounded-lg border border-white/30 px-4 py-3 text-center text-sm font-black text-white"
                            >
                                {{ __('capell-theme-healthcare::generic.booking_secondary_cta') }}
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>
