<?php

declare(strict_types=1);

namespace Capell\Comments\Filament\Settings\Contributors;

use Capell\Admin\Contracts\DashboardSettingsContributor;

class CommentsDashboardSettingsContributor implements DashboardSettingsContributor
{
    /**
     * @return list<array{key: string, label: string, group: string}>
     */
    public function settingsKeys(): array
    {
        return [
            [
                'key' => 'comment_stats',
                'label' => __('capell-comments::widgets.comment_stats'),
                'group' => __('capell-comments::widgets.group'),
            ],
            [
                'key' => 'latest_comments',
                'label' => __('capell-comments::widgets.latest_comments'),
                'group' => __('capell-comments::widgets.group'),
            ],
        ];
    }
}
