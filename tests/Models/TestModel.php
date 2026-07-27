<?php

namespace RussellRamey\JsonConfig\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use RussellRamey\JsonConfig\HasJsonConfig;

class TestModel extends Model
{
    use HasJsonConfig;

    protected $table = 'test_models';

    protected $fillable = [
        'name',
    ];

    protected string $jsonConfigAttribute = 'config';

    protected array $jsonConfigAttributes = [
        'theme',
        'locale',
        'timezone',
        'notifications_enabled',
    ];
}
