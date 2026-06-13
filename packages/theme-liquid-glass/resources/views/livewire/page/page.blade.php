@php
    use Capell\Core\ThemeStudio\Actions\RenderCurrentThemePageAction;
@endphp

{!! RenderCurrentThemePageAction::run($page, $site, $language) !!}
