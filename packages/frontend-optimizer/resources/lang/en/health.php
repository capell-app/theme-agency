<?php

declare(strict_types=1);

return [
    'critical_css_storage' => [
        'failed' => 'The local storage disk is not writable for generated critical CSS.',
        'label' => 'Frontend Optimizer critical CSS storage',
        'passed' => 'The local storage disk is writable for generated critical CSS.',
        'remediation' => 'Check filesystem permissions on the configured critical CSS path so generated CSS can be persisted.',
    ],
    'generator' => [
        'failed' => 'The configured Node/Playwright critical CSS generator is not ready.',
        'label' => 'Frontend Optimizer critical CSS generator',
        'passed' => 'The configured Node command, generator script, and Playwright package declaration are present.',
        'remediation' => 'Ensure the configured Node binary runs, the generator script exists, and package JavaScript dependencies are installed before enabling generation workers.',
    ],
    'manifest_storage' => [
        'failed' => 'The local storage disk is not writable for render-profile manifests.',
        'label' => 'Frontend Optimizer manifest storage',
        'passed' => 'The local storage disk is writable for render-profile manifests.',
        'remediation' => 'Check filesystem permissions on the configured manifest path so render profiles can be persisted.',
    ],
    'queue_driver' => [
        'failed' => 'Automatic critical-CSS generation is enabled while the queue driver is "sync"; generation would run the real browser inside the public request.',
        'label' => 'Frontend Optimizer generation queue driver',
        'passed' => 'Critical-CSS generation is dispatched to an asynchronous queue.',
        'remediation' => 'Configure a non-sync queue connection or disable automatic critical-CSS generation so the Playwright generator runs out of band.',
    ],
    'renderer_binding' => [
        'failed' => 'The public asset manifest renderer is not bound to the Frontend Optimizer; pages render with the default renderer and receive no optimization.',
        'label' => 'Frontend Optimizer asset renderer binding',
        'passed' => 'The public asset manifest renderer is bound to the Frontend Optimizer.',
        'remediation' => 'Ensure the Frontend Optimizer package is installed so its service provider rebinds the FrontendAssetManifestRenderer contract.',
    ],
];
