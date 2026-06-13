<?php

declare(strict_types=1);

namespace Capell\Bookings\Filament\Resources\LessonSeries\Pages;

use Capell\Bookings\Filament\Resources\LessonSeries\LessonSeriesResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateLessonSeries extends CreateRecord
{
    protected static string $resource = LessonSeriesResource::class;
}
