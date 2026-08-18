<?php

namespace DRC\JsonConfig\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use DRC\JsonConfig\HasJsonConfig;

class TestModel extends Model
{
    use HasJsonConfig;

    protected $table = 'test_models';

    protected $fillable = [
        'name',
    ];

    protected string $jsonConfigColumn = 'configKey';

    protected array $jsonConfigAttributes = [
        'theme',
        'locale',
        'timezone',
        'notifications_enabled',
    ];
}
