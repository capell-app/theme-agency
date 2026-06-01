<?php

declare(strict_types=1);

$status = (static function (): int {
    $shardPluginPath = dirname(__DIR__) . '/vendor/pestphp/pest/src/Plugins/Shard.php';

    if (! file_exists($shardPluginPath)) {
        echo "Pest shard plugin not found at {$shardPluginPath}; skipping shard patch.\n";

        return 0;
    }

    $contents = file_get_contents($shardPluginPath);

    if (! is_string($contents)) {
        fwrite(STDERR, "Unable to read Pest shard plugin at {$shardPluginPath}.\n");

        return 1;
    }

    $originalPattern = "preg_match_all('/ - (?:P\\\\\\\\)?(Tests\\\\\\\\[^:]+)::/', \$output, \$matches);";
    $patchedPattern = "preg_match_all('/ - (?:P\\\\\\\\)?([^:\\\\r\\\\n]+)::/', \$output, \$matches);";

    if (str_contains($contents, $patchedPattern)) {
        echo "Pest shard namespace patch already applied.\n";

        return 0;
    }

    if (! str_contains($contents, $originalPattern)) {
        fwrite(STDERR, "Pest shard namespace patch could not find the expected source line.\n");

        return 1;
    }

    file_put_contents($shardPluginPath, str_replace($originalPattern, $patchedPattern, $contents));

    echo "Pest shard namespace patch applied.\n";

    return 0;
})();

return $status;
