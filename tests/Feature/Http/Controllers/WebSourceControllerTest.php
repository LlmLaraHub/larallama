<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Collection;
use App\Models\User;
use Tests\TestCase;

class WebSourceControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_store(): void
    {
        $user = User::factory()->create();
        $collection = Collection::factory()->create();
        $this->assertDatabaseCount('sources', 0);
        $response = $this->actingAs($user)
            ->post(route('collections.sources.websearch.store', $collection), [
                'title' => 'Test Title',
                'details' => 'Test Details',
            ]);
        $response->assertSessionHas('flas.banner', 'Web source added successfully');
        $this->assertDatabaseCount('sources', 1);
    }
}
