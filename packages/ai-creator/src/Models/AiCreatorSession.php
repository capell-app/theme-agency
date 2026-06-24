<?php

declare(strict_types=1);

namespace Capell\AiCreator\Models;

use Capell\AiCreator\Enums\AiCreatorSessionStatus;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $intent
 * @property array<array-key, mixed>|null $questions
 * @property array<array-key, mixed>|null $answers
 * @property array<array-key, mixed>|null $package_recommendations
 * @property array<array-key, mixed>|null $package_requirements
 * @property array<array-key, mixed>|null $content_plan
 * @property array<array-key, mixed>|null $page_plan
 * @property array<array-key, mixed>|null $layout_plan
 * @property array<array-key, mixed>|null $theme_plan
 * @property array<array-key, mixed>|null $preview_output
 * @property AiCreatorSessionStatus $status
 * @property int|null $user_id
 * @property int|null $site_id
 * @property int|null $workspace_id
 * @property CarbonImmutable|null $applied_at
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 */
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
