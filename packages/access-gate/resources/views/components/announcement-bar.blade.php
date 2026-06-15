@php
    use Capell\AccessGate\Data\AnnouncementBarData;

    /** @var AnnouncementBarData $announcement */
@endphp

<div
    class="site-announcement"
    role="status"
>
    <p class="site-announcement__message">{{ $announcement->message }}</p>

    @if ($announcement->linkUrl !== null && $announcement->linkLabel !== null)
        <a
            class="site-announcement__link"
            href="{{ $announcement->linkUrl }}"
        >
            {{ $announcement->linkLabel }}
        </a>
    @endif
</div>
