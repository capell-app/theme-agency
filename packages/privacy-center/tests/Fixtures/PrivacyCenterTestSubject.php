<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Tests\Fixtures;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class PrivacyCenterTestSubject extends Model
{
    /** @use HasFactory<Factory<self>> */
    use HasFactory;

    protected $table = 'privacy_center_test_subjects';

    /** @var array<string> */
    protected $guarded = [];
}
