<?php

namespace Tests\Feature;

use App\Domains\Outputs\OutputTypeEnum;
use App\Domains\Recurring\RecurringTypeEnum;
use App\Domains\Sources\SourceTypeEnum;
use App\Models\Collection;
use App\Models\Output;
use App\Models\Source;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class EmailReplyOutputControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_create_store(): void
    {
        $user = User::factory()->create();
        $collection = Collection::factory()->create();
        $this->assertDatabaseCount('outputs', 0);
        $response = $this->actingAs($user)
            ->post(route('collections.outputs.email_reply_output.store', $collection), [
                'title' => 'Test Title',
                'active' => 1,
                'recurring' => RecurringTypeEnum::Daily->value,
                'summary' => 'Test Details',
                'meta_data' => [
                    'signature' => 'Call if needed',
                ],
            ])
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $response->assertSessionHas('flash.banner', 'Output added successfully');

        $this->assertDatabaseCount('outputs', 1);

        $model = Output::first();

        $this->assertEquals(OutputTypeEnum::EmailReplyOutput, $model->type);

        $this->assertNotEmpty($model->meta_data['signature']);
    }

    public function test_create_update(): void
    {
        $user = User::factory()->create();
        $collection = Collection::factory()->create();
        $output = Output::factory()->create([
            'type' => OutputTypeEnum::EmailReplyOutput,
            'collection_id' => $collection->id,
        ]);
        $response = $this->actingAs($user)
            ->put(route('collections.outputs.email_reply_output.update',
                [
                    $collection,
                    $output
                ]), [
                'title' => 'Test Title',
                'active' => 1,
                'recurring' => RecurringTypeEnum::Daily->value,
                'summary' => 'Test Details',
                'meta_data' => [
                    'signature' => 'Call if needed',
                ],
            ])
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $response->assertSessionHas('flash.banner', 'Updated');

        $this->assertDatabaseCount('outputs', 1);

        $model = Output::first();

        $this->assertEquals(OutputTypeEnum::EmailReplyOutput, $model->type);

        $this->assertNotEmpty($model->meta_data['signature']);
    }
}
