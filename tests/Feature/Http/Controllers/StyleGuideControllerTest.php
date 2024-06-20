<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Audience;
use App\Models\Persona;
use App\Models\User;
use Tests\TestCase;

class StyleGuideControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_create_persona(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('style_guide.create.persona'), [
            'name' => 'Test Persona',
            'content' => 'Test Data',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseCount('personas', 1);
    }

    public function test_update_persona(): void
    {
        $user = User::factory()->create();

        $persona = Persona::factory()->create();

        $this->actingAs($user)->put(route('style_guide.update.persona', [
            $persona,
        ]), [
            'name' => 'Test Persona',
            'content' => 'Test Data',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseCount('personas', 1);

        $this->assertEquals('Test Persona', $persona->refresh()->name);
        $this->assertEquals('Test Data', $persona->refresh()->content);
    }

    public function test_create_audience(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('style_guide.create.audience'), [
            'name' => 'Test Persona',
            'content' => 'Test Data',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseCount('audiences', 1);
    }

    public function test_update_audience(): void
    {
        $user = User::factory()->create();

        $persona = Audience::factory()->create();

        $this->actingAs($user)->put(route('style_guide.update.audience', [
            $persona,
        ]), [
            'name' => 'Test Persona',
            'content' => 'Test Data',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseCount('audiences', 1);

        $this->assertEquals('Test Persona', $persona->refresh()->name);
        $this->assertEquals('Test Data', $persona->refresh()->content);
    }
}
