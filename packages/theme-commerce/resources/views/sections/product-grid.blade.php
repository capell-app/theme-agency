@php
    $products = $section->features ?? $section->items ?? [];
@endphp

<section class="retail-products bg-white">
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
            @foreach ($products as $product)
                <article
                    class="rounded-xl border border-stone-200 bg-[#fffaf3] p-3"
                >
                    @if ($product['image'] ?? $product['imageUrl'] ?? null)
                        <img
                            src="{{ $product['image'] ?? $product['imageUrl'] }}"
                            alt="{{ $product['imageAlt'] ?? '' }}"
                            class="aspect-square w-full rounded-lg object-cover"
                        />
                    @else
                        <div class="aspect-square rounded-lg bg-white"></div>
                    @endif
                    <div class="p-2">
                        <h3 class="text-lg font-black">
                            {{ $product['title'] }}
                        </h3>
                        <p class="mt-2 text-sm">
                            {{ $product['description'] ?? $product['summary'] ?? '' }}
                        </p>
                        @if ($product['price'] ?? $product['metric'] ?? null)
                            <p class="mt-4 text-sm font-black text-[#1f5f4a]">
                                {{ $product['price'] ?? $product['metric'] }}
                            </p>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
