<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Collection;
use App\Models\Output;
use App\Models\Source;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OutputControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_index(): void
    {
        $collection = Collection::factory()->create();

        Output::factory(10)->create([
            'collection_id' => $collection->id,
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('collections.outputs.index', $collection))
            ->assertOk();
    }
}
