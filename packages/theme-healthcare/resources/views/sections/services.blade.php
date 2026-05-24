@php
    $services = $section->features ?? $section->items ?? [];
@endphp

<section class="healthcare-services bg-white">
    <div class="px-6">
        <div
            class="flex flex-col justify-between gap-5 md:flex-row md:items-end"
        >
            <div>
                <h2>{{ $section->heading }}</h2>
                @if ($section->summary ?? null)
                    <p class="mt-4 max-w-2xl text-lg">
                        {{ $section->summary }}
                    </p>
                @endif
            </div>
        </div>

        <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($services as $service)
                <article
                    class="rounded-xl border border-stone-200 bg-[#f6fbfd] p-3"
                >
                    @if ($service['image'] ?? $service['imageUrl'] ?? null)
                        <img
                            src="{{ $service['image'] ?? $service['imageUrl'] }}"
                            alt="{{ $service['imageAlt'] ?? '' }}"
                            class="aspect-square w-full rounded-lg object-cover"
                        />
                    @else
                        <div class="aspect-square rounded-lg bg-white"></div>
                    @endif
                    <div class="p-2">
                        <h3 class="text-lg font-black">
                            {{ $service['title'] }}
                        </h3>
                        <p class="mt-2 text-sm">
                            {{ $service['description'] ?? $service['summary'] ?? '' }}
                        </p>
                        @if ($service['price'] ?? $service['metric'] ?? null)
                            <p class="mt-4 text-sm font-black text-[#0f766e]">
                                {{ $service['price'] ?? $service['metric'] }}
                            </p>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
