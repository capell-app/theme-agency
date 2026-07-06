@php
    use Filament\Support\Enums\IconSize;
    use Filament\Support\Icons\Heroicon;

    $state = $this->state();
    $dateFormat = 'd/m/Y H:i';
@endphp

<div
    class="capell-publish-status-panel rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900"
    aria-labelledby="publish-status-panel-title"
>
    <div
        class="flex items-center gap-3 border-b border-gray-100 px-4 py-3 dark:border-gray-800"
    >
        @svg (Heroicon::OutlinedDocumentText->getIconForSize(IconSize::Small), 'h-4 w-4 text-gray-400', ['aria-hidden' => 'true'])
        <h3
            id="publish-status-panel-title"
            class="text-sm font-semibold text-gray-700 dark:text-gray-300"
        >
            {{ __('capell-admin::publish_panel.title') }}
        </h3>
    </div>

    <div class="space-y-3 px-4 py-3 text-sm">
        @include ('capell-admin::livewire.partials.publish-status-rows', [
            'dateFormat' => $dateFormat,
            'state' => $state,
        ])

        {{-- Preview URL --}}
        @if ($state->previewUrl !== null)
            <div class="pt-1">
                <a
                    class="text-primary-600 dark:text-primary-400 inline-flex items-center gap-1.5 text-xs hover:underline"
                    href="{{ $state->previewUrl }}"
                    rel="noopener"
                    target="_blank"
                >
                    @svg (Heroicon::OutlinedArrowTopRightOnSquare->getIconForSize(IconSize::Small), 'h-3.5 w-3.5', ['aria-hidden' => 'true'])
                    {{ __('capell-admin::publish_panel.preview') }}
                </a>
            </div>
        @endif
    </div>

    {{-- Extension slots injected by PublishPanelExtender implementations --}}
    @foreach ($this->extensions() as $extension)
        <div class="border-t border-gray-100 px-4 py-3 dark:border-gray-800">
            {!! $extension !!}
        </div>
    @endforeach
</div>
