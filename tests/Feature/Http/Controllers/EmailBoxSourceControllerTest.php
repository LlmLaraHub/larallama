<?php

namespace Tests\Feature\Http\Controllers;

use App\Domains\Recurring\RecurringTypeEnum;
use App\Domains\Sources\SourceTypeEnum;
use App\Models\Collection;
use App\Models\Source;
use App\Models\User;
use Tests\TestCase;

class EmailBoxSourceControllerTest extends TestCase
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
            ->post(route('collections.sources.email_box_source.store', $collection), [
                'title' => 'Test Title',
                'active' => 1,
                'recurring' => RecurringTypeEnum::Daily->value,
                'details' => 'Test Details',
                'secrets' => [
                    'username' => 'bob@bobsburgers.com',
                    'password' => 'password',
                    'delete' => true,
                    'host' => 'mail.privateemail.com',
                    'email_box' => 'bob@bobsburgers.com',
                ],
            ]);
        $response->assertSessionHas('flash.banner', 'Source added successfully');
        $this->assertDatabaseCount('sources', 1);

        $source = Source::first();

        $this->assertEquals(SourceTypeEnum::EmailBoxSource, $source->type);
        $secrets = $source->secrets;

        $this->assertEquals($secrets['username'], 'bob@bobsburgers.com');
        $this->assertEquals($secrets['password'], 'password');
        $this->assertEquals($secrets['host'], 'mail.privateemail.com');
        $this->assertEquals($secrets['delete'], true);
        $this->assertEquals($secrets['email_box'], 'bob@bobsburgers.com');
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
                'title' => 'Test Title',
                'active' => 1,
                'recurring' => RecurringTypeEnum::Daily->value,
                'details' => 'Test Details',
                'secrets' => [
                    'username' => 'bob@bobsburgers.com',
                    'password' => 'password',
                    'delete' => true,
                    'host' => 'mail.privateemail.com',
                    'email_box' => 'bob@bobsburgers.com',
                ],
            ])
            ->assertSessionHasNoErrors()
            ->assertStatus(302);

        $secrets = $source->refresh()->secrets;
        $this->assertEquals($secrets['username'], 'bob@bobsburgers.com');
        $this->assertEquals($secrets['password'], 'password');
        $this->assertEquals($secrets['host'], 'mail.privateemail.com');
        $this->assertEquals($secrets['delete'], true);
        $this->assertEquals($secrets['email_box'], 'bob@bobsburgers.com');
    }
}
