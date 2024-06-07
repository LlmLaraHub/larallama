<?php

namespace Tests\Feature\Http\Controllers;

use App\Domains\Recurring\RecurringTypeEnum;
use App\Domains\Sources\SourceTypeEnum;
use App\Models\Collection;
use App\Models\Source;
use App\Models\User;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class JsonSourceControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_store(): void
    {
        Bus::fake();
        $user = User::factory()->create();
        $collection = Collection::factory()->create();
        $this->assertDatabaseCount('sources', 0);
        $data = get_fixture('example_instructions.json', false);
        $data_json = get_fixture('example_instructions.json');
        $response = $this->actingAs($user)
            ->post(route('collections.sources.json_source.store', $collection), [
                'title' => 'Test Title',
                'active' => 1,
                'recurring' => RecurringTypeEnum::Daily->value,
                'details' => 'Test Details',
                'meta_data' => $data,
            ])->assertSessionHasNoErrors();

        $response->assertSessionHas('flash.banner', 'Source added successfully');

        $this->assertDatabaseCount('sources', 1);
        $this->assertDatabaseCount('documents', 20);

        $source = Source::first();

        $this->assertEquals(SourceTypeEnum::JsonSource, $source->type);

        $this->assertEquals($source->meta_data, $data_json);
        Bus::assertBatchCount(20);
    }

    public function test_update()
    {
        Bus::fake();
        $source = Source::factory()->create();

        $user = User::factory()->create();
        $data = get_fixture('example_instructions.json', false);
        $data_json = get_fixture('example_instructions.json');
        $this->actingAs($user)
            ->put(route('collections.sources.json_source.update',
                [
                    'collection' => $source->collection->id,
                    'source' => $source->id,
                ]
            ), [
                'title' => 'Test Title',
                'active' => 1,
                'meta_data' => $data,
                'recurring' => RecurringTypeEnum::Daily->value,
                'details' => 'Test Details',
            ])
            ->assertSessionHasNoErrors()
            ->assertStatus(302);

        $this->assertEquals($data_json, $source->refresh()->meta_data);

        $this->assertDatabaseCount('documents', 20);

        $this->actingAs($user)
            ->put(route('collections.sources.json_source.update',
                [
                    'collection' => $source->collection->id,
                    'source' => $source->id,
                ]
            ), [
                'title' => 'Test Title',
                'active' => 1,
                'meta_data' => $data,
                'recurring' => RecurringTypeEnum::Daily->value,
                'details' => 'Test Details',
            ])
            ->assertSessionHasNoErrors()
            ->assertStatus(302);

        $this->assertEquals($data_json, $source->refresh()->meta_data);

        //Test no duplicates
        $this->assertDatabaseCount('documents', 20);

        Bus::assertBatchCount(20);
    }

    public function test_will_not_update_if_not_changed()
    {
        Bus::fake();

        $user = User::factory()->create();
        $data = get_fixture('example_instructions.json', false);
        $data_json = get_fixture('example_instructions.json');
        $source = Source::factory()->create(
            [
                'meta_data' => $data_json,
            ]
        );

        $this->actingAs($user)
            ->put(route('collections.sources.json_source.update',
                [
                    'collection' => $source->collection->id,
                    'source' => $source->id,
                ]
            ), [
                'title' => 'Test Title',
                'active' => 1,
                'meta_data' => $data,
                'recurring' => RecurringTypeEnum::Daily->value,
                'details' => 'Test Details',
            ])
            ->assertSessionHasNoErrors()
            ->assertStatus(302);

        $this->assertEquals($data_json, $source->refresh()->meta_data);

        $this->assertDatabaseCount('documents', 0);

        Bus::assertBatchCount(0);
    }
}
