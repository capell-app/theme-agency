<?php

declare(strict_types=1);

namespace Capell\MediaAI\Data;

use InvalidArgumentException;

final readonly class ImageDoctorRequest
{
    /**
     * The canonical set of image operations a provider may be asked to perform.
     *
     * @var list<string>
     */
    public const array OPERATIONS = [
        'improve',
        'remove_background',
        'remove_object',
        'restore',
        'upscale',
    ];

    public function __construct(
        public string $operation,
        public string $instructions,
    ) {
        if (! in_array($operation, self::OPERATIONS, true)) {
            throw new InvalidArgumentException(
                sprintf('Unsupported image doctor operation [%s].', $operation),
            );
        }
    }
}
