<div class="space-y-4">
    @foreach ($this->entries as $entry)
        <div
            class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900"
        >
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div
                        class="text-sm font-medium text-gray-950 dark:text-white"
                    >
                        {{ $entry->title }}
                    </div>
                    @if ($entry->description)
                        <div
                            class="mt-1 text-sm text-gray-600 dark:text-gray-400"
                        >
                            {{ $entry->description }}
                        </div>
                    @endif

                    @if ($entry->actorName)
                        <div
                            class="mt-2 text-xs text-gray-500 dark:text-gray-500"
                        >
                            {{ $entry->actorName }}
                        </div>
                    @endif
                </div>
                <x-filament::badge :color="$entry->type->getColor()">
                    {{ $entry->type->getLabel() }}
                </x-filament::badge>
            </div>
            <div class="mt-3 text-xs text-gray-500 dark:text-gray-500">
                {{ $entry->occurredAt->toDayDateTimeString() }}
            </div>
        </div>
    @endforeach
</div>
