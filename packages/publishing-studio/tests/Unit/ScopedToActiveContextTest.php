<?php

declare(strict_types=1);

use Capell\Core\Models\Language;
use Capell\Core\Models\Site;
use Capell\PublishingStudio\Concerns\ScopedToActiveContext;
use Capell\PublishingStudio\Models\Workspace;
use Capell\PublishingStudio\WorkspaceContext;

it('provides default nullable active context hooks', function (): void {
    WorkspaceContext::clear();

    $scoper = new class
    {
        use ScopedToActiveContext;

        public function site(): ?Site
        {
            return $this->activeSite();
        }

        public function workspace(): ?Workspace
        {
            return $this->activeWorkspace();
        }

        public function language(): ?Language
        {
            return $this->activeLanguage();
        }
    };

    expect($scoper->site())->toBeNull()
        ->and($scoper->workspace())->toBeNull()
        ->and($scoper->language())->toBeNull();
});
