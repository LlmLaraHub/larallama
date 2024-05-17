<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Collection;
use App\Models\Document;
use App\Models\Filter;
use App\Models\User;
use Tests\TestCase;

class FilterControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_create(): void
    {
        $user = User::factory()->create();

        $collection = Collection::factory()->create();

        $documents = Document::factory(3)->create([
            'collection_id' => $collection->id,
        ]);

        $this->assertDatabaseCount('filters', 0);
        $this->actingAs($user)->post(
            route('filters.create', [
                'collection' => $collection->id,
            ]), [
                'documents' => $documents->pluck('id')->toArray(),
                'name' => 'foo bar',
                'description' => 'Baz',
            ])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseCount('filters', 1);

        $filter = Filter::first();

        $this->assertCount(3, $filter->documents);
    }
}
