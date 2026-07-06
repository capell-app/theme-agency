@php
    $quote = data_get($section, 'quote', data_get($section, 'text', ''));
    $attributionName = data_get($section, 'attributionName', data_get($section, 'name'));
    $attributionRole = data_get($section, 'attributionRole', data_get($section, 'role'));
    $attributionImage = data_get($section, 'attributionImage', data_get($section, 'image'));
@endphp

{{--
    quote-context-weaving (Part 2 §B) — the merged pull-quote mechanic: the
    quote itself is set into the running text at a larger display scale
    (`.eser-quote-context blockquote`), with a margin "medallion" carrying
    the speaker's portrait/initial. The medallion is a `<button>` acting as
    a disclosure trigger: `title` on the trigger is the no-JS fallback (a
    native OS tooltip on hover/long-press), and the `aria-describedby`
    popover beneath is a pure-CSS `:focus`/`:hover` reveal — no JS shipped
    for this widget at all, keeping it at 0KB against the §0.4 budget.
    Default variant docks the medallion to the end (right) of the quote;
    the `medallion-left` variant (quote-context--medallion-left.blade.php)
    docks it to the start.
--}}
<section
    id="quote-context"
    class="eser-section eser-quote-context"
>
    <div class="eser-section-inner">
        <figure
            class="eser-quote-context-figure eser-quote-context-medallion-end"
        >
            <blockquote class="eser-quote-context-quote">
                <p>{{ $quote }}</p>
            </blockquote>

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
        </figure>
    </div>
</section>
