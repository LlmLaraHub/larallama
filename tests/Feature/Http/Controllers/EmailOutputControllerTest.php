<?php

namespace Tests\Feature\Http\Controllers;

use App\Domains\Recurring\RecurringTypeEnum;
use App\Jobs\SendOutputEmailJob;
use App\Models\Collection;
use App\Models\Document;
use App\Models\Output;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class EmailOutputControllerTest extends TestCase
{
    public function test_store(): void
    {

        $user = User::factory()->create();

        $collection = Collection::factory()->create();

        Document::factory(5)->create([
            'collection_id' => $collection->id,
        ]);

        $this->actingAs($user)->post(route(
            'collections.outputs.email_output.store',
            [
                'collection' => $collection->id,
            ]
        ), [
            'title' => 'Foobar',
            'summary' => 'Foobar',
            'meta_data' => [
                'to' => 'bob@bob.com',
            ],
            'recurring' => RecurringTypeEnum::Daily->value,
        ]
        );
        $output = Output::first();
        $this->assertEquals(RecurringTypeEnum::Daily, $output->recurring);
        $this->assertEquals(['to' => 'bob@bob.com'], $output->meta_data);

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
                'to' => 'bob@bob.com',
            ],
            'recurring' => RecurringTypeEnum::Daily->value,
        ]
        )->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseCount('outputs', 1);

        $output = Output::first();

        $this->assertEquals(RecurringTypeEnum::Daily, $output->recurring);
        $this->assertEquals(['to' => 'bob@bob.com'], $output->meta_data);
    }

    public function test_send(): void
    {

        Queue::fake();

        $output = Output::factory()->create([
            'recurring' => RecurringTypeEnum::HalfHour,
            'meta_data' => [
                'to' => 'bob@bobsburgers.com',
            ],
        ]);

        $user = User::factory()->create();

        $this->actingAs($user)->post(route(
            'collections.outputs.email_output.send',
            [
                'output' => $output->id,
            ]
        ))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        Queue::assertPushed(SendOutputEmailJob::class);
    }

    public function test_nothing_sent(): void
    {

        Queue::fake();

        $output = Output::factory()->create([
            'recurring' => RecurringTypeEnum::HalfHour,
            'meta_data' => [
                'to' => '',
            ],
        ]);

        $user = User::factory()->create();

        $this->actingAs($user)->post(route(
            'collections.outputs.email_output.send',
            [
                'output' => $output->id,
            ]
        ))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        Queue::assertNothingPushed();
    }
}
