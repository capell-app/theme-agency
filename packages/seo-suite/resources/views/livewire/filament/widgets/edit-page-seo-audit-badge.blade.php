<span class="inline-flex">
    @if ($this->issueCount !== null)
        <x-filament::badge
            :color="$this->issueCount === 0 ? 'success' : 'warning'"
            size="xs"
        >
            {{ $this->issueCount }}
        </x-filament::badge>
    @endif
</span>
