<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\RecruitmentJobs\Actions;

use Capell\FoundationTheme\Contracts\InstallsThemeDemo;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageInstaller;
use Capell\ThemeStudio\RecruitmentJobs\Support\Demo\RecruitmentJobsDemoContent;
use Lorisleiva\Actions\Concerns\AsObject;

final class InstallRecruitmentJobsThemeDemoAction implements InstallsThemeDemo
{
    use AsObject;

    public function handle(ThemeDemoInstallData $data): int
    {
        return ThemeDemoPageInstaller::run($data, 'recruitment-jobs', 'Recruitment & Jobs', new RecruitmentJobsDemoContent);
    }
}
