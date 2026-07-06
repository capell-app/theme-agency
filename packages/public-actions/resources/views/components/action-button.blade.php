@props ([
    'actionKey',
    'label',
    'payload' => [],
    'class' => '',
])

@php
    $safePayload = is_array($payload) ? $payload : [];
@endphp

@if (Route::has('capell-public-actions.submit') && filled($actionKey))
    <form
        method="post"
        action="{{ route('capell-public-actions.submit', ['action' => $actionKey]) }}"
        class="capell-action-button inline-flex"
    >
        @csrf

        @foreach ($safePayload as $payloadKey => $payloadValue)
            @if (is_string($payloadKey) && is_scalar($payloadValue) && filled((string) $payloadValue))
                <input
                    type="hidden"
                    name="{{ $payloadKey }}"
                    value="{{ (string) $payloadValue }}"
                />
            @endif
        @endforeach

        <button
            type="submit"
            {{ $attributes->merge(['class' => $class]) }}
        >
            {{ $label }}
        </button>
    </form>
@endif
