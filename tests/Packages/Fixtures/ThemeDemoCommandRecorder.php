<?php

declare(strict_types=1);

namespace Capell\Tests\Packages\Fixtures;

use Capell\FoundationTheme\Data\ThemeDemoInstallData;

final class ThemeDemoCommandRecorder
{
    /**
     * @var list<ThemeDemoInstallData>
     */
    public array $records = [];
}
