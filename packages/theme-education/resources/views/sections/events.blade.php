<section class="theme-section theme-section-events">
    @isset($heading)
        <div class="mx-auto max-w-5xl px-6 py-14">
            <h2 class="text-4xl font-black tracking-tight text-[#1d4ed8]">
                {{ $heading }}
            </h2>
        </div>
    @endisset

    <div
        class="theme-content-pathway mx-auto mt-6 grid max-w-5xl gap-4 px-6 pb-14 md:grid-cols-3"
    >
        <article class="rounded-xl border border-indigo-200 bg-white p-5">
            <p class="text-xs font-black tracking-widest text-[#1d4ed8]">
                {{ $eventsAvailable ?? false ? __('capell-theme-education::generic.events_connected') : __('capell-theme-education::generic.events_static') }}
            </p>
            <h3 class="mt-2 text-lg font-black">Live Workshops</h3>
            <p class="mt-2 text-sm text-stone-600">
                Instructor-led sessions for cohorts, teams, and launch support.
            </p>
        </article>
        <article class="rounded-xl border border-indigo-200 bg-white p-5">
            <p class="text-xs font-black tracking-widest text-[#1d4ed8]">
                Q4 Calendar
            </p>
            <h3 class="mt-2 text-lg font-black">Masterclasses</h3>
            <p class="mt-2 text-sm text-stone-600">
                Focused deep-dive sessions with reusable materials and
                recordings.
            </p>
        </article>
        <article class="rounded-xl border border-indigo-200 bg-white p-5">
            <p class="text-xs font-black tracking-widest text-[#1d4ed8]">
                Office Hours
            </p>
            <h3 class="mt-2 text-lg font-black">Mentor Access</h3>
            <p class="mt-2 text-sm text-stone-600">
                Fast feedback windows built for premium learners and teams.
            </p>
        </article>
    </div>
</section>
