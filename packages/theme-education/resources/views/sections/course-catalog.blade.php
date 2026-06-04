@php
    $courseCards = __('capell-theme-education::generic.course_catalog_cards');
    $courseCards = is_array($courseCards) ? $courseCards : [];
@endphp

<section class="theme-section theme-section-course-catalog">
    @isset($heading)
        <div class="mx-auto max-w-5xl px-6 py-14">
            <h2 class="text-4xl font-black tracking-tight text-[#1d4ed8]">
                {{ $heading }}
            </h2>
        </div>
    @endisset

    <div
        class="theme-carousel relative mx-auto mt-6 max-w-5xl px-6 pb-14"
        data-carousel="course-catalog"
    >
        <div
            class="flex snap-x snap-mandatory [scrollbar-width:none] gap-4 overflow-x-auto pr-6 pb-2 sm:grid sm:grid-cols-3 [&::-webkit-scrollbar]:hidden"
            data-carousel-track
        >
            @foreach ($courseCards as $courseCard)
                @php
                    $courseFormat = is_array($courseCard) && is_scalar($courseCard['format'] ?? null) ? (string) $courseCard['format'] : '';
                    $courseTitle = is_array($courseCard) && is_scalar($courseCard['title'] ?? null) ? (string) $courseCard['title'] : '';
                    $courseSummary = is_array($courseCard) && is_scalar($courseCard['summary'] ?? null) ? (string) $courseCard['summary'] : '';
                @endphp

                @if ($courseFormat !== '' && $courseTitle !== '' && $courseSummary !== '')
                    <article
                        class="min-w-[250px] snap-start rounded-xl border border-indigo-200 bg-white p-4"
                    >
                        <p
                            class="text-xs font-black tracking-widest text-[#1d4ed8]"
                        >
                            {{ $courseFormat }}
                        </p>
                        <h3 class="mt-2 text-lg font-black">
                            {{ $courseTitle }}
                        </h3>
                        <p class="mt-2 text-sm text-stone-600">
                            {{ $courseSummary }}
                        </p>
                    </article>
                @endif
            @endforeach
        </div>
    </div>
</section>
