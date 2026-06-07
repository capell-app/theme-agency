<?php

declare(strict_types=1);

namespace Capell\MediaAI\Providers;

use Capell\Admin\Contracts\Extenders\MediaEditActionExtender;
use Capell\Core\Enums\PackageTypeEnum;
use Capell\Core\Support\Packages\AbstractPackageServiceProvider;
use Capell\MediaAI\Console\Commands\QueueImageDoctorBatchCommand;
use Capell\MediaAI\Contracts\ImageDoctor;
use Capell\MediaAI\Filament\MediaAIEditActionExtender;
use Capell\MediaAI\Support\AIOrchestratorImageDoctor;
use Capell\MediaAI\Support\NullImageDoctor;
use Spatie\LaravelPackageTools\Package;

final class MediaAIServiceProvider extends AbstractPackageServiceProvider
{
    public static string $name = 'capell-media-ai';

    public static string $packageName = 'capell-app/media-ai';

    public static PackageTypeEnum $type = PackageTypeEnum::Package;

    public function configurePackage(Package $package): void
    {
        $package
            ->name(self::$name)
            ->hasConfigFile()
            ->hasTranslations()
            ->hasCommand(QueueImageDoctorBatchCommand::class);
    }

    public function registeringPackage(): void
    {
        $this->app->singletonIf(ImageDoctor::class, fn (): ImageDoctor => $this->imageDoctor());

        if (
            config('capell-media-ai.enabled', true) === true
            && interface_exists(MediaEditActionExtender::class)
        ) {
            $this->app->tag(MediaAIEditActionExtender::class, MediaEditActionExtender::TAG);
        }
    }

    private function imageDoctor(): ImageDoctor
    {
        if (config('capell-media-ai.image_doctor.driver') === 'ai_orchestrator') {
            return new AIOrchestratorImageDoctor;
        }

        return new NullImageDoctor;
    }
}
