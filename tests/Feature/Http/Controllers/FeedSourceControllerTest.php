<?php

namespace Tests\Feature\Http\Controllers;

use App\Domains\Recurring\RecurringTypeEnum;
use App\Domains\Sources\SourceTypeEnum;
use App\Models\Collection;
use App\Models\Source;
use App\Models\User;
use Tests\TestCase;

class FeedSourceControllerTest extends TestCase
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
            ->post(route('collections.sources.feed_source.store', $collection), [
                'title' => 'Test Title',
                'active' => 1,
                'recurring' => RecurringTypeEnum::Daily->value,
                'details' => 'Test Details',
                'meta_data' => [
                    'feed_url' => 'https://www.larallama.io/feed',
                ],
            ])->assertSessionHasNoErrors();
        $response->assertSessionHas('flash.banner', 'Source added successfully');

        $this->assertDatabaseCount('sources', 1);

        $source = Source::first();

        $this->assertEquals(SourceTypeEnum::FeedSource, $source->type);

        $this->assertEquals($source->meta_data['feed_url'], 'https://www.larallama.io/feed');
    }

    public function test_update()
    {
        $source = Source::factory()->create();

        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('collections.sources.feed_source.update',
                [
                    'collection' => $source->collection->id,
                    'source' => $source->id,
                ]
            ), [
                'title' => 'Test Title',
                'active' => 1,
                'recurring' => RecurringTypeEnum::Daily->value,
                'details' => 'Test Details',
                'meta_data' => [
                    'feed_url' => 'https://www.larallama.io/feed',
                ],
            ])
            ->assertSessionHasNoErrors()
            ->assertStatus(302);

        $this->assertTrue($source->refresh()->active, true);
        $this->assertEquals($source->meta_data['feed_url'], 'https://www.larallama.io/feed');
    }
}
