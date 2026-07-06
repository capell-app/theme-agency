<div class="capell-release-workspace-summary-panel">
    @if ($visible)
        <section class="space-y-4">
            <div>
                <h3 class="text-sm font-semibold text-gray-950 dark:text-white">
                    {{ __('capell-admin::workspace.release.summary_title') }}
                </h3>
                <p class="text-sm font-medium text-gray-950 dark:text-white">
                    {{ trans_choice('capell-admin::workspace.release.item_count', $summary->itemCount, ['count' => $summary->itemCount]) }}
                </p>

                @if ($summary->itemCount === 0)
                    <p class="text-sm text-gray-500">
                        {{ __('capell-admin::workspace.release.empty_summary') }}
                    </p>
                @else
                    <ul
                        class="mt-2 divide-y divide-gray-200 dark:divide-white/10"
                    >
                        @foreach ($summary->items as $item)
                            <li class="py-2 text-sm">
                                <span
                                    class="font-medium text-gray-950 dark:text-white"
                                >
                                    {{ $item->label }}
                                </span>
                                <span class="block text-gray-500">
                                    <span>
                                        {{ __('capell-admin::workspace.release.item_source') }}: {{ $item->source }}
                                    </span>
                                    <span aria-hidden="true">&middot;</span>
                                    <span>
                                        {{ __('capell-admin::workspace.release.item_change_type') }}: {{ __("capell-admin::workspace.release.change_type.{$item->changeType}") }}
                                    </span>
                                    <span aria-hidden="true">&middot;</span>
                                    <span>
                                        {{ __('capell-admin::workspace.release.item_status') }}: {{ __("capell-admin::workspace.release.item_statuses.{$item->status}") }}
                                    </span>
                                </span>
                            </li>
                        @endforeach
                    </ul>

                    @if ($remainingItemCount > 0)
                        <p class="mt-2 text-sm text-gray-500">
                            {{ __('capell-admin::workspace.release.remaining_items', ['count' => $remainingItemCount]) }}
                        </p>
                        @if ($compareUrl !== null)
                            <a
                                class="text-primary-600 dark:text-primary-400 mt-1 inline-flex text-sm font-medium hover:underline"
                                href="{{ $compareUrl }}"
                            >
                                {{ __('capell-admin::workspace.release.view_all_items') }}
                            </a>
                        @endif
                    @endif
                @endif
            </div>

            <div>
                <h3 class="text-sm font-semibold text-gray-950 dark:text-white">
                    {{ __('capell-admin::workspace.release.readiness_title') }}
                </h3>

                <p class="text-sm text-gray-500">
                    {{ $readiness->wouldPublish ? __('capell-admin::workspace.release.ready') : __('capell-admin::workspace.release.blocked') }} {{ trans_choice('capell-admin::workspace.release.blocking_count', $readiness->blockingIssueCount, ['count' => $readiness->blockingIssueCount]) }}
                </p>

                @if ($readiness->blockingIssueCount > 0)
                    <ul class="text-danger-600 mt-2 list-disc pl-5 text-sm">
                        @foreach ($readiness->blockingIssues as $issue)
                            <li>{{ $issue }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </section>
    @endif
</div>
