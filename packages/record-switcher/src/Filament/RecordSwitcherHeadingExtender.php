<?php

declare(strict_types=1);

namespace Capell\RecordSwitcher\Filament;

use Capell\Admin\Contracts\Extenders\EditRecordHeadingExtender;
use Capell\RecordSwitcher\Livewire\RecordSwitcher as RecordSwitcherComponent;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Livewire\Livewire;

final class RecordSwitcherHeadingExtender implements EditRecordHeadingExtender
{
    public function supports(EditRecord $page): bool
    {
        return true;
    }

    public function heading(EditRecord $page, string|Htmlable $default): Htmlable
    {
        $record = $page->getRecord();
        $label = $page->getRecordTitle();

        Livewire::component('capell-record-switcher::record-switcher', RecordSwitcherComponent::class);

        return new HtmlString(Blade::render(
            <<<'BLADE'
            @livewire('capell-record-switcher::record-switcher', [
                'pageClass' => $pageClass,
                'resourceClass' => $resourceClass,
                'recordKey' => $recordKey,
                'label' => $label,
            ], key($componentKey))
            BLADE,
            [
                'componentKey' => sprintf('record-switcher-%s-%s', str_replace('\\', '-', $page::class), $record->getRouteKey()),
                'label' => $label instanceof Htmlable ? $label->toHtml() : $label,
                'pageClass' => $page::class,
                'recordKey' => (string) $record->getRouteKey(),
                'resourceClass' => $page::getResource(),
            ],
        ));
    }

    public function saved(EditRecord $page): void
    {
        $label = $page->getRecordTitle();

        $page->dispatch('record-switcher:refresh', label: $label instanceof Htmlable ? $label->toHtml() : $label);
    }
}
