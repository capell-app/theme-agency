@props ([
    'area',
    'publicActionKey' => null,
    'action' => null,
    'label' => __('capell-access-gate::public.cta.submit'),
    'email' => null,
    'requestedUrl' => null,
])

@php
    use Illuminate\Support\Facades\Route;

    $areaKey = is_object($area) && isset($area->key) ? $area->key : (string) $area;
    $resolvedPublicActionKey = $publicActionKey ?? $action;
    $targetUrl = $resolvedPublicActionKey !== null && Route::has('capell-public-actions.submit')
        ? route('capell-public-actions.submit', ['action' => $resolvedPublicActionKey])
        : route('capell-access-gate.request.store', ['area' => $areaKey]);
@endphp

<form
    method="post"
    action="{{ $targetUrl }}"
    class="capell-request-cta"
>
    @csrf
    <input
        type="hidden"
        name="area"
        value="{{ $areaKey }}"
    />

    @if ($requestedUrl !== null)
        <input
            type="hidden"
            name="requested_url"
            value="{{ $requestedUrl }}"
        />
    @endif

    @if ($email !== null)
        <input
            type="hidden"
            name="email"
            value="{{ $email }}"
        />
    @endif

    <button type="submit">{{ $label }}</button>
</form>
