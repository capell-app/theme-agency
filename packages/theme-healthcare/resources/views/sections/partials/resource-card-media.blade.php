@if ($imageUrl)
    <img
        src="{{ $imageUrl }}"
        alt="{{ $imageAlt }}"
        width="960"
        height="600"
        loading="lazy"
        decoding="async"
        class="aspect-[16/10] w-full object-cover"
    />
@else
    <div
        class="grid aspect-[16/10] bg-[#14323a] p-4 text-white"
        aria-hidden="true"
    >
        <div class="grid h-full content-between">
            <div class="flex items-center justify-between gap-3">
                <span class="h-3 w-20 rounded-full bg-[#0f766e]"></span>
                <span class="h-3 w-10 rounded-full bg-[#f59e0b]"></span>
            </div>
            <div class="grid gap-2">
                <span class="h-3 w-3/4 rounded-full bg-white/45"></span>
                <span class="h-3 w-1/2 rounded-full bg-white/25"></span>
            </div>
            <div class="grid grid-cols-3 gap-2">
                <span class="h-10 rounded bg-white/10"></span>
                <span class="h-10 rounded bg-white/20"></span>
                <span class="h-10 rounded bg-white/10"></span>
            </div>
        </div>
    </div>
@endif
