<?php

namespace Tests\Feature\Http\Controllers;

use App\Domains\Sources\SourceTypeEnum;
use App\Models\Collection;
use App\Models\Source;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ApiChromeExtensionControllerTest extends TestCase
{


    public function test_index()
    {
        $user = User::factory()->create();
        Collection::factory(20)->create();
        $response = $this->actingAs($user)
            ->get(route('api.chrome_extension.collections.index'))
            ->assertStatus(200)
            ->json();

        $this->assertCount(20, $response['data']);
    }

    public function test_create_source()
    {
        $this->assertDatabaseCount('sources', 0);
        $user = User::factory()->create();
        $collection = Collection::factory()->create();
        $response = $this->actingAs($user)
            ->post(route('api.chrome_extension.collections.source.create', $collection), [
                'url' => 'https://www.google.com',
                'recurring' => 'not',
                'force' => false,
                'prompt' => 'Foo bar',
                'content' => 'Foo bar',
            ])
            ->assertStatus(200);
        $this->assertDatabaseCount('sources', 1);

    }

    public function test_get_source()
    {
        $this->assertDatabaseCount('sources', 0);
        $user = User::factory()->create();
        $collection = Collection::factory()->create();
        $source = Source::factory()->create([
            'collection_id' => $collection->id,
        ]);
        $response = $this->actingAs($user)
            ->get(
                route('api.chrome_extension.collections.source.get',
                    [
                        'collection' => $collection->id,
                        'source' => $source->id,
                    ]))
            ->assertStatus(200)
            ->json();

        $this->assertEquals($source->id, $response['id']);
        $this->assertEquals($source->title, $response['title']);
        $this->assertEquals($source->details, $response['details']);
        $this->assertEquals($source->details, $response['prompt']);
        $this->assertEquals($source->active, $response['active']);
        $this->assertEquals($source->recurring->name, $response['recurring']);
        $this->assertEquals($source->force, $response['force']);
        $this->assertEquals("non needed", $response['status']);
        $this->assertEquals(data_get($source->meta_data, 'urls.0'), $response['url']);
    }

    public function test_get_sources()
    {
        $this->assertDatabaseCount('sources', 0);
        $user = User::factory()->create();
        $collection = Collection::factory()->create();
        Source::factory(2)->create([
            'type' => SourceTypeEnum::WebPageSource,
            'collection_id' => $collection->id,
        ]);
        $response = $this->actingAs($user)
            ->get(
                route('api.chrome_extension.collections.source.index'))
            ->assertStatus(200)
            ->json();

        $this->assertCount(2, $response['data']);
    }
}
