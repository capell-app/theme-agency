@php
    $heading ??= $section->heading ?? null;
    $contactRoutes = $section->items ?? [
        ['title' => __('capell-theme-nonprofit::generic.contact_route_donate')],
        ['title' => __('capell-theme-nonprofit::generic.contact_route_volunteer')],
        ['title' => __('capell-theme-nonprofit::generic.contact_route_partner')],
    ];
@endphp

<section class="theme-section theme-section-contact nonprofit-bg-surface-warm">
    <div class="mx-auto max-w-5xl px-6 py-14">
        @isset($heading)
            <p
                class="nonprofit-text-accent-strong text-xs font-black tracking-[0.18em] uppercase"
            >
                {{ __('capell-theme-nonprofit::generic.contact_label') }}
            </p>
            <h2
                class="nonprofit-text-ink mt-4 max-w-2xl text-4xl font-black tracking-tight"
            >
                {{ $heading }}
            </h2>
        @endisset

        <div class="mt-10 grid gap-4 md:grid-cols-3">
            @foreach ($contactRoutes as $route)
                <article class="nonprofit-border-accent border bg-white p-5">
                    <p
                        class="nonprofit-text-primary text-xs font-black tracking-[0.18em] uppercase"
                    >
                        {{ $route['label'] ?? __('capell-theme-nonprofit::generic.contact_route_label') }}
                    </p>
                    <h3 class="nonprofit-text-ink mt-3 text-lg font-black">
                        {{ $route['title'] ?? __('capell-theme-nonprofit::generic.contact_route_label') }}
                    </h3>
                    <p class="mt-3 text-sm leading-6 text-slate-600">
                        {{ $route['summary'] ?? __('capell-theme-nonprofit::generic.contact_route_summary') }}
                    </p>
                </article>
            @endforeach
        </div>
    </div>
</section>
