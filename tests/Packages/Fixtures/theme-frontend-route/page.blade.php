@php
    use Capell\Core\ThemeStudio\Actions\RenderCurrentThemePageAction;
    use Capell\Core\ThemeStudio\Contracts\ThemeRuntimeSettings;
    use Capell\Frontend\Facades\Frontend;

    $themeFrontendTheme = Frontend::theme();
    $themeFrontendThemeKey = $themeFrontendTheme?->key ?? resolve(ThemeRuntimeSettings::class)->activeTheme();
@endphp

{!! RenderCurrentThemePageAction::run(activeTheme: $themeFrontendThemeKey) !!}
