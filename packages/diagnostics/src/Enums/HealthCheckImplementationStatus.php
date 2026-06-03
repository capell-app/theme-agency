<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Enums;

/**
 * Describes whether a declared health-check class actually self-tests.
 *
 * A declared `healthChecks[]` entry in a package manifest points at a class that
 * implements the `ChecksExtensionHealth` contract. The contract only mandates a
 * compatibility version, so a class can satisfy it while asserting nothing. This
 * enum distinguishes runnable checks from no-op stubs and broken declarations.
 */
enum HealthCheckImplementationStatus: string
{
    /** The class exposes a runnable `runDiagnostics()` method. */
    case Implemented = 'implemented';

    /** The class only satisfies the contract method and asserts nothing. */
    case Stub = 'stub';

    /** The declared class is missing or does not implement the health contract. */
    case Broken = 'broken';
}
