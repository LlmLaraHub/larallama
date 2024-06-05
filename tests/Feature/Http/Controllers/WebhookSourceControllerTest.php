<?php

namespace Tests\Feature\Http\Controllers;

use App\Domains\Recurring\RecurringTypeEnum;
use App\Domains\Sources\SourceTypeEnum;
use App\Jobs\WebhookSourceJob;
use App\Models\Collection;
use App\Models\Source;
use App\Models\User;
use Tests\TestCase;

class WebhookSourceControllerTest extends TestCase
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
            ->post(route('collections.sources.webhook_source.store', $collection), [
                'title' => 'Test Title',
                'active' => 1,
                'recurring' => RecurringTypeEnum::Daily->value,
                'details' => 'Test Details',
                'secrets' => [
                    'token' => 'foobar',
                ],
            ])->assertSessionHasNoErrors();
        $response->assertSessionHas('flash.banner', 'Source added successfully');

        $this->assertDatabaseCount('sources', 1);

        $source = Source::first();

        $this->assertEquals(SourceTypeEnum::WebhookSource, $source->type);

        $this->assertEquals('foobar', $source->secrets['token']);
    }

    public function test_update()
    {
        $source = Source::factory()->create();

        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('collections.sources.webhook_source.update',
                [
                    'collection' => $source->collection->id,
                    'source' => $source->id,
                ]
            ), [
                'title' => 'Test Title',
                'active' => 1,
                'recurring' => RecurringTypeEnum::Daily->value,
                'details' => 'Test Details',
                'secrets' => [
                    'token' => 'foobar',
                ],
            ])
            ->assertSessionHasNoErrors()
            ->assertStatus(302);

        $this->assertEquals('foobar', $source->refresh()->secrets['token']);
    }

    public function test_api()
    {
        \Illuminate\Support\Facades\Queue::fake();
        $source = Source::factory()->create(
            ['active' => true]
        );

        $url = route('collections.sources.webhook_source.api', [
            'source' => $source->slug,
        ]);

        $data = [];
        $data['token'] = 'bazboo';
        $this->post($url, $data)->assertStatus(200);
        \Illuminate\Support\Facades\Queue::assertPushed(WebhookSourceJob::class);
    }

    public function test_api_no_job()
    {
        \Illuminate\Support\Facades\Queue::fake();
        $source = Source::factory()->create(
            ['active' => false]
        );

        $url = route('collections.sources.webhook_source.api', [
            'source' => $source->slug,
        ]);

        $data = [];
        $data['token'] = 'bazboo';
        $this->post($url, $data)->assertStatus(200);
        \Illuminate\Support\Facades\Queue::assertNothingPushed();
    }
}
