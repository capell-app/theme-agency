@php
    $breadcrumbs = $section->breadcrumbs ?? $breadcrumbs ?? __('capell-theme-knowledge::generic.doc_breadcrumbs');
    $breadcrumbs = is_array($breadcrumbs) ? $breadcrumbs : [];
    $sidebarItems = $section->sidebarItems ?? $sidebarItems ?? __('capell-theme-knowledge::generic.doc_sidebar_items');
    $sidebarItems = is_array($sidebarItems) ? $sidebarItems : [];
    $tocItems = $section->tocItems ?? $tocItems ?? __('capell-theme-knowledge::generic.doc_toc_items');
    $tocItems = is_array($tocItems) ? $tocItems : [];
    $title = $section->title ?? $section->heading ?? $title ?? __('capell-theme-knowledge::generic.doc_title');
    $summary = $section->summary ?? $summary ?? __('capell-theme-knowledge::generic.doc_summary');
    $category = $section->category ?? $category ?? __('capell-theme-knowledge::generic.doc_category');
    $version = $section->version ?? $version ?? null;
    $updatedAt = $section->updatedAt ?? $updatedAt ?? __('capell-theme-knowledge::generic.doc_updated_at');
    $readingTime = $section->readingTime ?? $readingTime ?? __('capell-theme-knowledge::generic.doc_reading_time');
    $contentHtml = $section->contentHtml ?? $contentHtml ?? null;
@endphp

<section class="theme-section theme-section-doc-article bg-[var(--site-theme-surface)]">
    <div
        class="mx-auto grid max-w-7xl gap-8 px-6 lg:grid-cols-[17rem_minmax(0,1fr)_15rem]"
    >
        <aside
            class="knowledge-doc-sidebar hidden lg:block"
            aria-label="{{ __('capell-theme-knowledge::generic.doc_sidebar_label') }}"
        >
            <div class="sticky top-8 border border-slate-200 bg-white p-4">
                <p
                    class="text-xs font-black tracking-[0.18em] text-[var(--site-theme-primary)] uppercase"
                >
                    {{ __('capell-theme-knowledge::generic.doc_sidebar_label') }}
                </p>
                <nav class="mt-4 space-y-1">
                    @foreach ($sidebarItems as $sidebarItem)
                        @php
                            $sidebarLabel = is_array($sidebarItem) && is_scalar($sidebarItem['label'] ?? $sidebarItem['title'] ?? null) ? (string) ($sidebarItem['label'] ?? $sidebarItem['title']) : '';
                            $sidebarUrl = is_array($sidebarItem) && is_scalar($sidebarItem['url'] ?? null) ? (string) $sidebarItem['url'] : '#';
                            $sidebarActive = is_array($sidebarItem) && (bool) ($sidebarItem['active'] ?? false);
                        @endphp

                        @if ($sidebarLabel !== '')
                            <a
                                href="{{ $sidebarUrl }}"
                                @class([
                                    'block border-l-2 px-3 py-2 text-sm font-bold',
                                    'border-[var(--site-theme-primary)] bg-[var(--site-theme-primary-panel)] text-[var(--site-theme-heading)]' => $sidebarActive,
                                    'border-transparent text-slate-600 hover:border-[var(--site-theme-primary-muted)] hover:bg-slate-50 hover:text-[var(--site-theme-heading)]' => ! $sidebarActive,
                                ])
                            >
                                {{ $sidebarLabel }}
                            </a>
                        @endif
                    @endforeach
                </nav>
            </div>
        </aside>

        <article
            class="knowledge-doc-article border border-slate-200 bg-white p-6 shadow-sm md:p-8 lg:p-10"
        >
            @if ($breadcrumbs !== [])
                <nav
                    class="mb-8"
                    aria-label="{{ __('capell-theme-knowledge::generic.doc_breadcrumb_label') }}"
                >
                    <ol
                        class="flex flex-wrap items-center gap-2 text-sm font-bold text-slate-500"
                    >
                        @foreach ($breadcrumbs as $breadcrumb)
                            @php
                                $breadcrumbLabel = is_array($breadcrumb) && is_scalar($breadcrumb['label'] ?? $breadcrumb['title'] ?? null) ? (string) ($breadcrumb['label'] ?? $breadcrumb['title']) : '';
                                $breadcrumbUrl = is_array($breadcrumb) && is_scalar($breadcrumb['url'] ?? null) ? (string) $breadcrumb['url'] : null;
                            @endphp

                            @if ($breadcrumbLabel !== '')
                                <li class="flex items-center gap-2">
                                    @if (! $loop->first)
                                        <span
                                            aria-hidden="true"
                                            class="text-slate-300"
                                        >
                                            /
                                        </span>
                                    @endif

                                    @if ($breadcrumbUrl !== null && ! $loop->last)
                                        <a
                                            href="{{ $breadcrumbUrl }}"
                                            class="hover:text-[var(--site-theme-primary)]"
                                        >
                                            {{ $breadcrumbLabel }}
                                        </a>
                                    @else
                                        <span class="text-slate-700">
                                            {{ $breadcrumbLabel }}
                                        </span>
                                    @endif
                                </li>
                            @endif
                        @endforeach
                    </ol>
                </nav>
            @endif

            <header class="max-w-3xl">
                <p
                    class="text-xs font-black tracking-[0.18em] text-[var(--site-theme-primary)] uppercase"
                >
                    {{ $category }}
                </p>
                <h2
                    class="mt-4 text-4xl leading-tight font-black text-[var(--site-theme-heading)] md:text-5xl"
                >
                    {{ $title }}
                </h2>
                @if ($summary !== null)
                    <p class="mt-5 text-lg leading-8 text-slate-600">
                        {{ $summary }}
                    </p>
                @endif

                <dl
                    class="mt-6 flex flex-wrap gap-4 text-sm font-bold text-slate-500"
                >
                    @if (is_scalar($version) && (string) $version !== '')
                        <div class="flex gap-2">
                            <dt>
                                {{ __('capell-theme-knowledge::generic.doc_version_label') }}
                            </dt>
                            <dd class="text-slate-700">{{ $version }}</dd>
                        </div>
                    @endif

                    <div class="flex gap-2">
                        <dt>
                            {{ __('capell-theme-knowledge::generic.doc_updated_label') }}
                        </dt>
                        <dd class="text-slate-700">{{ $updatedAt }}</dd>
                    </div>
                    <div class="flex gap-2">
                        <dt>
                            {{ __('capell-theme-knowledge::generic.doc_reading_label') }}
                        </dt>
                        <dd class="text-slate-700">{{ $readingTime }}</dd>
                    </div>
                </dl>
            </header>

            <div class="knowledge-doc-prose mt-10 max-w-3xl text-slate-700">
                @if (is_string($contentHtml) && $contentHtml !== '')
                    {!! $contentHtml !!}
                @else
                    <h3>
                        {{ __('capell-theme-knowledge::generic.doc_section_overview') }}
                    </h3>
                    <p>
                        {{ __('capell-theme-knowledge::generic.doc_section_overview_copy') }}
                    </p>
                    <h3>
                        {{ __('capell-theme-knowledge::generic.doc_section_next_steps') }}
                    </h3>
                    <p>
                        {{ __('capell-theme-knowledge::generic.doc_section_next_steps_copy') }}
                    </p>
                @endif
            </div>
        </article>

        <aside
            class="knowledge-doc-toc hidden xl:block"
            aria-label="{{ __('capell-theme-knowledge::generic.doc_toc_label') }}"
        >
            <div class="sticky top-8 border border-slate-200 bg-white p-4">
                <p
                    class="text-xs font-black tracking-[0.18em] text-[var(--site-theme-primary)] uppercase"
                >
                    {{ __('capell-theme-knowledge::generic.doc_toc_label') }}
                </p>
                <nav class="mt-4 space-y-3">
                    @foreach ($tocItems as $tocItem)
                        @php
                            $tocLabel = is_array($tocItem) && is_scalar($tocItem['label'] ?? $tocItem['title'] ?? null) ? (string) ($tocItem['label'] ?? $tocItem['title']) : '';
                            $tocUrl = is_array($tocItem) && is_scalar($tocItem['url'] ?? null) ? (string) $tocItem['url'] : '#';
                        @endphp

                        @if ($tocLabel !== '')
                            <a
                                href="{{ $tocUrl }}"
                                class="block border-l border-slate-200 pl-3 text-sm font-bold text-slate-600 hover:border-[var(--site-theme-primary)] hover:text-[var(--site-theme-heading)]"
                            >
                                {{ $tocLabel }}
                            </a>
                        @endif
                    @endforeach
                </nav>
            </div>
        </aside>
    </div>
</section>
