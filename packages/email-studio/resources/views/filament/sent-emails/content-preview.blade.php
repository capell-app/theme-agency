@php
    $content = (string) $getState();
@endphp

<div class="space-y-3">
    <iframe
        title="{{ __('capell-email-studio::mail_tracker.preview_title') }}"
        sandbox=""
        class="min-h-[32rem] w-full rounded-lg border border-gray-200 bg-white dark:border-gray-700"
        srcdoc="{{ $content }}"
    ></iframe>
</div>
