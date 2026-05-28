<section class="theme-section theme-section-faq">
    @isset($heading)
        <div class="mx-auto max-w-5xl px-6 py-14">
            <h2 class="text-4xl font-black tracking-tight text-[#1d4ed8]">
                {{ $heading }}
            </h2>
        </div>
    @endisset

    <div class="mx-auto grid max-w-5xl gap-3 px-6 pb-14 md:grid-cols-2">
        <details
            class="group rounded-lg border border-indigo-200 bg-white p-4 open:ring-2 open:ring-indigo-200"
        >
            <summary class="cursor-pointer font-semibold text-[#1d4ed8]">
                Who is this curriculum for?
            </summary>
            <p class="mt-3 text-sm text-stone-600">
                Entrepreneurs, product teams, and operators building repeatable
                teaching or onboarding playbooks.
            </p>
        </details>
        <details
            class="group rounded-lg border border-indigo-200 bg-white p-4 open:ring-2 open:ring-indigo-200"
        >
            <summary class="cursor-pointer font-semibold text-[#1d4ed8]">
                Can I run this with my own content?
            </summary>
            <p class="mt-3 text-sm text-stone-600">
                Yes—content modules can be mapped directly and themed with your
                existing style guide.
            </p>
        </details>
    </div>
</section>
