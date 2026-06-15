@php
    use Capell\EmailStudio\Actions\BuildEmailTemplatePreviewAction;

    $preview = BuildEmailTemplatePreviewAction::run($record);
    $rendered = $preview->rendered;
@endphp

<div class="space-y-4">
    @if ($preview->error !== null)
        <div
            class="border-danger-200 bg-danger-50 text-danger-700 rounded-md border p-3 text-sm"
        >
            {{ $preview->error }}
        </div>
    @else
        @if ($preview->missingVariables !== [])
            <div
                class="border-warning-200 bg-warning-50 text-warning-700 rounded-md border p-3 text-sm"
            >
                {{ __('capell-email-studio::templates.messages.missing_variables', ['variables' => implode(', ', $preview->missingVariables)]) }}
            </div>
        @endif

        <div class="space-y-1">
            <div
                class="text-xs font-medium tracking-wide text-gray-500 uppercase"
            >
                {{ __('capell-email-studio::templates.fields.subject') }}
            </div>
            <div class="text-sm font-semibold text-gray-950 dark:text-white">
                {{ $rendered?->subject }}
            </div>
        </div>

        @if ($rendered?->previewText !== null)
            <div class="space-y-1">
                <div
                    class="text-xs font-medium tracking-wide text-gray-500 uppercase"
                >
                    {{ __('capell-email-studio::templates.fields.preview_text') }}
                </div>
                <div class="text-sm text-gray-700 dark:text-gray-200">
                    {{ $rendered->previewText }}
                </div>
            </div>
        @endif

        <iframe
            title="{{ __('capell-email-studio::templates.actions.preview') }}"
            sandbox
            srcdoc="{{ e($rendered?->html ?? '') }}"
            style="
                width: 100%;
                min-height: 520px;
                border: 1px solid #d1d5db;
                border-radius: 8px;
                background: white;
            "
        ></iframe>
    @endif
</div>
