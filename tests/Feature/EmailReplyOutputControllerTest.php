<?php

namespace Tests\Feature;

use App\Domains\Outputs\OutputTypeEnum;
use App\Domains\Recurring\RecurringTypeEnum;
use App\Models\Collection;
use App\Models\Output;
use App\Models\Persona;
use App\Models\User;
use Tests\TestCase;

class EmailReplyOutputControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_create_store(): void
    {
        $user = User::factory()->create();
        $persona = Persona::factory()->create();
        $collection = Collection::factory()->create();
        $this->assertDatabaseCount('outputs', 0);
        $response = $this->actingAs($user)
            ->post(route('collections.outputs.email_reply_output.store', $collection), [
                'title' => 'Test Title',
                'active' => 1,
                'recurring' => RecurringTypeEnum::Daily->value,
                'summary' => 'Test Details',
                'persona_id' => $persona->id,
                'meta_data' => [
                    'signature' => 'Call if needed',
                ],
                'secrets' => [
                    'username' => 'bob@bobsburgers.com',
                    'password' => 'password',
                    'delete' => true,
                    'host' => 'mail.privateemail.com',
                    'email_box' => 'bob@bobsburgers.com',
                ],
            ])
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $response->assertSessionHas('flash.banner', 'Output added successfully');

        $this->assertDatabaseCount('outputs', 1);

        $model = Output::first();
        $this->assertNotNull($model->persona_id);
        $this->assertEquals(OutputTypeEnum::EmailReplyOutput, $model->type);

        $secrets = $model->refresh()->secrets;
        $this->assertEquals($secrets['username'], 'bob@bobsburgers.com');
        $this->assertEquals($secrets['password'], 'password');
        $this->assertEquals($secrets['host'], 'mail.privateemail.com');
        $this->assertEquals($secrets['delete'], true);
        $this->assertEquals($secrets['email_box'], 'bob@bobsburgers.com');
        $this->assertEquals($secrets['port'], 993);
        $this->assertEquals($secrets['protocol'], 'imap');
        $this->assertEquals($secrets['encryption'], 'ssl');

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
        $persona = Persona::factory()->create();

        $response = $this->actingAs($user)
            ->put(route('collections.outputs.email_reply_output.update',
                [
                    $collection->id,
                    $output->id,
                ]), [
                    'title' => 'Test Title',
                    'active' => 1,
                    'persona_id' => $persona->id,
                    'recurring' => RecurringTypeEnum::Daily->value,
                    'summary' => 'Test Details',
                    'meta_data' => [
                        'signature' => 'Call if needed',
                    ],
                    'secrets' => [
                        'username' => 'bob@bobsburgers.com',
                        'password' => 'password',
                        'delete' => true,
                        'host' => 'mail.privateemail.com',
                        'email_box' => 'bob@bobsburgers.com',
                    ],
                ])
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $response->assertSessionHas('flash.banner', 'Updated');

        $this->assertDatabaseCount('outputs', 1);

        $model = Output::first();

        $this->assertEquals(OutputTypeEnum::EmailReplyOutput, $model->type);

        $this->assertNotEmpty($model->meta_data['signature']);

        $this->assertNotNull($model->persona_id);
        $secrets = $output->refresh()->secrets;
        $this->assertEquals($secrets['username'], 'bob@bobsburgers.com');
        $this->assertEquals($secrets['password'], 'password');
        $this->assertEquals($secrets['host'], 'mail.privateemail.com');
        $this->assertEquals($secrets['delete'], true);
        $this->assertEquals($secrets['email_box'], 'bob@bobsburgers.com');
        $this->assertEquals($secrets['port'], 465);
        $this->assertEquals($secrets['protocol'], 'imap');
        $this->assertEquals($secrets['encryption'], 'ssl');
    }
}
