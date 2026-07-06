@props ([
    'related',
    'key',
])

<div
    style="
        --slide-size: 100%;
        --slide-size-sm: 50%;
        --slide-size-lg: calc(100% / {{ min(3, $related->count()) }});
    "
    {{ $attributes->class(['hero-related @container/related mt-4 grid gap-x-8 gap-y-4 lg:mt-6 lg:flex']) }}
>
    @foreach ($related as $feature)
        <div
            class="@container/item hero-related-item @md:basis-[var(--slide-size-sm)] @lg:shrink @lg:basis-[var(--slide-size-lg)] group min-w-0 shrink-0 grow-0 basis-[var(--slide-size)]"
        >
            <div
                class="flex flex-wrap items-center gap-x-4 gap-y-3 @2xs/item:flex-nowrap"
            >
                <div
                    class="prose prose-sm dark:prose-invert [&>:first-child]:mt-0 [&>:last-child]:mb-0 grid h-full grow"
                >
                    @if ($feature['title'])
                        <p class="text-md mb-1 leading-6 @2xs/item:text-lg">
                            @if ($feature['url'])
                                <a
                                    href="{{ $feature['url'] }}"
                                    wire:navigate
                                    class="text-link hover:text-primary-600 focus:text-primary-600 font-medium no-underline"
                                >
                                    <strong class="font-semibold">
                                        {{ $feature['title'] }}
                                    </strong>
                                </a>
                            @else
                                <strong class="font-semibold">
                                    {{ $feature['title'] }}
                                </strong>
                            @endif
                        </p>
                    @endif

                    @if ($feature['summary'])
                        <div
                            class="line-clamp-4 leading-6 font-medium break-words opacity-80"
                        >
                            {{ $feature['summary'] }}
                        </div>
                    @endif

                    @if ($feature['linkText'])
                        <a
                            class="text-link hover:text-primary focus:text-primary font-medium no-underline focus:underline"
                            href="{{ $feature['url'] }}"
                            title="{{ e(strip_tags((string) $feature['title'])) }}"
                            wire:navigate
                        >
                            {{ $feature['linkText'] }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>
