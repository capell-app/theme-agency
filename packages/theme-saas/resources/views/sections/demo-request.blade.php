@php
    $items ??= $section->items ?? [];
    $heading ??= $section->heading ?? __('capell-theme-saas::generic.demo_request_label');
    $summary ??= $section->summary ?? null;
    $formHandle = $section->form_handle ?? $section->handle ?? null;
    $formAction = $section->form_action ?? $section->url ?? '#';
@endphp

<section
    class="theme-section theme-section-demo-request bg-slate-950 text-white"
>
    <div class="mx-auto max-w-6xl px-6 py-16">
        <div class="grid gap-8 md:grid-cols-[0.8fr_1.2fr] md:items-center">
            <div>
                <p
                    class="text-xs font-black tracking-[0.16em] text-cyan-200 uppercase"
                >
                    {{ __('capell-theme-saas::generic.demo_request_label') }}
                </p>
                <h2 class="mt-3 text-4xl font-black">{{ $heading }}</h2>
                @if ($summary)
                    <p class="mt-4 text-base leading-8 text-slate-300">
                        {{ $summary }}
                    </p>
                @endif

                <p class="mt-4 text-sm font-bold text-slate-300">
                    {{ $formBuilderAvailable ?? false ? __('capell-theme-saas::generic.demo_request_connected') : __('capell-theme-saas::generic.demo_request_static') }}
                </p>
            </div>

            <div class="grid gap-4">
                @forelse ($items as $item)
                    <article
                        class="grid gap-2 rounded-xl border border-white/10 bg-white/[0.04] p-5"
                    >
                        <p class="text-xs font-black text-cyan-200 uppercase">
                            {{ $item['type'] ?? __('capell-theme-saas::generic.conversion_command_label') }}
                        </p>
                        <h3 class="text-xl font-black">
                            {{ $item['title'] ?? __('capell-theme-saas::generic.demo_request_label') }}
                        </h3>
                        <p class="text-sm leading-6 text-slate-300">
                            {{ $item['summary'] ?? __('capell-theme-saas::generic.demo_request_ready') }}
                        </p>
                    </article>
                @empty
                    <article
                        class="rounded-xl border border-white/10 bg-white/[0.04] p-5"
                    >
                        <h3 class="text-lg font-black">
                            {{ __('capell-theme-saas::generic.demo_request_ready') }}
                        </h3>
                    </article>
                @endforelse

                <div
                    class="rounded-2xl border border-white/10 bg-white p-5 text-slate-950 shadow-2xl shadow-black/20"
                >
                    @if (($formBuilderAvailable ?? false) && ($formHandle !== null && $formHandle !== ''))
                        @livewire('capell-form-builder::form', [
                            'handle' => $formHandle,
                            'instanceId' => 'theme-saas-demo-request',
                        ])
                    @else
                        <form
                            action="{{ $formAction }}"
                            method="get"
                            class="grid gap-4"
                        >
                            <div class="grid gap-1">
                                <label
                                    for="theme-saas-demo-name"
                                    class="text-sm font-black text-slate-800"
                                >
                                    {{ __('capell-theme-saas::generic.demo_request_name_label') }}
                                </label>
                                <input
                                    id="theme-saas-demo-name"
                                    name="name"
                                    type="text"
                                    autocomplete="name"
                                    class="min-h-12 rounded-lg border border-slate-300 px-3 text-sm"
                                />
                            </div>

                            <div class="grid gap-1">
                                <label
                                    for="theme-saas-demo-email"
                                    class="text-sm font-black text-slate-800"
                                >
                                    {{ __('capell-theme-saas::generic.demo_request_email_label') }}
                                </label>
                                <input
                                    id="theme-saas-demo-email"
                                    name="email"
                                    type="email"
                                    autocomplete="email"
                                    class="min-h-12 rounded-lg border border-slate-300 px-3 text-sm"
                                />
                            </div>

                            <div class="grid gap-1">
                                <label
                                    for="theme-saas-demo-company"
                                    class="text-sm font-black text-slate-800"
                                >
                                    {{ __('capell-theme-saas::generic.demo_request_company_label') }}
                                </label>
                                <input
                                    id="theme-saas-demo-company"
                                    name="company"
                                    type="text"
                                    autocomplete="organization"
                                    class="min-h-12 rounded-lg border border-slate-300 px-3 text-sm"
                                />
                            </div>

                            <button
                                type="submit"
                                class="inline-flex min-h-12 items-center justify-center rounded-lg bg-slate-950 px-4 text-sm font-black text-white"
                            >
                                {{ __('capell-theme-saas::generic.demo_request_submit_label') }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
