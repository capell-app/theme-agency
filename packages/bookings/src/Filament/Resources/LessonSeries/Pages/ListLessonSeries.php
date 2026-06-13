<?php

declare(strict_types=1);

namespace Capell\Bookings\Filament\Resources\LessonSeries\Pages;

use Capell\Bookings\Filament\Resources\LessonSeries\LessonSeriesResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListLessonSeries extends ListRecords
{
    protected static string $resource = LessonSeriesResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
