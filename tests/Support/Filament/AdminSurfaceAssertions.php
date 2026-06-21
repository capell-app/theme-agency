<?php

declare(strict_types=1);

namespace Capell\Tests\Support\Filament;

use Filament\Actions\ActionGroup;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Mockery;

final class AdminSurfaceAssertions
{
    public static function table(): Table
    {
        $livewire = Mockery::mock(HasTable::class);
        $livewire->shouldIgnoreMissing();
        $livewire->shouldReceive('makeFilamentTranslatableContentDriver')->andReturn(null)->byDefault();
        $livewire->shouldReceive('getTableFilterState')->andReturn([])->byDefault();
        $livewire->shouldReceive('isTableLoaded')->andReturnTrue()->byDefault();
        $livewire->shouldReceive('getTableArguments')->andReturn([])->byDefault();

        return Table::make($livewire);
    }

    /**
     * @param  array<array-key, mixed>  $actions
     * @return list<string>
     */
    public static function actionNames(array $actions): array
    {
        $names = [];

        foreach ($actions as $action) {
            foreach (self::flattenActionNames($action) as $name) {
                $names[] = $name;
            }
        }

        return $names;
    }

    /**
     * @return list<string>
     */
    private static function flattenActionNames(mixed $action): array
    {
        if ($action instanceof ActionGroup) {
            return self::actionNames($action->getActions());
        }

        if (is_object($action) && method_exists($action, 'getName')) {
            return [(string) $action->getName()];
        }

        return [];
    }
}
