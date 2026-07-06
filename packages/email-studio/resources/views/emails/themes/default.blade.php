@php
    use Illuminate\Support\Facades\Storage;

    $logoUrl = $theme->logo_url;

    if ((! is_string($logoUrl) || $logoUrl === '') && is_string($theme->logo_path) && $theme->logo_path !== '') {
        $logoUrl = Storage::url($theme->logo_path);
    }
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    />
    <title>{{ $theme->name }}</title>
</head>
<body
    style="
            margin: 0;
            padding: 0;
            background: {{ $colors['body_bg_color'] }};
            color: {{ $colors['body_color'] }};
            font-family: Arial, Helvetica, sans-serif;
        "
>
    @if ($previewText !== null && $previewText !== '')
        <div
            style="
                display: none;
                max-height: 0;
                overflow: hidden;
                opacity: 0;
                color: transparent;
            "
        >
            {{ $previewText }}
        </div>
    @endif

    <table
        role="presentation"
        width="100%"
        cellspacing="0"
        cellpadding="0"
        style="
                border-collapse: collapse;
                background: {{ $colors['body_bg_color'] }};
                padding: 24px 0;
            "
    >
        <tr>
            <td
                align="center"
                style="padding: 0 16px"
            >
                <table
                    role="presentation"
                    width="100%"
                    cellspacing="0"
                    cellpadding="0"
                    style="
                            max-width: 640px;
                            border-collapse: collapse;
                            background: {{ $colors['content_bg_color'] }};
                            border: 1px solid {{ $colors['border_color'] }};
                        "
                >
                    <tr>
                        <td
                            style="
                                    padding: 24px;
                                    background: {{ $colors['header_bg_color'] }};
                                "
                        >
                            @if (is_string($logoUrl) && $logoUrl !== '')
                                <img
                                    src="{{ $logoUrl }}"
                                    alt="{{ $theme->name }}"
                                    style="
                                        display: block;
                                        max-width: 180px;
                                        height: auto;
                                    "
                                />
                            @else
                                <strong
                                    style="
                                        display: block;
                                        color: #ffffff;
                                        font-size: 18px;
                                        line-height: 24px;
                                    "
                                >
                                    {{ $theme->name }}
                                </strong>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td
                            style="
                                    padding: 32px 24px;
                                    color: {{ $colors['body_color'] }};
                                    font-size: 16px;
                                    line-height: 24px;
                                "
                        >
                            {!! $html !!}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
