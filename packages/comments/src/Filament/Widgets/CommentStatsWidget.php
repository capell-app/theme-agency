<?php

declare(strict_types=1);

namespace Capell\Comments\Filament\Widgets;

use Capell\Admin\Contracts\CapellWidgetContract;
use Capell\Admin\Filament\Concerns\GatedByRoleAndSettings;
use Capell\Admin\Support\SiteScope;
use Capell\Comments\Enums\CommentStatus;
use Capell\Comments\Filament\Resources\Comments\CommentResource;
use Capell\Comments\Models\Comment;
use Capell\Core\Contracts\Extensions\RegistersExtensionWidget;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CommentStatsWidget extends StatsOverviewWidget implements CapellWidgetContract, RegistersExtensionWidget
{
    use GatedByRoleAndSettings;

    /** @var list<string> */
    protected static array $rolesConfigKeys = ['editor', 'admin', 'super_admin'];

    protected static string $settingsKey = 'comment_stats';

    /** @var int|string|array<string, int|null> */
    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 30;

    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }

    protected function getStats(): array
    {
        return [
            Stat::make(__('capell-comments::widgets.pending'), (string) $this->count(CommentStatus::PendingApproval))
                ->color('warning')
                ->url($this->commentsUrl(CommentStatus::PendingApproval)),
            Stat::make(__('capell-comments::widgets.approved'), (string) $this->count(CommentStatus::Approved))
                ->color('success')
                ->url($this->commentsUrl(CommentStatus::Approved)),
            Stat::make(__('capell-comments::widgets.spam'), (string) $this->count(CommentStatus::Spam))
                ->color('danger')
                ->url($this->commentsUrl(CommentStatus::Spam)),
        ];
    }

    private function commentsUrl(CommentStatus $status): string
    {
        return CommentResource::getUrl(parameters: [
            'tableFilters' => [
                'status' => [
                    'value' => $status->value,
                ],
            ],
        ]);
    }

    private function count(CommentStatus $status): int
    {
        return SiteScope::applyForCurrentActor(Comment::query())
            ->where('status', $status)
            ->count();
    }
}
