<?php

declare(strict_types=1);

namespace Capell\Hero\Data;

final readonly class HeroBackgroundData
{
    public const string ModeInherit = 'inherit';

    public const string ModeDefault = 'default';

    public const string ModeCustom = 'custom';

    public const string ModeOff = 'off';

    /** @var list<string> */
    public const array OverlayStyles = [
        'mesh',
        'ribbons',
        'grid',
        'contours',
    ];

    public function __construct(
        public bool $enabled,
        public string $backgroundColor,
        public string $overlayStyle,
        public float $overlayOpacity,
        public string $accentColor,
        public string $accentColorAlt,
    ) {}

    public static function defaults(): self
    {
        return new self(
            enabled: true,
            backgroundColor: '#f4f7fb',
            overlayStyle: 'mesh',
            overlayOpacity: 0.32,
            accentColor: '#315f8f',
            accentColorAlt: '#7aa0c4',
        );
    }

    public static function disabled(): self
    {
        $defaults = self::defaults();

        return new self(
            enabled: false,
            backgroundColor: $defaults->backgroundColor,
            overlayStyle: $defaults->overlayStyle,
            overlayOpacity: $defaults->overlayOpacity,
            accentColor: $defaults->accentColor,
            accentColorAlt: $defaults->accentColorAlt,
        );
    }

    /**
     * @return array<string, string>
     */
    public function cssVariables(): array
    {
        return [
            '--capell-hero-background-color' => $this->backgroundColor,
            '--capell-hero-overlay-opacity' => (string) $this->overlayOpacity,
            '--capell-hero-accent-color' => $this->accentColor,
            '--capell-hero-accent-color-alt' => $this->accentColorAlt,
        ];
    }
}
