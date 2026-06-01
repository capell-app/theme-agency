@php
    $jsonFlags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
@endphp

@if (is_array($websiteSchema ?? null))
    {!! '<script type="application/ld+json">' . json_encode($websiteSchema, $jsonFlags) . '</script>' !!}
@endif
