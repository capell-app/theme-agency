<div class="capell-authoring-editor p-5 sm:p-6">
    <form wire:submit="save">
        {{ $this->form }}

        <div
            class="capell-authoring-editor__actions sticky bottom-0 -mx-5 mt-6 -mb-5 flex items-center justify-between gap-3 border-t border-gray-200 bg-white/95 px-5 py-4 backdrop-blur sm:-mx-6 sm:-mb-6 sm:px-6 dark:border-gray-700 dark:bg-gray-900/95"
            data-capell-authoring-save-toolbar
        >
            <div class="text-sm text-gray-600 dark:text-gray-300">
                @if ($savedStatus === 'pending_approval')
                    {{ __('capell-frontend-authoring::authoring.approval_notice') }}
                @elseif ($savedStatus === 'published')
                    {{ __('capell-frontend-authoring::authoring.saved') }}
                @else
                    {{ __('capell-frontend-authoring::authoring.inline_editor') }}
                @endif
            </div>

            <x-filament::button
                type="submit"
                wire:loading.attr="disabled"
            >
                <span
                    wire:loading.remove
                    wire:target="save"
                >
                    {{ __('capell-frontend-authoring::authoring.save') }}
                </span>
                <span
                    wire:loading
                    wire:target="save"
                >
                    {{ __('capell-frontend-authoring::authoring.saving') }}
                </span>
            </x-filament::button>
        </div>
    </form>
</div>
