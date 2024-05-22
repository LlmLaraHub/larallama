<?php

namespace Tests\Feature\Http\Controllers;

use App\Domains\Recurring\RecurringTypeEnum;
use App\Models\Collection;
use App\Models\Document;
use App\Models\Output;
use App\Models\User;
use Tests\TestCase;

class ApiOutputControllerTest extends TestCase
{
    public function test_store(): void
    {

        $user = User::factory()->create();

        $collection = Collection::factory()->create();

        Document::factory(5)->create([
            'collection_id' => $collection->id,
        ]);

        $this->actingAs($user)->post(route(
            'collections.outputs.api_output.store',
            [
                'collection' => $collection->id,
            ]
        ), [
            'title' => 'Foobar',
            'summary' => 'Foobar',
            'meta_data' => [
                'token' => 'hei8Job9Ebooquee',
            ],
        ]
        )->assertRedirect()
            ->assertSessionHasNoErrors();
        $output = Output::first();
        $this->assertEquals(['token' => 'hei8Job9Ebooquee'], $output->meta_data);

        $this->assertDatabaseCount('outputs', 1);

    }

    public function test_edit(): void
    {

        $output = Output::factory()->create();

        $user = User::factory()->create();

        $this->actingAs($user)->get(route(
            'collections.outputs.email_output.edit',
            [
                'collection' => $output->collection_id,
                'output' => $output->id,
            ]
        )
        )->assertStatus(200);

    }

    public function test_update(): void
    {

        $webpage = Output::factory()->create([
            'recurring' => RecurringTypeEnum::HalfHour,
        ]);

        $user = User::factory()->create();

        $this->actingAs($user)->put(route(
            'collections.outputs.email_output.update',
            [
                'collection' => $webpage->collection_id,
                'output' => $webpage->id,
            ]
        ), [
            'title' => 'Foobar2',
            'summary' => 'Foobar2',
            'meta_data' => [
                'token' => 'hei8Job9Ebooquee',
            ],
            'recurring' => RecurringTypeEnum::Daily->value,
        ]
        )->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseCount('outputs', 1);

        $output = Output::first();

        $this->assertEquals(['token' => 'hei8Job9Ebooquee'], $output->meta_data);
    }

    public function test_api_url()
    {
        $output = Output::factory()->create([
            'meta_data' => [
                'token' => 'foobar',
            ],
        ]);

        $this->post(route('collections.outputs.api_output.api', [
            'output' => $output->id,
        ]), [
            'token' => 'foobar',
            'prompt' => 'Baz',
        ])->assertStatus(200);

        $this->post(route('collections.outputs.api_output.api', [
            'output' => $output->id,
        ]), [
            'token' => 'bazboo',
            'prompt' => 'Baz',
        ])->assertStatus(404);

        $this->post(route('collections.outputs.api_output.api', [
            'output' => $output->id,
        ]), [
            'prompt' => 'Baz',
        ], [
            'Authorization' => 'Bearer foobar',
        ])->assertStatus(200);

    }
}
