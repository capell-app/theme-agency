<section class="theme-section theme-section-instructors bg-white">
    <div class="mx-auto max-w-5xl px-6 py-14">
        @isset($heading)
            <div class="grid gap-5 md:grid-cols-[0.7fr_1fr] md:items-end">
                <div>
                    <p
                        class="text-xs font-black tracking-[0.18em] text-[#14b8a6] uppercase"
                    >
                        {{ __('capell-theme-education::generic.instructors_label') }}
                    </p>
                    <h2
                        class="mt-4 text-4xl font-black tracking-tight text-[#0f172a]"
                    >
                        {{ $heading }}
                    </h2>
                </div>
                <p class="max-w-2xl text-lg text-slate-600 md:justify-self-end">
                    {{ __('capell-theme-education::generic.instructors_summary') }}
                </p>
            </div>
        @endisset

        <div class="mt-10 grid gap-4 md:grid-cols-3">
            @foreach (['Programme lead', 'Cohort mentor', 'Assessment coach'] as $role)
                <article class="border border-[#c7d2fe] bg-[#f8fbff] p-5">
                    <div
                        class="mb-5 flex aspect-[4/2.4] items-end bg-white p-4"
                        aria-hidden="true"
                    >
                        <div class="grid w-full grid-cols-[3rem_1fr] gap-3">
                            <span class="h-12 bg-[#4338ca]"></span>
                            <span class="space-y-2 pt-1">
                                <span
                                    class="block h-3 w-20 bg-[#14b8a6]/40"
                                ></span>
                                <span
                                    class="block h-3 w-28 bg-[#93c5fd]"
                                ></span>
                                <span
                                    class="block h-3 w-16 bg-[#e0e7ff]"
                                ></span>
                            </span>
                        </div>
                    </div>
                    <p
                        class="text-xs font-black tracking-[0.18em] text-[#4338ca] uppercase"
                    >
                        {{ $role }}
                    </p>
                    <h3 class="mt-3 text-lg font-black text-[#0f172a]">
                        {{ __('capell-theme-education::generic.instructor_card_title') }}
                    </h3>
                    <p class="mt-3 text-sm leading-6 text-slate-600">
                        {{ __('capell-theme-education::generic.instructor_card_summary') }}
                    </p>
                </article>
            @endforeach
        </div>
    </div>
</section>
