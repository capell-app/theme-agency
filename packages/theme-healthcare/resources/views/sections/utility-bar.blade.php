@php
    $links = $section->links ?? $section->items ?? [];
@endphp

<section
    class="healthcare-utility-bar border-b border-[#d9e8ee] bg-[#14323a] px-6 py-0 text-white"
>
    <div
        class="flex flex-col gap-3 py-3 text-sm font-semibold sm:flex-row sm:items-center sm:justify-between"
    >
        <p class="text-white">
            {{ $section->summary ?? __('capell-theme-healthcare::generic.utility_summary') }}
        </p>
        <div class="flex flex-wrap gap-4">
            @foreach ($links as $link)
                <a
                    href="{{ $link['url'] ?? '#' }}"
                    class="text-white underline-offset-4 hover:underline"
                >
                    {{ $link['label'] ?? $link['title'] ?? '' }}
                </a>
            @endforeach
        </div>
    </div>
</section>
