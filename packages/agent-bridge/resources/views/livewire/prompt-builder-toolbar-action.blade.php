<div>
    <button
        type="button"
        wire:click="openBuilder"
        class="hover:text-primary-600 focus:text-primary-600 dark:hover:text-primary-400 dark:focus:text-primary-400 flex h-8 flex-shrink-0 items-center gap-1.5 rounded-md px-2 text-sm font-medium text-gray-600 transition-colors focus:outline-none dark:text-gray-300"
        title="{{ __('capell-agent-bridge::admin.prompt_builder_tooltip') }}"
        aria-label="{{ __('capell-agent-bridge::admin.prompt_builder_tooltip') }}"
    >
        <svg
            class="h-5 w-5 flex-shrink-0"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.5"
            aria-hidden="true"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M7.5 8.25h9M7.5 12h5.25M21 12c0 4.142-4.03 7.5-9 7.5a10.4 10.4 0 0 1-3.226-.504L3 20.25l1.816-4.238C3.667 14.836 3 13.455 3 12c0-4.142 4.03-7.5 9-7.5s9 3.358 9 7.5Z"
            />
        </svg>
        <span class="whitespace-nowrap">
            {{ __('capell-agent-bridge::admin.prompt_builder_tool') }}
        </span>
    </button>

    @if ($isOpen)
        <div
            id="agent-bridge-prompt-builder"
            x-data
            x-init="$nextTick(() => $refs.dialog.focus())"
            x-trap.noscroll="true"
            x-on:keydown.escape.window="$wire.closeBuilder()"
            class="fixed inset-0 z-50"
            role="dialog"
            aria-modal="true"
            aria-labelledby="agent-bridge-prompt-builder-title"
        >
            <div
                class="absolute inset-0 bg-gray-950/35"
                wire:click="closeBuilder"
            ></div>
            <section
                x-ref="dialog"
                tabindex="-1"
                class="absolute inset-y-0 right-0 flex w-full max-w-5xl flex-col overflow-y-auto bg-white p-6 shadow-xl focus:outline-none dark:bg-gray-900"
            >
                <div class="mb-5 flex items-center justify-between gap-4">
                    <h2
                        id="agent-bridge-prompt-builder-title"
                        class="text-lg font-semibold text-gray-950 dark:text-white"
                    >
                        {{ __('capell-agent-bridge::admin.prompt_builder_title') }}
                    </h2>
                    <button
                        type="button"
                        wire:click="closeBuilder"
                        class="rounded-md px-2 py-1 text-sm font-semibold text-gray-500 hover:text-gray-900 focus:outline-none dark:text-gray-400 dark:hover:text-white"
                        aria-label="{{ __('capell-agent-bridge::admin.close') }}"
                    >
                        {{ __('capell-agent-bridge::admin.close') }}
                    </button>
                </div>

                <div class="space-y-5">
                    <div class="flex flex-wrap gap-2">
                        @foreach ([
                                      'inspect_page_readiness' => __('capell-agent-bridge::admin.starter_inspect_readiness'),
                                      'create_disabled_draft_page' => __('capell-agent-bridge::admin.starter_create_disabled_draft'),
                                      'update_draft_content' => __('capell-agent-bridge::admin.starter_update_draft'),
                                      'clear_cache' => __('capell-agent-bridge::admin.starter_clear_cache'),
                                      'recommend_packages' => __('capell-agent-bridge::admin.starter_recommend_packages'),
                                  ] as $starter => $label)
                            <button
                                type="button"
                                wire:click="applyStarter('{{ $starter }}')"
                                class="rounded-md border border-gray-300 px-2.5 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800"
                            >
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>

                    <div class="grid gap-4 md:grid-cols-3">
                        <label class="space-y-1 md:col-span-3">
                            <span
                                class="text-sm font-medium text-gray-700 dark:text-gray-200"
                            >
                                {{ __('capell-agent-bridge::admin.goal') }}
                            </span>
                            <input
                                type="text"
                                wire:model.live.debounce.300ms="data.goal"
                                placeholder="{{ __('capell-agent-bridge::admin.goal_placeholder') }}"
                                class="focus:border-primary-500 focus:ring-primary-500 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                            />
                            <span
                                class="text-xs text-gray-500 dark:text-gray-400"
                            >
                                {{ __('capell-agent-bridge::admin.goal_help') }}
                            </span>
                        </label>

                        <label class="space-y-1">
                            <span
                                class="text-sm font-medium text-gray-700 dark:text-gray-200"
                            >
                                {{ __('capell-agent-bridge::admin.area') }}
                            </span>
                            <select
                                wire:model.live="data.area"
                                class="focus:border-primary-500 focus:ring-primary-500 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                            >
                                @foreach ($this->areaOptions() as $value => $label)
                                    <option value="{{ $value }}">
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <label class="space-y-1">
                            <span
                                class="text-sm font-medium text-gray-700 dark:text-gray-200"
                            >
                                {{ __('capell-agent-bridge::admin.operation') }}
                            </span>
                            <select
                                wire:model.live="data.operation"
                                class="focus:border-primary-500 focus:ring-primary-500 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                            >
                                @foreach ($this->operationOptions() as $value => $label)
                                    <option value="{{ $value }}">
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <label class="space-y-1">
                            <span
                                class="text-sm font-medium text-gray-700 dark:text-gray-200"
                            >
                                {{ __('capell-agent-bridge::admin.safety') }}
                            </span>
                            <select
                                wire:model.live="data.safety"
                                class="focus:border-primary-500 focus:ring-primary-500 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                            >
                                @foreach ($this->safetyOptions() as $value => $label)
                                    <option value="{{ $value }}">
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        @foreach ([
                                      'target' => ['label' => __('capell-agent-bridge::admin.target'), 'help' => __('capell-agent-bridge::admin.target_help'), 'placeholder' => __('capell-agent-bridge::admin.target_placeholder')],
                                      'constraints' => ['label' => __('capell-agent-bridge::admin.constraints'), 'help' => __('capell-agent-bridge::admin.constraints_help'), 'placeholder' => __('capell-agent-bridge::admin.constraints_placeholder')],
                                      'success_criteria' => ['label' => __('capell-agent-bridge::admin.success_criteria'), 'help' => __('capell-agent-bridge::admin.success_criteria_help'), 'placeholder' => __('capell-agent-bridge::admin.success_criteria_placeholder')],
                                  ] as $field => $copy)
                            <label class="space-y-1 md:col-span-3">
                                <span
                                    class="text-sm font-medium text-gray-700 dark:text-gray-200"
                                >
                                    {{ $copy['label'] }}
                                </span>
                                <textarea
                                    wire:model.live.debounce.300ms="data.{{ $field }}"
                                    rows="2"
                                    placeholder="{{ $copy['placeholder'] }}"
                                    class="focus:border-primary-500 focus:ring-primary-500 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                                ></textarea>
                                <span
                                    class="text-xs text-gray-500 dark:text-gray-400"
                                >
                                    {{ $copy['help'] }}
                                </span>
                            </label>
                        @endforeach
                    </div>

                    <div class="grid gap-4 md:grid-cols-3">
                        <label class="space-y-1">
                            <span
                                class="text-sm font-medium text-gray-700 dark:text-gray-200"
                            >
                                {{ __('capell-agent-bridge::admin.saved_prompt') }}
                            </span>
                            <select
                                wire:model.live="selectedSavedPromptId"
                                class="focus:border-primary-500 focus:ring-primary-500 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                            >
                                <option value="">
                                    {{ __('capell-agent-bridge::admin.saved_prompt_placeholder') }}
                                </option>
                                @foreach ($this->savedPromptOptions() as $value => $label)
                                    <option value="{{ $value }}">
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <label class="space-y-1">
                            <span
                                class="text-sm font-medium text-gray-700 dark:text-gray-200"
                            >
                                {{ __('capell-agent-bridge::admin.saved_prompt_name') }}
                            </span>
                            <input
                                type="text"
                                wire:model="templateName"
                                class="focus:border-primary-500 focus:ring-primary-500 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                            />
                        </label>

                        <label class="space-y-1">
                            <span
                                class="text-sm font-medium text-gray-700 dark:text-gray-200"
                            >
                                {{ __('capell-agent-bridge::admin.saved_prompt_description') }}
                            </span>
                            <input
                                type="text"
                                wire:model="templateDescription"
                                class="focus:border-primary-500 focus:ring-primary-500 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                            />
                        </label>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <button
                            type="button"
                            wire:click="buildPrompt"
                            class="bg-primary-600 hover:bg-primary-500 rounded-md px-3 py-2 text-sm font-semibold text-white"
                        >
                            {{ __('capell-agent-bridge::admin.build_prompt') }}
                        </button>
                        <button
                            type="button"
                            x-on:click="
                                $wire
                                    .refreshPromptForCopy()
                                    .then(() =>
                                        navigator.clipboard.writeText(
                                            $wire.preparedPrompt,
                                        ),
                                    )
                            "
                            class="rounded-md border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800"
                        >
                            {{ __('capell-agent-bridge::admin.copy_prompt') }}
                        </button>
                        <button
                            type="button"
                            wire:click="savePrompt"
                            class="rounded-md border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800"
                        >
                            {{ __('capell-agent-bridge::admin.save_prompt') }}
                        </button>
                        <button
                            type="button"
                            wire:click="loadSavedPrompt"
                            class="rounded-md border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800"
                        >
                            {{ __('capell-agent-bridge::admin.load_saved') }}
                        </button>
                        <button
                            type="button"
                            wire:click="deleteSavedPrompt"
                            class="border-danger-300 text-danger-700 hover:bg-danger-50 dark:border-danger-700 dark:text-danger-300 dark:hover:bg-danger-950 rounded-md border px-3 py-2 text-sm font-semibold"
                        >
                            {{ __('capell-agent-bridge::admin.delete_saved') }}
                        </button>
                        <a
                            href="{{ $deepLinkUrl }}"
                            class="rounded-md border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800"
                        >
                            {{ __('capell-agent-bridge::admin.open_full_page') }}
                        </a>
                    </div>

                    <label class="space-y-1">
                        <span
                            class="text-sm font-medium text-gray-700 dark:text-gray-200"
                        >
                            {{ __('capell-agent-bridge::admin.prepared_prompt') }}
                        </span>
                        <textarea
                            readonly
                            rows="10"
                            class="block w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 font-mono text-sm text-gray-800 shadow-sm dark:border-gray-700 dark:bg-gray-950 dark:text-gray-200"
                            >{{ $preparedPrompt }}</textarea
                        >
                    </label>
                </div>
            </section>
        </div>
    @endif
</div>
