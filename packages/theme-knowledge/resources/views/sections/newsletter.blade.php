<section
    class="theme-section theme-section-newsletter bg-[#0f172a] px-6 py-14 text-white"
>
    @isset($heading)
        <div class="mx-auto max-w-5xl">
            <p class="text-xs font-black tracking-[0.2em] text-[#e2e8f0]">
                Knowledge Hub
            </p>
            <h2 class="mt-3 text-4xl font-black tracking-tight text-white">
                {{ $heading }}
            </h2>
            <p class="mt-4 max-w-2xl text-slate-200">
                {{ $newsletterAvailable ?? false ? __('capell-theme-knowledge::generic.newsletter_connected') : __('capell-theme-knowledge::generic.newsletter_static') }}
            </p>
        </div>
    @endisset
</section>
