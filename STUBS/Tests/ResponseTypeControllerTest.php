<?php

use App\Models\Outbound;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;

it('should create and redirect', function () {
    $user = User::factory()->create();

    $outbound = Outbound::factory()->create();

    assertDatabaseCount('response_types', 0);

    actingAs($user)
        ->get(route('response_types.[RESOURCE_KEY].create', ['outbound' => $outbound->id]));

    assertDatabaseCount('response_types', 1);
});
