<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use Tests\TestCase;

class SettingControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_show_makes(): void
    {
        $user = User::factory()->create();

        $this->assertDatabaseCount('settings', 0);

        $response = $this
            ->actingAs($user)
            ->get(route('settings.show'))
            ->assertStatus(200);

        $this->assertDatabaseCount('settings', 1);
    }

    public function test_updates_open_ai(): void
    {
        $user = User::factory()->create();

        $setting = Setting::factory()->create();

        $response = $this
            ->actingAs($user)
            ->put(route('settings.update.open_ai', $setting), [
                'api_key' => 'foobar',
                'api_url' => 'https://api.openai.com/v1',
            ])
            ->assertSessionHasNoErrors();

        $setting = Setting::first();

        $this->assertEquals('foobar', $setting->secrets['openai']['api_key']);
        $this->assertEquals('https://api.openai.com/v1', $setting->secrets['openai']['api_url']);

        $this->assertTrue($setting->steps['setup_secrets']);
    }
}
