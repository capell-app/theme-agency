<?php

declare(strict_types=1);

namespace Capell\CustomerPortal\Actions;

use Capell\CustomerPortal\Data\PortalPreferenceOptionData;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static array<int, PortalPreferenceOptionData> run()
 */
final class ResolvePortalPreferenceOptionsAction
{
    use AsAction;

    /**
     * @return list<PortalPreferenceOptionData>
     */
    public function handle(): array
    {
        $configuredOptions = config('capell-customer-portal.preferences', []);

        if (! is_array($configuredOptions)) {
            return [];
        }

        $options = [];

        foreach ($configuredOptions as $key => $optionConfig) {
            if (! is_string($key) || $key === '') {
                continue;
            }

            if (! is_array($optionConfig)) {
                continue;
            }

            $label = $optionConfig['label'] ?? null;

            if (! is_string($label) || trim($label) === '') {
                continue;
            }

            $options[] = new PortalPreferenceOptionData(
                key: $key,
                label: __($label),
            );
        }

        return $options;
    }
}
