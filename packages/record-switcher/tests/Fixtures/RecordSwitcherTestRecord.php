<?php

declare(strict_types=1);

namespace Capell\RecordSwitcher\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

final class RecordSwitcherTestRecord extends Model
{
    protected $table = 'record_switcher_test_records';

    protected $guarded = [];
}
