<main
    data-theme="{{ $themeKey }}"
    style="
        --primary: {{ $brand->primaryColor }};
        --accent: {{ $brand->accentColor }};
    "
>
    {!! $content !!}
</main>
