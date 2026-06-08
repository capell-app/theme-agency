@php
    $items = $section->items ?? $section->faculty ?? [];
@endphp

<section class="theme-section theme-section-faculty-directory bg-[#f8fbff]">
    <div class="mx-auto max-w-6xl px-6 py-16">
        <div class="grid gap-5 md:grid-cols-[0.72fr_1fr] md:items-end">
            <div>
                <p
                    class="text-xs font-black tracking-[0.16em] text-[#0f766e] uppercase"
                >
                    {{ __('capell-theme-education::generic.faculty_directory_label') }}
                </p>
                <h2 class="mt-3 text-4xl font-black text-[#111827]">
                    {{ $section->heading ?? __('capell-theme-education::generic.faculty_directory_heading') }}
                </h2>
            </div>
            @if ($section->summary ?? null)
                <p class="text-base leading-8 text-slate-600">
                    {{ $section->summary }}
                </p>
            @endif
        </div>

        <div class="mt-8 grid gap-4 md:grid-cols-3">
            @foreach ($items as $item)
                <article
                    class="rounded-xl border border-[#c7d2fe] bg-white p-5"
                >
                    <div
                        class="grid size-16 place-items-center rounded-full bg-[#eef2ff] text-lg font-black text-[#4338ca]"
                    >
                        {{ $item['initials'] ?? substr((string) ($item['name'] ?? 'ED'), 0, 2) }}
                    </div>
                    <h3 class="mt-4 text-lg font-black text-[#111827]">
                        {{ $item['name'] ?? __('capell-theme-education::generic.faculty_member_label') }}
                    </h3>
                    <p
                        class="mt-1 text-xs font-black tracking-widest text-[#0f766e] uppercase"
                    >
                        {{ $item['role'] ?? __('capell-theme-education::generic.faculty_role_label') }}
                    </p>
                    <p class="mt-3 text-sm leading-7 text-slate-600">
                        {{ $item['summary'] ?? __('capell-theme-education::generic.faculty_directory_ready') }}
                    </p>
                </article>
            @endforeach
        </div>
    </div>
</section>
