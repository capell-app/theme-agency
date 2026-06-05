@php
    $state = $getState();
    $clickRows = collect(is_array($state) ? ($state['clickRows'] ?? []) : []);
@endphp

@if ($clickRows->isEmpty())
    <p class="text-sm text-gray-500 dark:text-gray-400">
        {{ __('capell-email-studio::mail_tracker.no_click_rows') }}
    </p>
@else
    <div
        class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700"
    >
        <table
            class="w-full divide-y divide-gray-200 text-sm dark:divide-gray-700"
        >
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th
                        scope="col"
                        class="px-3 py-2 text-left font-medium text-gray-600 dark:text-gray-300"
                    >
                        {{ __('capell-email-studio::mail_tracker.fields.url') }}
                    </th>
                    <th
                        scope="col"
                        class="px-3 py-2 text-left font-medium text-gray-600 dark:text-gray-300"
                    >
                        {{ __('capell-email-studio::mail_tracker.fields.clicks') }}
                    </th>
                    <th
                        scope="col"
                        class="px-3 py-2 text-left font-medium text-gray-600 dark:text-gray-300"
                    >
                        {{ __('capell-email-studio::mail_tracker.fields.updated_at') }}
                    </th>
                </tr>
            </thead>
            <tbody
                class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900"
            >
                @foreach ($clickRows as $clickRow)
                    <tr>
                        <td
                            class="max-w-xl px-3 py-2 break-all text-gray-900 dark:text-gray-100"
                        >
                            {{ $clickRow->url }}
                        </td>
                        <td class="px-3 py-2 text-gray-700 dark:text-gray-300">
                            {{ $clickRow->clicks }}
                        </td>
                        <td class="px-3 py-2 text-gray-700 dark:text-gray-300">
                            {{ $clickRow->updated_at }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
