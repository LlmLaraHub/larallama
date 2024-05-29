<?php

namespace Tests\Feature\Http\Controllers;

use App\Domains\Recurring\RecurringTypeEnum;
use App\Models\Collection;
use App\Models\Source;
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
            ->post(route('collections.sources.web_search_source.store', $collection), [
                'title' => 'Test Title',
                'active' => 1,
                'recurring' => RecurringTypeEnum::Daily->value,
                'details' => 'Test Details',
            ]);
        $response->assertSessionHas('flash.banner', 'Source added successfully');
        $this->assertDatabaseCount('sources', 1);

        $source = Source::first();

        $this->assertNotEmpty($source->meta_data);
        $this->assertEquals(RecurringTypeEnum::Daily, $source->recurring);
        $this->assertEquals('brave', $source->meta_data['driver']);
    }

    public function test_update()
    {
        $source = Source::factory()->create();

        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('collections.sources.web_search_source.update',
                [
                    'collection' => $source->collection->id,
                    'source' => $source->id,
                ]
            ), [
                'title' => 'Test Title2',
                'active' => 1,
                'recurring' => RecurringTypeEnum::Daily->value,
                'details' => 'Test Details2',
            ])
            ->assertSessionHasNoErrors()
            ->assertStatus(302);

        $this->assertEquals($source->refresh()->details, 'Test Details2');
        $this->assertEquals($source->refresh()->recurring, RecurringTypeEnum::Daily);
    }
}
