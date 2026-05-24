<?php

declare(strict_types=1);

namespace Capell\Comments\Enums;

use Capell\Comments\Livewire\CommentThreadComponent;

enum LivewireComponentEnum: string
{
    case Thread = 'capell-comments::thread';

    /**
     * @return array<string, class-string>
     */
    public static function getComponents(): array
    {
        return [
            self::Thread->value => CommentThreadComponent::class,
        ];
    }
}
