@php
    $sectionHeading = $heading ?? ($section->heading ?? null);
@endphp

<section
    class="theme-section theme-section-events bg-gradient-to-r from-emerald-950 to-slate-900 px-6 py-16 text-white"
>
    @if ($sectionHeading)
        <div class="mx-auto max-w-5xl">
            <p class="text-xs font-black tracking-[0.2em] text-emerald-200">
                {{ __('capell-theme-nonprofit::generic.events_label') }}
            </p>
            <h2 class="mt-3 text-4xl font-black tracking-tight">
                {{ $sectionHeading }}
            </h2>
            <p class="mt-4 max-w-2xl text-emerald-100">
                {{ $eventsAvailable ?? false ? __('capell-theme-nonprofit::generic.events_connected') : __('capell-theme-nonprofit::generic.events_static') }}
            </p>
        </div>
    @endif
</section>
