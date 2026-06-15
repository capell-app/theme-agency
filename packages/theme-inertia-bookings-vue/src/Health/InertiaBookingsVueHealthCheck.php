<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\InertiaBookingsVue\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;

final class InertiaBookingsVueHealthCheck implements ChecksExtensionHealth
{
    /**
     * @var list<string>
     */
    private const array REQUIRED_FILES = [
        'resources/js/app.js',
        'resources/js/Pages/Capell/Page.vue',
        'resources/js/Pages/Capell/Bookings/Request.vue',
        'resources/js/Components/Capell/Widgets/Content.vue',
        'resources/js/Components/Capell/Widgets/Image.vue',
        'resources/js/Components/Capell/Widgets/Title.vue',
        'resources/js/Support/publicHtml.js',
    ];

    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }

    public function passes(): bool
    {
        return $this->missingRequiredFiles() === []
            && $this->componentMapContainsRequiredPages();
    }

    /**
     * @param  list<string>|null  $requiredFiles
     * @return list<string>
     */
    public function missingRequiredFiles(?array $requiredFiles = null): array
    {
        $basePath = dirname(__DIR__, 2);

        return array_values(collect($requiredFiles ?? self::REQUIRED_FILES)
            ->reject(static fn (string $path): bool => is_file($basePath . '/' . $path))
            ->values()
            ->all());
    }

    public function componentMapContainsRequiredPages(): bool
    {
        $entrypoint = dirname(__DIR__, 2) . '/resources/js/app.js';

        if (! is_file($entrypoint)) {
            return false;
        }

        $contents = (string) file_get_contents($entrypoint);

        return str_contains($contents, "'Capell/Page': Page")
            && str_contains($contents, "'Capell/Bookings/Request': BookingRequest")
            && str_contains($contents, 'pages[name] ?? Page');
    }
}
