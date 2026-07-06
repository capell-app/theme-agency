@php
    use Capell\Frontend\Enums\RenderHookLocation;
    use Capell\Frontend\Facades\Frontend;
    use Capell\Frontend\Support\Render\RenderHookRegistry;
    use Capell\Frontend\Support\Security\JsonLdScriptSanitizer;
    use Illuminate\Support\Facades\Route;

    $publicRenderData ??= Frontend::getFrontendData('publicPageRenderData');
    $assetManifest ??= Frontend::getFrontendData('assetManifest');
    $runtimeManifest ??= Frontend::getFrontendData('runtimeManifest');
    $mediaHints ??= Frontend::getFrontendData('mediaHints') ?? [];
    $pageRecord ??= Frontend::page();
    $site ??= Frontend::site();
    $language ??= Frontend::language();
    $layout ??= Frontend::layout();
    $theme ??= Frontend::theme();
    $siteMeta = $site?->meta ?? [];
    $metaSchema = data_get($siteMeta, 'meta_schema');
    $customMetaSchema = data_get($siteMeta, 'custom_meta_schema');
    $beaconRouteName = config('capell-page.frontend.route_name', 'capell-frontend.beacon');
    $usesBeacon = ($runtimeManifest?->usesBeacon ?? false)
        && is_string($beaconRouteName)
        && Route::has($beaconRouteName);
@endphp

@push ('styles')
    <x-inertia::head />
@endpush

<!DOCTYPE html>
<html
    class="h-full"
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
>
    <x-capell::app.head
        :livewire-enabled="false"
        :runtime-manifest="$runtimeManifest"
        :asset-manifest="$assetManifest"
        :media-hints="$mediaHints"
    />

    <x-capell::app.body
        :layout="$layout"
        :language="$language"
        :page-record="$pageRecord"
        :site="$site"
        :theme="$theme"
    >
        <x-inertia::app />

        {!! app(RenderHookRegistry::class)->renderAll(RenderHookLocation::BodyEnd) !!}

        @if ($usesBeacon)
            <x-capell::page-data />
        @endif

        @if ($metaSchema)
            @foreach ($metaSchema as $schema)
                <x-dynamic-component :component="$schema" />
            @endforeach
        @endif

        @if ($customMetaSchema)
            <script type="application/ld+json">
                {!! JsonLdScriptSanitizer::sanitize((string) $customMetaSchema) !!}
            </script>
        @endif
    </x-capell::app.body>
</html>
