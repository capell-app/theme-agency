<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\InertiaBookingsReact\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;

final class InertiaBookingsReactHealthCheck implements ChecksExtensionHealth
{
    /**
     * @var list<string>
     */
    private const array REQUIRED_FILES = [
        'resources/js/Pages/Capell/Page.jsx',
        'resources/js/Pages/Capell/Bookings/Request.jsx',
        'resources/js/Components/Capell/Widgets/Content.jsx',
        'resources/js/Components/Capell/Widgets/Image.jsx',
        'resources/js/Components/Capell/Widgets/Title.jsx',
    ];

    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }

    public function passes(): bool
    {
        $basePath = dirname(__DIR__, 2);

        foreach (self::REQUIRED_FILES as $requiredFile) {
            if (! is_file($basePath . '/' . $requiredFile)) {
                return false;
            }
        }

        return true;
    }
}
