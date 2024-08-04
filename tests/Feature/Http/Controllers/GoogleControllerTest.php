<?php

namespace Feature\Http\Controllers;

use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Tests\TestCase;

class GoogleControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_saves_meta_data(): void
    {
        $this->markTestSkipped('@TODO need to come back to getting Google Auth working');
        $user = User::factory()->create([
            'meta_data' => [],
        ]);

        Socialite::shouldReceive('driver->user')
            ->once()
            ->andReturn([
                'token' => 'foo',
                'refreshToken' => 'bar',
                'expiresIn' => 3600,
                'getAvatar' => 'https://foo.bar',
                'getEmail' => 'foo@bar.com',
            ]);

        $this->actingAs($user)->get(route('auth.google.callback'));

        $this->assertNotNull($user->refresh()->meta_data);

    }
}
