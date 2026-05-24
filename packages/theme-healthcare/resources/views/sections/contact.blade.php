@php
    $locations = $section->locations ?? $section->items ?? [];
@endphp

<section class="healthcare-contact bg-[#f6fbfd]">
    <div class="grid gap-8 px-6 lg:grid-cols-[0.9fr_1.1fr]">
        <div>
            <h2>{{ $section->heading }}</h2>
            @if ($section->summary ?? null)
                <p class="mt-4 max-w-xl text-lg">{{ $section->summary }}</p>
            @endif
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            @foreach ($locations as $location)
                <article
                    class="rounded-lg border border-[#d9e8ee] bg-white p-6"
                >
                    <p
                        class="text-xs font-black uppercase tracking-widest text-[#2563eb]"
                    >
                        {{ $location['label'] ?? __('capell-theme-healthcare::generic.location') }}
                    </p>
                    <h3 class="mt-3 text-xl font-black">
                        {{ $location['title'] ?? '' }}
                    </h3>
                    <p class="mt-2 text-sm">
                        {{ $location['summary'] ?? $location['address'] ?? '' }}
                    </p>
                    @if ($location['phone'] ?? null)
                        <p class="mt-4 text-sm font-black text-[#0f766e]">
                            {{ $location['phone'] }}
                        </p>
                    @endif
                </article>
            @endforeach
        </div>
    </div>
</section>
