<x-filament-widgets::widget class="capell-workspace-activity">
    <x-filament::section
        :heading="__('capell-publishing-studio::workflow.widgets.workspace_activity.heading')"
    >
        @php
            $data = $this->data;
            $hasActivity = $data->pendingApprovalsCount > 0 || $data->stuckCount > 0 || $data->recentMerges->count() > 0;
        @endphp

        <div class="space-y-4">
            {{-- Summary tiles --}}
            <div class="grid grid-cols-2 gap-3">
                <div class="rounded-lg bg-amber-50 p-3">
                    <div class="text-2xl font-bold text-amber-700">
                        {{ $data->pendingApprovalsCount }}
                    </div>
                    <div class="mt-0.5 text-xs text-amber-600">
                        {{ __('capell-publishing-studio::workflow.widgets.workspace_activity.pending_approval') }}
                    </div>
                </div>
                <div class="rounded-lg bg-red-50 p-3">
                    <div class="text-2xl font-bold text-red-700">
                        {{ $data->stuckCount }}
                    </div>
                    <div class="mt-0.5 text-xs text-red-600">
                        {{ __('capell-publishing-studio::workflow.widgets.workspace_activity.stuck_open') }}
                    </div>
                </div>
            </div>

            {{-- Recent merges --}}
            @if ($data->recentMerges->count() === 0)
                @if (! $hasActivity)
                    <div class="text-sm text-gray-500">
                        {{ __('capell-publishing-studio::workflow.widgets.workspace_activity.no_recent_activity') }}
                    </div>
                @else
                    <div class="text-sm text-gray-500">
                        {{ __('capell-publishing-studio::workflow.widgets.workspace_activity.no_recent_merges') }}
                    </div>
                @endif
            @else
                <div>
                    <div
                        class="mb-1.5 text-xs font-semibold tracking-wide text-gray-400 uppercase"
                    >
                        {{ __('capell-publishing-studio::workflow.widgets.workspace_activity.recent_merges') }}
                    </div>
                    <div class="space-y-2">
                        @foreach ($data->recentMerges as $merge)
                            <div
                                class="flex items-center justify-between gap-4 text-sm"
                            >
                                <div class="min-w-0 flex-1">
                                    <span class="truncate font-medium">
                                        {{ $merge->name }}
                                    </span>
                                    <div class="mt-0.5 text-xs text-gray-500">
                                        {{ $merge->actorName }}
                                        &middot;
                                        {{ $merge->pageCount }}
                                        {{ Str::plural('page', $merge->pageCount) }}
                                        &middot;
                                        {{ $merge->durationOpenHours }}h open
                                    </div>
                                </div>
                                <time
                                    datetime="{{ $merge->publishedAt }}"
                                    class="shrink-0 text-xs text-gray-400"
                                >
                                    {{ Carbon::parse($merge->publishedAt)->diffForHumans() }}
                                </time>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
