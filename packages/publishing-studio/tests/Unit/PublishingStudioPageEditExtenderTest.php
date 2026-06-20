<?php

declare(strict_types=1);

use Capell\PublishingStudio\Extenders\PublishingStudioPageEditExtender;
use Capell\PublishingStudio\Filament\Widgets\PageAlertsFilamentWidget;
use Capell\PublishingStudio\Livewire\PageApprovalStatus;

it('contributes page edit widgets without overriding the edit record context', function (): void {
    $widgets = resolve(PublishingStudioPageEditExtender::class)->getHeaderWidgets();

    expect($widgets)->toBe([
        PageAlertsFilamentWidget::class,
        PageApprovalStatus::class,
    ]);
});
