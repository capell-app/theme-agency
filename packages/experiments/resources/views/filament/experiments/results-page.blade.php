@php
    use Capell\Experiments\Filament\Resources\Experiments\Pages\ExperimentResultsPage;

    /** @var ExperimentResultsPage $this */
@endphp

<x-filament-panels::page>
    @include ('capell-experiments::filament.experiments.results', [
        'experiment' => $this->record,
        'report' => $this->getReport(),
    ])
</x-filament-panels::page>
