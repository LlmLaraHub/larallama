<?php

namespace Tests\Feature\Models;

use App\Models\Setting;
use App\Models\User;
use Tests\TestCase;

class SettingTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_model(): void
    {

        $model = Setting::factory()->create();

        $this->assertNotNull($model->meta_data);
        $this->assertNotNull($model->secrets);
        $this->assertNotNull($model->user_id);
        $this->assertNotNull($model->user->id);
    }

    public function test_creates(): void
    {

        $this->be(User::factory()->create());
        $model = Setting::createNewSetting();

        $this->assertNotNull($model->meta_data);
        $this->assertNotNull($model->secrets);
        $this->assertNotNull($model->user_id);
        $this->assertNotNull($model->user->id);
    }

    public function test_get_secret(): void
    {
        $this->be(User::factory()->create());
        $model = Setting::factory()->all_have_keys()->create();

        $openai = Setting::getSecret('openai');
        $this->assertNotNull($openai);
        $this->assertEquals('https://api.openai.com/v1', $openai['api_url']);

        $openai = Setting::getSecret('openai', 'api_url');
        $this->assertNotNull($openai);
        $this->assertEquals('https://api.openai.com/v1', $openai);

        $openai = Setting::getSecret('groq', 'api_key');
        $this->assertNotNull($openai);
        $this->assertEquals('foobar', $openai);
    }

    public function test_get_llms(): void
    {
        $this->be(User::factory()->create());
        $model = Setting::createNewSetting();
        $llms = $model->getLlms();

        $this->assertCount(0, $llms);

    }

    public function test_get_llms_all_have_keys(): void
    {
        $this->be(User::factory()->create());
        $model = Setting::factory()->all_have_keys()->create();
        $llms = $model->getLlms();

        $this->assertCount(4, $llms);

        $this->assertCount(4,
            Setting::getAllActiveLlms()
        );
    }

    public function test_get_settings(): void
    {
        $this->be(User::factory()->create());
        $model = Setting::createNewSetting();
        $this->assertFalse(Setting::secretsConfigured());
        $model->updateStep($model);
        $this->assertTrue(Setting::secretsConfigured());
    }

    public function test_get_drivers(): void
    {
        $this->markTestSkipped('@TODO had to pivot');
        $this->be(User::factory()->create());
        $model = Setting::createNewSetting();
        $this->assertFalse(Setting::secretsConfigured());
        $model->updateStep($model);
        $this->assertTrue(Setting::secretsConfigured());
    }
}
