<?php

declare(strict_types=1);

namespace Capell\Bookings\Enums;

use Filament\Support\Contracts\HasLabel;

enum BookingLessonSkillStatusEnum: string implements HasLabel
{
    case Introduced = 'introduced';
    case Practising = 'practising';
    case Confident = 'confident';
    case NeedsFocus = 'needs_focus';

    public function getLabel(): string
    {
        return __('capell-bookings::enum.booking_lesson_skill_status.' . $this->value);
    }
}
