@php
    $brandName = $section->brandName ?? ($brandName ?? __('capell-theme-restaurant::generic.brand_name'));
    $items = $section->items ?? ($items ?? []);
    $publicThemeUrl = 'Capell\\ThemeStudio\\Restaurant\\Support\\PublicThemeUrl';
@endphp

<footer class="restaurant-footer px-6 py-10">
    <div class="mx-auto grid max-w-6xl gap-8 lg:grid-cols-[1fr_auto]">
        <div>
            <p class="text-xl font-black text-white">{{ $brandName }}</p>
            <p class="mt-3 max-w-xl text-sm leading-6 text-white/65">
                {{ __('capell-theme-restaurant::generic.footer_summary') }}
            </p>
        </div>
        <div class="flex flex-wrap gap-5 text-sm font-bold">
            @foreach ($items as $item)
                @php
                    $itemUrl = $publicThemeUrl::link($item['url'] ?? null);
                @endphp

                @if ($itemUrl !== null)
                    <a
                        href="{{ $itemUrl }}"
                        class="text-white/75 hover:text-white"
                    >
                        {{ $item['label'] ?? $item['title'] ?? '' }}
                    </a>
                @else
                    <span class="text-white/75">
                        {{ $item['label'] ?? $item['title'] ?? '' }}
                    </span>
                @endif
            @endforeach
        </div>
    </div>
</footer>
