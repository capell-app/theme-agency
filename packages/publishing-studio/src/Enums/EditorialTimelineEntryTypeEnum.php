<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum EditorialTimelineEntryTypeEnum: string implements HasColor, HasLabel
{
    case Draft = 'draft';
    case Preview = 'preview';
    case Comment = 'comment';
    case Submitted = 'submitted';
    case ChangesRequested = 'changes_requested';
    case Rejected = 'rejected';
    case Approved = 'approved';
    case Scheduled = 'scheduled';
    case Published = 'published';
    case Restored = 'restored';
    case RolledBack = 'rolled_back';

    public function getLabel(): string
    {
        return match ($this) {
            self::Draft => __('capell-publishing-studio::editorial_timeline.types.draft'),
            self::Preview => __('capell-publishing-studio::editorial_timeline.types.preview'),
            self::Comment => __('capell-publishing-studio::editorial_timeline.types.comment'),
            self::Submitted => __('capell-publishing-studio::editorial_timeline.types.submitted'),
            self::ChangesRequested => __('capell-publishing-studio::editorial_timeline.types.changes_requested'),
            self::Rejected => __('capell-publishing-studio::editorial_timeline.types.rejected'),
            self::Approved => __('capell-publishing-studio::editorial_timeline.types.approved'),
            self::Scheduled => __('capell-publishing-studio::editorial_timeline.types.scheduled'),
            self::Published => __('capell-publishing-studio::editorial_timeline.types.published'),
            self::Restored => __('capell-publishing-studio::editorial_timeline.types.restored'),
            self::RolledBack => __('capell-publishing-studio::editorial_timeline.types.rolled_back'),
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Draft, self::Preview, self::Comment => 'gray',
            self::Submitted, self::Scheduled => 'warning',
            self::ChangesRequested, self::Rejected => 'danger',
            self::Approved, self::Published, self::Restored => 'success',
            self::RolledBack => 'info',
        };
    }
}
