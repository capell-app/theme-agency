<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Livewire;

use Capell\PublishingStudio\Actions\BuildEditorialTimelineAction;
use Capell\PublishingStudio\Data\EditorialTimelineEntryData;
use Capell\PublishingStudio\Models\Workspace;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

final class EditorialTimeline extends Component
{
    public Workspace $workspace;

    /**
     * @return Collection<int, EditorialTimelineEntryData>
     */
    #[Computed]
    public function entries(): Collection
    {
        return BuildEditorialTimelineAction::run($this->workspace);
    }

    public function render(): View
    {
        /** @var view-string $view */
        $view = 'capell-publishing-studio::livewire.editorial-timeline';

        return view($view);
    }
}
