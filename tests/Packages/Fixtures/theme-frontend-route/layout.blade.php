<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>{{ $pageRecord?->name ?? 'Theme frontend route' }}</title>
    </head>
    <body>
        {!! $slot !!}
    </body>
</html>
