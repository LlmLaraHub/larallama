<?php

namespace Feature\Models;

use App\Models\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_model(): void
    {
        $user = User::factory()->create();

        $this->assertNotNull($user->meta_data);
        $this->assertNotNull($user->meta_data['google']);
    }
}
