@php
    $json = $schemaJson ?? [];
    $jsonFlags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
@endphp

@if ($json !== [])
    {!! '<script type="application/ld+json">' . json_encode($json, $jsonFlags) . '</script>' !!}
@endif
