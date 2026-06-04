<?php

declare(strict_types=1);

namespace Capell\RecordSwitcher\Tests\Fixtures;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class RecordSwitcherSearchFixture extends Model
{
    /** @use HasFactory<Factory<self>> */
    use HasFactory;

    protected $table = 'record_switcher_search_fixtures';
}
