<?php

declare(strict_types=1);

namespace Capell\Bookings\Enums;

use Filament\Support\Contracts\HasLabel;

enum LessonNoteVisibilityEnum: string implements HasLabel
{
    case Private = 'private';
    case Shared = 'shared';

    public function isPortalVisible(): bool
    {
        return $this === self::Shared;
    }

    public function getLabel(): string
    {
        return __('capell-bookings::enum.lesson_note_visibility_' . $this->value);
    }
}
