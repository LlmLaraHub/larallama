<?php

namespace Tests\Feature\Models;

use App\Models\Setting;
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
}
