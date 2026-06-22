<?php

declare(strict_types=1);

namespace Capell\AiCreator\Models;

use Capell\AiCreator\Enums\AiCreatorSessionStatus;
use Illuminate\Database\Eloquent\Model;

final class AiCreatorSession extends Model
{
    protected $table = 'capell_ai_creator_sessions';

    /** @var list<string> */
    protected $fillable = [
        'intent',
        'questions',
        'answers',
        'package_recommendations',
        'package_requirements',
        'content_plan',
        'page_plan',
        'layout_plan',
        'theme_plan',
        'preview_output',
        'status',
        'user_id',
        'site_id',
        'workspace_id',
        'applied_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'questions' => 'array',
            'answers' => 'array',
            'package_recommendations' => 'array',
            'package_requirements' => 'array',
            'content_plan' => 'array',
            'page_plan' => 'array',
            'layout_plan' => 'array',
            'theme_plan' => 'array',
            'preview_output' => 'array',
            'status' => AiCreatorSessionStatus::class,
            'applied_at' => 'datetime',
        ];
    }
}
