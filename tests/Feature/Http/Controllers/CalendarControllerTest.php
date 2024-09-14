<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Collection;
use App\Models\Output;
use App\Models\User;
use Tests\TestCase;

class CalendarControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_success(): void
    {
        $collection = Collection::factory()->create();

        Output::factory()->create([
            'collection_id' => $collection->id,
            'type' => \App\Domains\Outputs\OutputTypeEnum::CalendarOutput,
        ]);

        $this->actingAs(User::factory()->create())->get(route('calendar.show', [
            'collection' => $collection->id,
        ]))->assertStatus(200);
    }
}
