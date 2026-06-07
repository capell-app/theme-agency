<?php

declare(strict_types=1);

return [
    'enabled' => true,
    'image_doctor' => [
        'driver' => env('CAPELL_MEDIA_AI_IMAGE_DOCTOR', 'null'),
        'rate_limit' => [
            'max_attempts' => (int) env('CAPELL_MEDIA_AI_IMAGE_DOCTOR_MAX_ATTEMPTS', 10),
            'decay_seconds' => (int) env('CAPELL_MEDIA_AI_IMAGE_DOCTOR_DECAY_SECONDS', 3600),
        ],
        'budget_cents' => env('CAPELL_MEDIA_AI_IMAGE_DOCTOR_BUDGET_CENTS') === null
            ? null
            : (int) env('CAPELL_MEDIA_AI_IMAGE_DOCTOR_BUDGET_CENTS'),
        'model' => env('CAPELL_MEDIA_AI_IMAGE_DOCTOR_MODEL'),
        'ai_orchestrator' => [
            'module' => env('CAPELL_MEDIA_AI_ORCHESTRATOR_MODULE', 'media-ai'),
            'capability' => env('CAPELL_MEDIA_AI_ORCHESTRATOR_CAPABILITY', 'doctor-image'),
        ],
    ],
];
