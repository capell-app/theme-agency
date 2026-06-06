@php
    /** @var \Capell\Experiments\Filament\Resources\Experiments\Pages\ExperimentResultsPage $this */
@endphp

<x-filament-panels::page>
    @include('capell-experiments::filament.experiments.results', [
        'experiment' => $this->record,
        'report' => $this->getReport(),
    ])
</x-filament-panels::page>
