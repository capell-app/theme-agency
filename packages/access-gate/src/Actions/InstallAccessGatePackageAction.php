<?php

declare(strict_types=1);

namespace Capell\AccessGate\Actions;

use Capell\AccessGate\Models\Area;
use Capell\AccessGate\Support\AccessGateSchema;
use Capell\Core\Actions\Install\PublishPackageMigrationsAction;
use Capell\Core\Actions\Install\RunArtisanCommandAction;
use Capell\Core\Actions\Install\RunMigrationsAction;
use Capell\Core\Contracts\PackageLifecycleAction;
use Capell\Core\Contracts\ProgressReporter;
use Capell\Core\Data\PackageData;
use Capell\Core\Support\Install\NullProgressReporter;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsObject;

final class InstallAccessGatePackageAction implements PackageLifecycleAction
{
    use AsObject;

    public function __construct(
        private readonly SetupDefaultAccessAreaAction $setupDefaultArea,
    ) {}

    public function handle(PackageData $package, array $arguments = [], ?ProgressReporter $reporter = null): void
    {
        $reporter ??= new NullProgressReporter;

        $schemaIsReady = $this->accessGateSchemaIsReady();

        foreach ($this->publishTags() as $tag) {
            RunArtisanCommandAction::run('vendor:publish', [
                '--tag' => $tag,
                '--force' => false,
            ], $reporter);
        }

        $reporter->report((string) __('capell-access-gate::install.published'));

        if (! $schemaIsReady) {
            PublishPackageMigrationsAction::run(new Collection([$package->name => $package]), $reporter);
            RunMigrationsAction::run($reporter);
        }

        $area = $this->setupDefaultArea->handle();

        $reporter->report((string) __('capell-access-gate::install.default_area_ready', ['key' => $area->key]));
    }

    /**
     * @return list<string>
     */
    private function publishTags(): array
    {
        return [
            'capell-access-gate-config',
            'capell-access-gate-views',
            'capell-access-gate-translations',
        ];
    }

    private function accessGateSchemaIsReady(): bool
    {
        $schema = AccessGateSchema::builder();
        $areasTable = (new Area)->getTable();

        return $schema->hasTable($areasTable)
            && $schema->hasColumn($areasTable, 'site_id')
            && $schema->hasTable('access_gate_registrations')
            && $schema->hasTable('access_gate_grants')
            && $schema->hasTable('access_gate_claim_tokens')
            && $schema->hasTable('access_gate_browser_tokens')
            && $schema->hasTable('access_gate_events');
    }
}
