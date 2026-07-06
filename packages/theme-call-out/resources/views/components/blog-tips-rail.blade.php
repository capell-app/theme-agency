{{--
    Renders nothing when the optional blog package is not installed or
    there are no items — decided by
    `Capell\ThemeStudio\CallOut\View\Components\BlogTipsRail`'s constructor,
    never by this Blade file.
--}}
@if ($available)
    <div class="rco-blog-rail">
        <h3>{{ $heading }}</h3>
        <ul class="rco-blog-rail-list">
            @foreach ($items as $item)
                <li>
                    <a href="{{ data_get($item, 'url', '#') }}">
                        {{ data_get($item, 'title', '') }}
                    </a>
                    <p>{{ data_get($item, 'summary', '') }}</p>
                </li>
            @endforeach
        </ul>
    </div>
@endif
