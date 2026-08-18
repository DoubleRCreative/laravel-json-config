<?php

namespace DRC\JsonConfig\Tests;

use DRC\JsonConfig\Tests\Models\TestModel;

class HasJsonConfigTest extends TestCase
{
    public function test_it_can_create_model_with_json_config_attributes()
    {
        $model = TestModel::create([
            'name' => 'Test User',
            'theme' => 'dark',
            'locale' => 'en-US',
        ]);

        $this->assertNotNull($model->id);
        $this->assertEquals('Test User', $model->name);
        $this->assertEquals('dark', $model->theme);
        $this->assertEquals('en-US', $model->locale);
    }

    public function test_json_config_attributes_are_persisted_in_database()
    {
        $model = TestModel::create([
            'name' => 'Test User',
            'theme' => 'dark',
            'locale' => 'en-US',
        ]);

        $fresh = $model->fresh();

        $this->assertEquals('dark', $fresh->theme);
        $this->assertEquals('en-US', $fresh->locale);

        $this->assertIsArray($fresh->getConfigAttributes());
        $this->assertEquals('dark', $fresh->getConfigAttributes()['theme']);
        $this->assertEquals('en-US', $fresh->getConfigAttributes()['locale']);
    }

    public function test_json_config_attributes_appear_in_toArray()
    {
        $model = TestModel::create([
            'name' => 'Test User',
            'theme' => 'dark',
            'locale' => 'en-US',
        ]);

        $array = $model->toArray();

        $this->assertArrayHasKey('theme', $array);
        $this->assertArrayHasKey('locale', $array);
        $this->assertEquals('dark', $array['theme']);
        $this->assertEquals('en-US', $array['locale']);
    }

    public function test_non_json_config_attributes_are_not_stored_in_json_column()
    {
        $model = TestModel::create([
            'name' => 'Test User',
            'theme' => 'dark',
        ]);

        $config = $model->getConfigAttributes();

        $this->assertArrayNotHasKey('name', $config);
        $this->assertArrayNotHasKey('id', $config);
    }

    public function test_setting_json_config_attributes_via_setJsonConfig()
    {
        $model = TestModel::create(['name' => 'Test']);

        $model->setJsonConfig('theme', 'light');
        $model->save();

        $fresh = $model->fresh();
        $this->assertEquals('light', $fresh->theme);
    }

    public function test_setJsonConfig_throws_for_undefined_attribute()
    {
        $model = TestModel::create(['name' => 'Test']);

        $this->expectException(\InvalidArgumentException::class);
        $model->setJsonConfig('undefined_attr', 'value');
    }

    public function test_removing_json_config_attribute()
    {
        $model = TestModel::create([
            'name' => 'Test',
            'theme' => 'dark',
            'locale' => 'en-US',
        ]);

        $model->removeJsonConfig('locale');
        $model->save();

        $fresh = $model->fresh();

        $this->assertFalse($fresh->hasJsonSchemaAttribute('locale'));
        $this->assertNull($fresh->locale);
        $this->assertTrue($fresh->hasJsonSchemaAttribute('theme'));
    }

    public function test_json_config_attributes_are_declared_on_model()
    {
        $model = new TestModel();

        $this->assertContains('theme', $model->getFillable());
        $this->assertContains('locale', $model->getFillable());
        $this->assertContains('timezone', $model->getFillable());
        $this->assertContains('notifications_enabled', $model->getFillable());
    }

    public function test_multiple_models_can_have_different_configs()
    {
        $alice = TestModel::create([
            'name' => 'Alice',
            'theme' => 'dark',
            'locale' => 'en-US',
        ]);

        $bob = TestModel::create([
            'name' => 'Bob',
            'theme' => 'light',
            'locale' => 'fr-FR',
            'timezone' => 'Europe/Paris',
        ]);

        $this->assertEquals('dark', $alice->theme);
        $this->assertEquals('en-US', $alice->locale);
        $this->assertNull($alice->timezone);

        $this->assertEquals('light', $bob->theme);
        $this->assertEquals('fr-FR', $bob->locale);
        $this->assertEquals('Europe/Paris', $bob->timezone);
    }

    public function test_getConfigAttributes_returns_all_config()
    {
        $model = TestModel::create([
            'name' => 'Test',
            'theme' => 'dark',
            'locale' => 'en-US',
            'notifications_enabled' => true,
        ]);

        $config = $model->getConfigAttributes();

        $this->assertEquals([
            'theme' => 'dark',
            'locale' => 'en-US',
            'notifications_enabled' => true,
        ], $config);
    }

    public function test_has_json_schema_attribute()
    {
        $model = TestModel::create([
            'name' => 'Test',
            'theme' => 'dark',
        ]);

        $this->assertTrue($model->hasJsonSchemaAttribute('theme'));
        $this->assertFalse($model->hasJsonSchemaAttribute('nonexistent'));
    }

    public function test_updating_json_config_attributes()
    {
        $model = TestModel::create([
            'name' => 'Test',
            'theme' => 'dark',
        ]);

        $model->theme = 'light';
        $model->save();

        $fresh = $model->fresh();
        $this->assertEquals('light', $fresh->theme);
    }
}
