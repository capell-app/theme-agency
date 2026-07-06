@php
    $quote = data_get($section, 'quote', data_get($section, 'text', ''));
    $attributionName = data_get($section, 'attributionName', data_get($section, 'name'));
    $attributionRole = data_get($section, 'attributionRole', data_get($section, 'role'));
    $attributionImage = data_get($section, 'attributionImage', data_get($section, 'image'));
@endphp

{{--
    quote-context-weaving, medallion-left variant — the same mechanic as
    the default treatment, with the margin medallion docked to the start
    (left) of the quote rather than the end. See quote-context.blade.php
    for the full mechanic description.
--}}
<section
    id="quote-context"
    class="eser-section eser-quote-context"
>
    <div class="eser-section-inner">
        <figure
            class="eser-quote-context-figure eser-quote-context-medallion-start"
        >
            @if (filled($attributionName))
                <figcaption class="eser-quote-context-medallion">
                    <span
                        class="eser-quote-context-medallion-trigger"
                        tabindex="0"
                        title="{{ trim(($attributionRole ? $attributionRole . ' — ' : '') . $attributionName) }}"
                    >
                        @if (filled($attributionImage))
                            <img
                                src="{{ $attributionImage }}"
                                alt="{{ $attributionName }}"
                                width="56"
                                height="56"
                                loading="lazy"
                                decoding="async"
                                class="eser-quote-context-portrait"
                            />
                        @else
                            <span
                                class="eser-quote-context-initial"
                                aria-hidden="true"
                            >
                                {{ mb_substr((string) $attributionName, 0, 1) }}
                            </span>
                        @endif
                    </span>
                    <span class="eser-quote-context-context">
                        <span
                            class="eser-quote-context-name"
                            >{{ $attributionName }}</span
                        >
                        @if (filled($attributionRole))
                            <span
                                class="eser-quote-context-role"
                                >{{ $attributionRole }}</span
                            >
                        @endif
                    </span>
                </figcaption>
            @endif

            <blockquote class="eser-quote-context-quote">
                <p>{{ $quote }}</p>
            </blockquote>
        </figure>
    </div>
</section>
