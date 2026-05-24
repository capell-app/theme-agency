<section class="velocity-features bg-white">
    <div class="px-6">
        <div
            class="flex flex-col justify-between gap-5 md:flex-row md:items-end"
        >
            <div>
                <h2>{{ $section->heading }}</h2>
                @if ($section->summary)
                    <p class="mt-4 max-w-2xl text-lg">
                        {{ $section->summary }}
                    </p>
                @endif
            </div>
        </div>

        <div class="mt-10 grid gap-4 md:grid-cols-3">
            @foreach ($section->features as $feature)
                <article
                    class="rounded-2xl border border-slate-200 bg-slate-50/70 p-6"
                >
                    <div
                        class="mb-5 flex h-11 w-11 items-center justify-center rounded-xl bg-blue-600 text-sm font-black text-white"
                    >
                        {{ str($feature['title'])->substr(0, 1)->upper() }}
                    </div>
                    <h3 class="text-xl font-black">{{ $feature['title'] }}</h3>
                    <p class="mt-3 text-sm">
                        {{ $feature['description'] }}
                    </p>
                </article>
            @endforeach
        </div>
    </div>
</section>
