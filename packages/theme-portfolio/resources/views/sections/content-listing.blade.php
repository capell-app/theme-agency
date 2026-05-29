@php
    $items = $section->items ?? [];
@endphp

<section class="theme-section theme-section-content-listing bg-[#f8fafc]">
    <div class="mx-auto max-w-6xl px-6 py-16 lg:py-20">
        <div class="grid gap-5 md:grid-cols-[0.72fr_1fr] md:items-end">
            <div>
                <p
                    class="text-xs font-black tracking-[0.18em] text-[#1f3173] uppercase"
                >
                    {{ __('capell-theme-portfolio::generic.work_index_label') }}
                </p>
                <h2
                    class="mt-4 text-4xl font-black tracking-tight text-[#0f172a]"
                >
                    {{ $section->heading }}
                </h2>
            </div>

            @if ($section->summary ?? null)
                <p class="max-w-2xl text-base leading-7 text-slate-600">
                    {{ $section->summary }}
                </p>
            @endif
        </div>

        @if ($items !== [])
            <div class="mt-10 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($items as $item)
                    @php
                        $image = $item['image'] ?? $item['imageUrl'] ?? $item['mediaUrl'] ?? null;
                    @endphp

                    <a
                        href="{{ $item['url'] ?? '#' }}"
                        class="{{ $loop->first ? 'lg:col-span-2' : '' }} group flex min-h-full flex-col overflow-hidden border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:border-[#1f3173] hover:shadow-xl"
                    >
                        @if (is_string($image) && $image !== '')
                            <img
                                src="{{ $image }}"
                                alt=""
                                class="{{ $loop->first ? 'aspect-[16/8]' : 'aspect-[4/2.6]' }} w-full object-cover transition duration-300 group-hover:scale-[1.025]"
                            />
                        @else
                            <span
                                class="{{ $loop->first ? 'aspect-[16/8]' : 'aspect-[4/2.6]' }} flex items-end bg-[#070b1a] p-5"
                                aria-hidden="true"
                            >
                                <span class="block w-full">
                                    <span
                                        class="block h-2 w-24 rounded-full bg-[#fb923c]"
                                    ></span>
                                    <span class="mt-10 grid grid-cols-3 gap-2">
                                        <span class="h-9 bg-white/20"></span>
                                        <span class="h-9 bg-[#1f3173]"></span>
                                        <span class="h-9 bg-white/10"></span>
                                    </span>
                                </span>
                            </span>
                        @endif

                        <span class="flex flex-1 flex-col p-5 sm:p-6">
                            <span
                                class="flex items-center justify-between gap-3 text-xs font-black tracking-[0.16em] text-[#1f3173] uppercase"
                            >
                                <span>
                                    {{ $item['type'] ?? __('capell-theme-portfolio::generic.project_label') }}
                                </span>
                                <span class="text-slate-400">
                                    {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                </span>
                            </span>

                            <span
                                class="mt-4 block text-xl font-black text-[#0f172a]"
                            >
                                {{ $item['title'] ?? $item['name'] ?? '' }}
                            </span>

                            @if (! empty($item['summary']) || ! empty($item['description']))
                                <span
                                    class="mt-3 block text-sm leading-6 text-slate-600"
                                >
                                    {{ $item['summary'] ?? $item['description'] }}
                                </span>
                            @endif

                            <span
                                class="mt-6 inline-flex w-fit rounded-full bg-[#0f172a] px-3 py-1.5 text-xs font-black text-white"
                            >
                                {{ __('capell-theme-portfolio::generic.view_case_label') }}
                            </span>
                        </span>
                    </a>
                @endforeach
            </div>
        @else
            <div
                class="mt-10 border border-dashed border-slate-300 bg-white p-8 text-slate-600"
            >
                {{ __('capell-theme-portfolio::generic.empty_work_index') }}
            </div>
        @endif
    </div>
</section>
