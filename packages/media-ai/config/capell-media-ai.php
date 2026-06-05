<?php

declare(strict_types=1);

return [
    'enabled' => true,
    'image_doctor' => [
        'driver' => env('CAPELL_MEDIA_AI_IMAGE_DOCTOR', 'null'),
        'ai_orchestrator' => [
            'module' => env('CAPELL_MEDIA_AI_ORCHESTRATOR_MODULE', 'media-ai'),
            'capability' => env('CAPELL_MEDIA_AI_ORCHESTRATOR_CAPABILITY', 'doctor-image'),
        ],
    ],
];
