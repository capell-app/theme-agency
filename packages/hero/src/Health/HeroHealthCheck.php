<?php

declare(strict_types=1);

namespace Capell\Hero\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Models\Layout;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;

final class HeroHealthCheck implements ChecksExtensionHealth
{
    public const string WidgetComponentAlias = 'capell::widget.hero';

    public const string ViewNamespace = 'capell-hero';

    public const string WidgetView = 'capell-hero::components.widget.hero';

    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }

    /**
     * @return Collection<int, DoctorCheckResultData>
     */
    public static function runDiagnostics(): Collection
    {
        $check = new self;

        return collect([
            $check->widgetComponentCheck(),
            $check->viewNamespaceCheck(),
            $check->homeLayoutDefaultsCheck(),
        ]);
    }

    public static function passed(): bool
    {
        return self::runDiagnostics()
            ->every(static fn (DoctorCheckResultData $result): bool => $result->passed);
    }

    /**
     * Asserts the hero widget Blade component alias is registered for rendering.
     */
    public function widgetComponentCheck(): DoctorCheckResultData
    {
        $isRegistered = $this->isWidgetComponentRegistered();

        return new DoctorCheckResultData(
            label: 'Hero widget component',
            passed: $isRegistered,
            message: $isRegistered
                ? 'The capell::widget.hero Blade component is registered.'
                : 'The capell::widget.hero Blade component is not registered.',
            remediation: $isRegistered
                ? null
                : 'Ensure HeroServiceProvider boots and registers the hero widget Blade component.',
        );
    }

    /**
     * Asserts the hero view namespace resolves the shipped widget view.
     */
    public function viewNamespaceCheck(): DoctorCheckResultData
    {
        $resolves = $this->viewNamespaceResolves();

        return new DoctorCheckResultData(
            label: 'Hero view namespace',
            passed: $resolves,
            message: $resolves
                ? 'The capell-hero view namespace resolves the hero widget view.'
                : 'The capell-hero view namespace cannot resolve the hero widget view.',
            remediation: $resolves
                ? null
                : 'Ensure HeroServiceProvider registers the capell-hero view namespace and ships its views.',
        );
    }

    public function isWidgetComponentRegistered(): bool
    {
        return array_key_exists(self::WidgetComponentAlias, Blade::getClassComponentAliases());
    }

    public function viewNamespaceResolves(): bool
    {
        return resolve(ViewFactory::class)->exists(self::WidgetView);
    }

    public function homeLayoutDefaultsCheck(): DoctorCheckResultData
    {
        $isSeeded = $this->hasSeededHomeLayoutState();

        return new DoctorCheckResultData(
            label: 'Hero home layout defaults',
            passed: $isSeeded,
            message: $isSeeded
                ? 'The home layout has the Hero and page-content default widgets.'
                : 'The home layout is missing the Hero default widget state.',
            remediation: $isSeeded
                ? null
                : 'Run capell:hero-setup --force to install the Hero-managed home layout defaults.',
        );
    }

    public function hasSeededHomeLayoutState(): bool
    {
        $homeLayout = Layout::query()
            ->where('key', LayoutEnum::Home->value)
            ->first();

        if (! $homeLayout instanceof Layout) {
            return false;
        }

        $containers = is_array($homeLayout->containers) ? $homeLayout->containers : [];

        return data_get($containers, 'hero.widgets.0.widget_key') === 'hero'
            && data_get($containers, 'main.widgets.0.widget_key') === 'page-content';
    }
}
