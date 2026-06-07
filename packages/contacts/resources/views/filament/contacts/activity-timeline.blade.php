@php
    use Capell\Contacts\Models\ContactActivity;
    use Illuminate\Support\Collection;

    /** @var array{activities?: Collection<int, ContactActivity>}|null $state */
    $activities = $state['activities'] ?? collect();
@endphp

@if ($activities->isEmpty())
    <p class="text-sm text-gray-500">
        {{ __('capell-contacts::generic.placeholders.no_activity') }}
    </p>
@else
    <ol class="space-y-4">
        @foreach ($activities as $activity)
            <li
                class="rounded-md border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900"
            >
                <div
                    class="flex flex-wrap items-center gap-2 text-xs text-gray-500"
                >
                    <span class="font-medium text-gray-700 dark:text-gray-200">
                        {{ $activity->type?->getLabel() }}
                    </span>
                    @if ($activity->occurred_at)
                        <span>
                            {{ $activity->occurred_at->toDayDateTimeString() }}
                        </span>
                    @endif
                </div>

                @if ($activity->summary)
                    <p class="mt-2 text-sm text-gray-900 dark:text-gray-100">
                        {{ $activity->summary }}
                    </p>
                @endif
            </li>
        @endforeach
    </ol>
@endif
