@php
    $payload = json_encode($config->toArray(), JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_HEX_TAG);
@endphp

@if ($config->enabled)
    <div
        data-capell-live-chat-widget
        data-config="{{ $payload }}"
    ></div>
    <script
        async
        src="{{ route('capell-live-chat.widget.script') }}"
        data-capell-live-chat-script
    ></script>
@endif
